<?php
/**
 * BidController: place bids, buy-now, and AJAX endpoints for live updates.
 *
 * SECURITY:
 *  - Auth required (registered bidders only).
 *  - Rate limited (BID_RATE_LIMIT per RATE_LIMIT_WINDOW_SECONDS).
 *  - CSRF on every POST.
 *  - Business rules enforced server-side: auction active, end time not passed,
 *    bid exceeds current highest + minimum increment, seller can't bid on own car.
 *  - Outbid notifications (in-app + email) sent to the previous highest bidder.
 */
final class BidController extends BaseController
{
    private Bid $bids;
    private Car $cars;
    private Notification $notifications;
    private Transaction $transactions;

    public function __construct()
    {
        parent::__construct();
        $this->bids = new Bid();
        $this->cars = new Car();
        $this->notifications = new Notification();
        $this->transactions = new Transaction();
    }

    /** POST /bids/place/{carId} — form post (non-AJAX fallback). */
    public function place(string $carId): void
    {
        Middleware::requireAuth();
        $this->guardPost('bid', [(int) Config::get('BID_RATE_LIMIT', 10), (int) Config::get('RATE_LIMIT_WINDOW_SECONDS', 60)]);

        $result = $this->processBid((int) $carId, (float) $this->request->input('bid_amount', 0));
        if (!$result['success']) {
            $this->flashRedirect('error', $result['message'], '/cars/view/' . $carId);
        }
        $this->flashRedirect('success', $result['message'], '/cars/view/' . $carId);
    }

    /** POST /bids/ajax/{carId} — AJAX bid submission (JSON response). */
    public function placeAjax(string $carId): void
    {
        Middleware::requireAuth();
        if (!$this->request->isPost() || !Csrf::verify()) {
            Response::json(['success' => false, 'message' => 'Invalid request.'], 419);
        }
        $bidMax = (int) Config::get('BID_RATE_LIMIT', 10);
        $bidWindow = (int) Config::get('RATE_LIMIT_WINDOW_SECONDS', 60);
        RateLimiter::hit('bid', $bidMax, $bidWindow, Auth::id());
        Middleware::requireRateLimit('bid', $bidMax, $bidWindow);

        $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $amount = (float) ($payload['bid_amount'] ?? 0);
        $result = $this->processBid((int) $carId, $amount);

        // Include updated state for the UI
        if ($result['success']) {
            $result['current_bid'] = $this->bids->highestBid((int) $carId)['bid_amount'] ?? null;
            $result['bid_count'] = (new \Bid())->historyForCar((int) $carId);
        }
        Response::json($result, $result['success'] ? 200 : 400);
    }

    /** POST /bids/buy-now/{carId} — skip bidding at fixed price. */
    public function buyNow(string $carId): void
    {
        Middleware::requireAuth();
        $this->guardPost('buy_now', [5, 60]);

        $car = $this->cars->find((int) $carId);
        if (!$car) Response::notFound('Listing not found.');
        if ($car['status'] !== 'active' || strtotime($car['auction_end']) <= time()) {
            $this->flashRedirect('error', 'Auction is not active.', '/cars/view/' . $carId);
        }
        if (!$car['buy_now_price']) {
            $this->flashRedirect('error', 'No Buy Now price set.', '/cars/view/' . $carId);
        }
        if ((int) $car['seller_id'] === Auth::id()) {
            $this->flashRedirect('error', 'You cannot buy your own listing.', '/cars/view/' . $carId);
        }

        // Place a bid equal to buy_now_price and immediately close the auction.
        // The car becomes 'sold' and a transaction is created so the payment /
        // handover / completion lifecycle is tracked (winning != completed).
        $winBidId = $this->bids->place((int) $carId, Auth::id(), (float) $car['buy_now_price']);
        $this->cars->setStatus((int) $carId, 'sold');
        $this->transactions->createForWin(
            (int) $carId,
            (int) $car['seller_id'],
            Auth::id(),
            $winBidId,
            (float) $car['buy_now_price']
        );

        $this->notifications->send(
            (int) $car['seller_id'],
            'Your listing "' . $car['title'] . '" was sold via Buy Now for ' . number_format((float) $car['buy_now_price'], 2) . '. A transaction has been created.',
            'sale', '/transactions'
        );
        $this->notifications->send(
            Auth::id(),
            'You won "' . $car['title'] . '" via Buy Now. Payment is now required to complete the purchase.',
            'won', '/transactions'
        );
        Logger::audit('buy_now', ['car' => $carId, 'amount' => $car['buy_now_price']], Auth::id());
        $this->flashRedirect('success', 'Congratulations! You purchased this car via Buy Now. Please complete payment under your Transactions.', '/transactions');
    }

    /** GET /bids/history/{carId} — AJAX: latest bid history + highest. */
    public function history(string $carId): void
    {
        $carId = (int) $carId;
        $highest = $this->bids->highestBid($carId);
        $history = $this->bids->historyForCar($carId, 20);
        $car = $this->cars->find($carId);

        // Encode bidder names for XSS safety in JSON consumers
        $safe = [];
        foreach ($history as $h) {
            $safe[] = [
                'bid_amount'   => (float) $h['bid_amount'],
                'bidder_name'  => e($h['bidder_name']),
                'created_at'   => $h['created_at'],
            ];
        }
        Response::json([
            'success'     => true,
            'current_bid' => $highest ? (float) $highest['bid_amount'] : null,
            'status'      => $car['status'] ?? null,
            'auction_end' => $car['auction_end'] ?? null,
            'history'     => $safe,
        ]);
    }

    // ---------- core bidding logic ----------

    private function processBid(int $carId, float $amount): array
    {
        $car = $this->cars->find($carId);
        if (!$car) return ['success' => false, 'message' => 'Listing not found.'];
        if ($car['status'] !== 'active') return ['success' => false, 'message' => 'This auction is not active.'];
        if (strtotime($car['auction_end']) <= time()) {
            $this->cars->setStatus($carId, 'closed');
            return ['success' => false, 'message' => 'This auction has ended.'];
        }
        if (strtotime($car['auction_start']) > time()) {
            return ['success' => false, 'message' => 'This auction has not started yet.'];
        }
        if ((int) $car['seller_id'] === Auth::id()) {
            return ['success' => false, 'message' => 'You cannot bid on your own listing.'];
        }
        if ($amount <= 0) return ['success' => false, 'message' => 'Invalid bid amount.'];

        $highest = $this->bids->highestBid($carId);
        $current = $highest ? (float) $highest['bid_amount'] : (float) $car['starting_price'];
        $minIncrement = max(100.0, round($current * 0.01, 2)); // 1% or 100, whichever larger
        $minBid = $current + $minIncrement;

        if ($amount < $minBid) {
            return ['success' => false, 'message' => 'Bid must be at least ' . number_format($minBid, 2)];
        }

        // Place the bid
        $this->bids->place($carId, Auth::id(), $amount);

        // Notify previous highest bidder that they were outbid
        if ($highest && (int) $highest['bidder_id'] !== Auth::id()) {
            $this->notifications->send(
                (int) $highest['bidder_id'],
                'You were outbid on "' . $car['title'] . '". Current bid: ' . number_format($amount, 2),
                'outbid', '/cars/view/' . $carId
            );
            $prevUser = (new User())->find((int) $highest['bidder_id']);
            if ($prevUser) {
                $html = "<p>You were outbid on <strong>" . e($car['title']) . "</strong>.</p>
                         <p>New highest bid: " . number_format($amount, 2) . "</p>
                         <p><a href=\"" . rtrim(Config::get('APP_URL',''),'/') . "/cars/view/{$carId}\">Place a new bid</a></p>";
                Mailer::send($prevUser['email'], 'You were outbid', $html);
            }
        }

        // Notify seller
        $this->notifications->send(
            (int) $car['seller_id'],
            'A new bid of ' . number_format($amount, 2) . ' was placed on "' . $car['title'] . '".',
            'bid', '/cars/view/' . $carId
        );

        Logger::audit('bid_place', ['car' => $carId, 'amount' => $amount], Auth::id());
        return ['success' => true, 'message' => 'Bid placed successfully!'];
    }
}
