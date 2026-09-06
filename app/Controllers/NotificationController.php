<?php
/** NotificationController: list + mark-read (AJAX-friendly). */
final class NotificationController extends BaseController
{
    public function index(): void
    {
        Middleware::requireAuth();
        $model = new Notification();
        $this->view('dashboard/notifications', [
            'pageTitle' => 'Notifications',
            'items'     => $model->forUser(Auth::id(), 50),
        ]);
    }

    public function markRead(): void
    {
        Middleware::requireAuth();
        if (!$this->request->isPost() || !Csrf::verify()) {
            Response::json(['success' => false, 'message' => 'Invalid request.'], 419);
        }
        (new Notification())->markAllRead(Auth::id());
        Response::json(['success' => true]);
    }

    /** GET /notifications/unread-count — for the navbar bell badge (AJAX). */
    public function unreadCount(): void
    {
        if (Auth::guest()) Response::json(['count' => 0]);
        $count = (new Notification())->unreadCount(Auth::id());
        Response::json(['count' => $count]);
    }
}
