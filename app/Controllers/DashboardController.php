<?php
/**
 * DashboardController: role-aware user dashboard.
 * Shows stats + my listings / my bids / won auctions / watchlist summary.
 */
final class DashboardController extends BaseController
{
    public function index(): void
    {
        Middleware::requireAuth();
        // Opportunistically close expired auctions (DB is source of truth).
        AuctionCloser::run();
        $role = Auth::role();

        // Redirect admins to the admin dashboard
        if ($role === 'admin') {
            Response::redirect('/admin');
        }

        $uid = Auth::id();

        $carModel = new Car();
        $bidModel = new Bid();
        $watchModel = new Watchlist();

        $stats = [];
        $listings = [];

        if ($role === 'seller' || $role === 'admin') {
            $listings = $carModel->listingsBySeller($uid);
            $stats['active_listings'] = count(array_filter($listings, fn($c) => $c['status'] === 'active'));
            $stats['pending_listings'] = count(array_filter($listings, fn($c) => $c['status'] === 'pending'));
            $stats['sold'] = count(array_filter($listings, fn($c) => $c['status'] === 'sold'));
            $stats['revenue'] = array_sum(array_map(
                fn($c) => (float) ($c['current_bid'] ?? 0),
                array_filter($listings, fn($c) => $c['status'] === 'sold')
            ));
        }
        // Buyers (and sellers can bid too)
        $stats['active_bids'] = $bidModel->countByBidder($uid);
        $stats['won_auctions'] = $bidModel->countWonByBidder($uid);
        $stats['watchlist'] = $watchModel->countByUser($uid);

        // Most recently won car (single preview on the dashboard). Sellers can
        // also bid, so this is shown to all roles that have won auctions.
        $recentWon = $bidModel->wonByBidder($uid);
        $latestWon = !empty($recentWon) ? $recentWon[0] : null;

        // Spending chart data: monthly spend (last 6 months) + total spent + status breakdown.
        $txModel = new Transaction();
        $monthlySpend = $txModel->monthlySpendingByBuyer($uid);
        $totalSpent = $txModel->totalSpentByBuyer($uid);
        $spendBreakdown = $txModel->spendingBreakdownByBuyer($uid);

        // ---- Buyer analytics (dark dashboard, mirrors the admin layout) ----
        // Trends (last 30d vs prior 30d) + daily chart series via one PDO grab.
        $pdo = Database::getInstance();
        $pair = function (string $table, string $userCol, string $sumCol = null, string $extra = '') use ($pdo, $uid): array {
            $cur  = $sumCol ? "COALESCE(SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN $sumCol END), 0)"
                            : "SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END)";
            $prev = $sumCol ? "COALESCE(SUM(CASE WHEN created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN $sumCol END), 0)"
                            : "SUM(CASE WHEN created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END)";
            $stmt = $pdo->prepare(
                "SELECT $cur AS cur, $prev AS prev FROM $table
                 WHERE $userCol = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) $extra"
            );
            $stmt->execute([$uid]);
            $row = $stmt->fetch();
            return ['cur' => (float)($row['cur'] ?? 0), 'prev' => (float)($row['prev'] ?? 0)];
        };

        $bidPair   = $pair('bids', 'bidder_id');
        $spendPair = $pair('transactions', 'winner_id', 'final_amount', 'AND status != "cancelled"');
        $wonPair   = $pair('transactions', 'winner_id', null, 'AND status != "cancelled"');
        $watchPair = $pair('watchlist', 'user_id');

        // Daily bids + spend for the last 90 days (chart series).
        $bidsByDay = $pdo->prepare(
            "SELECT DATE(created_at) d, COUNT(*) c FROM bids
             WHERE bidder_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
             GROUP BY DATE(created_at)"
        );
        $bidsByDay->execute([$uid]);
        $bidsByDay = $bidsByDay->fetchAll(\PDO::FETCH_KEY_PAIR);
        $spendByDay = $pdo->prepare(
            "SELECT DATE(created_at) d, COALESCE(SUM(final_amount),0) c FROM transactions
             WHERE winner_id = ? AND status != 'cancelled' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
             GROUP BY DATE(created_at)"
        );
        $spendByDay->execute([$uid]);
        $spendByDay = $spendByDay->fetchAll(\PDO::FETCH_KEY_PAIR);

        $chart = [];
        for ($i = 89; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chart[] = [
                'd'     => $d,
                'bids'  => (int) ($bidsByDay[$d] ?? 0),
                'spend' => round((float) ($spendByDay[$d] ?? 0) / 1000, 1), // ₱ thousands for scale
            ];
        }

        $this->view('dashboard/index', [
            'pageTitle'      => 'Dashboard',
            'stats'          => $stats,
            'listings'       => $listings,
            'latestWon'      => $latestWon,
            'wonCount'       => count($recentWon),
            'monthlySpend'   => $monthlySpend,
            'totalSpent'     => $totalSpent,
            'spendBreakdown' => $spendBreakdown,
            'role'           => $role,
            // dark dashboard additions
            'trends'  => ['bids' => $bidPair, 'spend' => $spendPair, 'won' => $wonPair, 'watch' => $watchPair],
            'chart'   => $chart,
            'bids'    => array_slice($bidModel->bidsByBidder($uid), 0, 25),
            'won'     => $recentWon,
            'watch'   => array_slice($watchModel->forUser($uid), 0, 25),
        ]);
    }

    public function myBids(): void
    {
        Middleware::requireAuth();
        $uid = Auth::id();
        $bidModel = new Bid();
        $bids = $bidModel->bidsByBidder($uid);

        $this->view('dashboard/my_bids', [
            'pageTitle' => 'My Bids',
            'bids'      => $bids,
        ]);
    }

    public function wonAuctions(): void
    {
        Middleware::requireAuth();
        $uid = Auth::id();
        $bidModel = new Bid();
        $won = $bidModel->wonByBidder($uid);

        $this->view('dashboard/won_auctions', [
            'pageTitle' => 'Won Auctions',
            'won'       => $won,
        ]);
    }
}
