<?php
/** HomeController: homepage + static-ish landing content. */
final class HomeController extends BaseController
{
    public function index(): void
    {
        // Close any expired auctions on each homepage hit (cheap cron substitute).
        // Determines winners, creates transactions, and notifies parties.
        AuctionCloser::run();

        $carModel = new Car();
        $featured = $carModel->featured(8);
        $endingSoon = $carModel->endingSoon(6);

        $this->view('home/index', [
            'featured'    => $featured,
            'endingSoon'  => $endingSoon,
            'pageTitle'   => 'Home',
        ]);
    }
}
