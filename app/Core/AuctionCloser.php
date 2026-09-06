<?php
/**
 * AuctionCloser: orchestrates the end-of-auction lifecycle.
 *
 * The DB (cars.auction_end) is the source of truth for auction timing, not the
 * frontend countdown. This helper is invoked opportunistically from page loads
 * (home, catalog, dashboard) to close auctions whose end time has passed.
 *
 * For each expired active auction:
 *   - If a highest bid exists AND the reserve (if set) is met:
 *       -> car becomes 'sold'
 *       -> a transaction is created (status 'payment_pending')
 *       -> seller + winner are notified (in-app + email)
 *   - If no bids, OR the reserve was not met:
 *       -> car becomes 'closed' (no sale)
 *       -> seller is notified
 *
 * No fake winner is ever created — the winner always comes from the actual
 * highest valid bid stored in the database. Idempotent: re-running never
 * duplicates transactions or notifications for already-processed cars.
 */
final class AuctionCloser
{
    /**
     * Close all expired active auctions. Returns ['closed' => int, 'sold' => int].
     */
    public static function run(): array
    {
        $cars = (new Car())->expiredActiveAuctions();
        if (!$cars) return ['closed' => 0, 'sold' => 0];

        $carModel    = new Car();
        $txModel     = new Transaction();
        $notif       = new Notification();

        $sold = 0;
        $closed = 0;

        foreach ($cars as $car) {
            $carId      = (int) $car['id'];
            $sellerId   = (int) $car['seller_id'];
            $hasBid     = !empty($car['highest_bid']) && !empty($car['highest_bidder_id']);
            $reserveMet = $hasBid
                && ($car['reserve_price'] === null
                    || (float) $car['highest_bid'] >= (float) $car['reserve_price']);

            if ($hasBid && $reserveMet) {
                // Winner determined from the actual highest valid bid.
                $winnerId    = (int) $car['highest_bidder_id'];
                $winAmount   = (float) $car['highest_bid'];
                $winBidId    = $car['highest_bid_id'] ? (int) $car['highest_bid_id'] : null;

                $carModel->setStatus($carId, 'sold');
                $txModel->createForWin($carId, $sellerId, $winnerId, $winBidId, $winAmount);

                $notif->send(
                    $sellerId,
                    'Your listing "' . $car['title'] . '" was sold for ' . number_format($winAmount, 2) . '. A transaction has been created.',
                    'sale', '/transactions'
                );
                $notif->send(
                    $winnerId,
                    'Congratulations! You won "' . $car['title'] . '" with a bid of ' . number_format($winAmount, 2) . '. Payment is now required.',
                    'won', '/transactions'
                );

                // Email the winner (best-effort; Mailer falls back to mail()).
                if (!empty($car['highest_bidder_email'])) {
                    $url = rtrim((string) Config::get('APP_URL', ''), '/') . '/transactions';
                    $html = "<p>Congratulations! You won <strong>" . e($car['title']) . "</strong>.</p>"
                          . "<p>Winning bid: " . number_format($winAmount, 2) . "</p>"
                          . "<p><a href=\"" . $url . "\">View your transaction and complete payment</a></p>";
                    Mailer::send($car['highest_bidder_email'], 'You won the auction!', $html);
                }

                Logger::audit('auction_won', ['car' => $carId, 'winner' => $winnerId, 'amount' => $winAmount], $winnerId);
                $sold++;
            } else {
                // No bids or reserve not met — no sale. Do NOT create a winner.
                $carModel->setStatus($carId, 'closed');

                $reason = !$hasBid
                    ? 'no bids were placed'
                    : 'the reserve price was not met';
                $notif->send(
                    $sellerId,
                    'Your auction for "' . $car['title'] . '" has ended (' . $reason . '). The listing is now closed.',
                    'listing', '/cars/view/' . $carId
                );
                Logger::audit('auction_closed_no_sale', ['car' => $carId, 'reason' => $reason], $sellerId);
                $closed++;
            }
        }

        return ['closed' => $closed, 'sold' => $sold];
    }
}
