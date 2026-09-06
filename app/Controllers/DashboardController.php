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
