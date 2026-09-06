<?php
/** WatchlistController: add/remove favorites (AJAX-friendly). */
final class WatchlistController extends BaseController
{
    public function toggle(): void
    {
        Middleware::requireAuth();
        if (!$this->request->isPost() || !Csrf::verify()) {
            Response::json(['success' => false, 'message' => 'Invalid request.'], 419);
        }
        $carId = (int) $this->request->input('car_id', 0);
        if (!$carId) Response::json(['success' => false, 'message' => 'Invalid car.'], 400);

        $added = (new Watchlist())->toggle(Auth::id(), $carId);
        Logger::audit($added ? 'watchlist_add' : 'watchlist_remove', ['car' => $carId], Auth::id());
        Response::json(['success' => true, 'watching' => $added]);
    }

    public function list(): void
    {
        Middleware::requireAuth();
        $items = (new Watchlist())->forUser(Auth::id());
        $this->view('dashboard/watchlist', [
            'pageTitle' => 'My Watchlist',
            'items'     => $items,
        ]);
    }
}
