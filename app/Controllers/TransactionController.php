<?php
/**
 * TransactionController: post-auction sale lifecycle.
 *
 * Lifecycle (mapped to the existing system):
 *   AUCTION WON -> payment_pending -> payment_confirmed
 *               -> ready_for_handover -> completed
 *               (any state) -> cancelled
 *
 * RBAC / ownership:
 *   - Buyers see their own purchases (winner_id).
 *   - Sellers see their own sales (seller_id).
 *   - Admins see all transactions and can advance/cancel any.
 *   - State-changing actions are gated to the seller of the listing or an
 *     admin (the seller confirms receipt of payment and records handover).
 *   - The winner may mark a payment as sent (notifies the seller; no status
 *     change) so the manual/onsite confirmation process stays connected.
 */
final class TransactionController extends BaseController
{
    private Transaction $transactions;
    private Notification $notifications;

    public function __construct()
    {
        parent::__construct();
        $this->transactions = new Transaction();
        $this->notifications = new Notification();
    }

    /** GET /transactions — role-aware list (purchases / sales / all). */
    public function index(): void
    {
        Middleware::requireAuth();
        $role = Auth::role();
        $uid = Auth::id();

        if ($role === 'admin') {
            $items = $this->transactions->allWithDetails();
        } elseif ($role === 'seller') {
            // A seller sees their sales; if they also bid, merge purchases.
            $sales = $this->transactions->salesBySeller($uid);
            $purchases = $this->transactions->purchasesByBuyer($uid);
            // De-duplicate by id (a seller could theoretically be their own
            // winner only in test edge cases).
            $items = $this->mergeUnique($sales, $purchases);
        } else {
            $items = $this->transactions->purchasesByBuyer($uid);
        }

        $this->view('dashboard/transactions', [
            'pageTitle' => 'Transactions',
            'items'     => $items,
            'role'      => $role,
        ]);
    }

    /** GET /transactions/view/{id} — transaction detail with status timeline. */
    public function show(string $id): void
    {
        Middleware::requireAuth();
        $tx = $this->transactions->findWithDetails((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');

        $this->requireParty((int) $tx['seller_id'], (int) $tx['winner_id']);

        $isSeller = Auth::id() === (int) $tx['seller_id'];
        $isAdmin  = Auth::is('admin');
        $isWinner = Auth::id() === (int) $tx['winner_id'];
        $canAdvance = ($isSeller || $isAdmin) && $tx['status'] !== 'completed' && $tx['status'] !== 'cancelled';

        $this->view('dashboard/transaction_detail', [
            'pageTitle' => 'Transaction #' . (int) $tx['id'],
            'tx'        => $tx,
            'isSeller'  => $isSeller,
            'isWinner'  => $isWinner,
            'isAdmin'   => $isAdmin,
            'canAdvance'=> $canAdvance,
            'csrf'      => Csrf::token(),
        ]);
    }

    /** POST /transactions/mark-paid/{id} — winner signals payment sent. */
    public function markPaid(string $id): void
    {
        Middleware::requireAuth();
        $this->guardPost('tx', [10, 60]);
        $tx = $this->transactions->find((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');
        if (Auth::id() !== (int) $tx['winner_id']) Response::forbidden('Only the winner may mark payment as sent.');
        if ($tx['status'] !== 'payment_pending') {
            $this->flashRedirect('error', 'Payment has already been recorded for this transaction.', '/transactions/view/' . $id);
        }

        $this->notifications->send(
            (int) $tx['seller_id'],
            'The winner has marked payment as sent for transaction #' . (int) $tx['id'] . '. Please confirm receipt.',
            'sale', '/transactions/view/' . $id
        );
        Logger::audit('tx_payment_sent', ['tx' => $id], Auth::id());
        $this->flashRedirect('success', 'Payment marked as sent. The seller has been notified to confirm receipt.', '/transactions/view/' . $id);
    }

    /** POST /transactions/confirm-payment/{id} — seller/admin confirms payment received. */
    public function confirmPayment(string $id): void
    {
        Middleware::requireAuth();
        $this->guardPost('tx', [10, 60]);
        $tx = $this->transactions->find((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');
        $this->requireSellerOrAdmin((int) $tx['seller_id']);

        if (!$this->transactions->transition((int) $id, 'payment_confirmed')) {
            $this->flashRedirect('error', 'Payment cannot be confirmed from the current state.', '/transactions/view/' . $id);
        }
        $this->notifications->send(
            (int) $tx['winner_id'],
            'Your payment for transaction #' . (int) $tx['id'] . ' has been confirmed. The vehicle is being prepared for handover.',
            'sale', '/transactions/view/' . $id
        );
        Logger::audit('tx_payment_confirmed', ['tx' => $id], Auth::id());
        $this->flashRedirect('success', 'Payment confirmed. Transaction is now ready for handover.', '/transactions/view/' . $id);
    }

    /** POST /transactions/ready-handover/{id} — seller/admin marks ready for handover. */
    public function readyHandover(string $id): void
    {
        Middleware::requireAuth();
        $this->guardPost('tx', [10, 60]);
        $tx = $this->transactions->find((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');
        $this->requireSellerOrAdmin((int) $tx['seller_id']);

        if (!$this->transactions->transition((int) $id, 'ready_for_handover')) {
            $this->flashRedirect('error', 'Cannot mark ready for handover from the current state.', '/transactions/view/' . $id);
        }
        $this->notifications->send(
            (int) $tx['winner_id'],
            'Your vehicle for transaction #' . (int) $tx['id'] . ' is ready for handover. Please coordinate with the seller.',
            'sale', '/transactions/view/' . $id
        );
        Logger::audit('tx_ready_handover', ['tx' => $id], Auth::id());
        $this->flashRedirect('success', 'Marked ready for handover.', '/transactions/view/' . $id);
    }

    /** POST /transactions/complete/{id} — seller/admin records handover & completes. */
    public function complete(string $id): void
    {
        Middleware::requireAuth();
        $this->guardPost('tx', [10, 60]);
        $tx = $this->transactions->find((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');
        $this->requireSellerOrAdmin((int) $tx['seller_id']);

        if (!$this->transactions->transition((int) $id, 'completed')) {
            $this->flashRedirect('error', 'Cannot complete the transaction from the current state.', '/transactions/view/' . $id);
        }
        // Ensure the car stays sold/unavailable — it already is 'sold', but
        // this guards against any accidental status drift.
        (new Car())->setStatus((int) $tx['car_id'], 'sold');

        $this->notifications->send(
            (int) $tx['winner_id'],
            'Your transaction #' . (int) $tx['id'] . ' is complete. Vehicle handover has been recorded.',
            'sale', '/transactions/view/' . $id
        );
        $this->notifications->send(
            (int) $tx['seller_id'],
            'Transaction #' . (int) $tx['id'] . ' is complete. Vehicle handover has been recorded.',
            'sale', '/transactions/view/' . $id
        );
        Logger::audit('tx_completed', ['tx' => $id], Auth::id());
        $this->flashRedirect('success', 'Transaction completed. Vehicle handover recorded.', '/transactions/view/' . $id);
    }

    /** POST /transactions/cancel/{id} — admin cancels a transaction (no sale). */
    public function cancel(string $id): void
    {
        Middleware::requireAuth();
        $this->guardPost('tx', [10, 60]);
        $tx = $this->transactions->find((int) $id);
        if (!$tx) Response::notFound('Transaction not found.');
        if (!Auth::is('admin')) Response::forbidden('Only an admin may cancel a transaction.');

        if (!$this->transactions->transition((int) $id, 'cancelled')) {
            $this->flashRedirect('error', 'This transaction cannot be cancelled.', '/transactions/view/' . $id);
        }
        // Revert the car to closed so it is no longer considered sold.
        (new Car())->setStatus((int) $tx['car_id'], 'closed');

        $this->notifications->send(
            (int) $tx['seller_id'],
            'Transaction #' . (int) $tx['id'] . ' was cancelled by an admin. The listing has been reverted to closed.',
            'sale', '/transactions'
        );
        $this->notifications->send(
            (int) $tx['winner_id'],
            'Transaction #' . (int) $tx['id'] . ' was cancelled by an admin.',
            'sale', '/transactions'
        );
        Logger::audit('tx_cancelled', ['tx' => $id], Auth::id());
        $this->flashRedirect('success', 'Transaction cancelled.', '/transactions');
    }

    // ---------- access helpers ----------

    /** Allow only the seller or winner of a transaction (or admin). */
    private function requireParty(int $sellerId, int $winnerId): void
    {
        if (Auth::is('admin')) return;
        if (Auth::id() === $sellerId || Auth::id() === $winnerId) return;
        Response::forbidden('You do not have access to this transaction.');
    }

    /** Only the seller of the listing or an admin may advance the lifecycle. */
    private function requireSellerOrAdmin(int $sellerId): void
    {
        if (Auth::is('admin')) return;
        if (Auth::id() === $sellerId) return;
        Response::forbidden('Only the seller or an admin may perform this action.');
    }

    private function mergeUnique(array $a, array $b): array
    {
        $out = [];
        $seen = [];
        foreach (array_merge($a, $b) as $row) {
            $key = (int) $row['id'];
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            $out[] = $row;
        }
        return $out;
    }
}
