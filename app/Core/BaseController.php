<?php
/**
 * BaseController: shared helpers for all controllers.
 * Provides view rendering, JSON, redirect, and a CSRF-guarded POST gate.
 */
abstract class BaseController
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /** Render a view with the default layout. */
    protected function view(string $view, array $data = [], ?string $header = 'layouts/header', ?string $footer = 'layouts/footer'): void
    {
        View::render($view, $data, $header, $footer);
    }

    protected function json($data, int $status = 200): void
    {
        Response::json($data, $status);
    }

    protected function redirect(string $path): void
    {
        Response::redirect($path);
    }

    /** Guard all POST actions with CSRF + optional rate limiting. */
    protected function guardPost(string $action = 'form', ?array $rate = null): void
    {
        if (!$this->request->isPost()) {
            Response::redirect('/');
        }
        Middleware::requireCsrf();
        if ($rate) {
            // Increment the counter first, then check if the limit is exceeded.
            RateLimiter::hit($action, $rate[0], $rate[1], Auth::id());
            Middleware::requireRateLimit($action, $rate[0], $rate[1]);
        }
    }

    /** Convenience: flash + redirect. */
    protected function flashRedirect(string $type, string $message, string $path): void
    {
        Session::flash($type, $message);
        Response::redirect($path);
    }
}
