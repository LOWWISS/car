<?php
/**
 * View: renders PHP templates with extracted variables.
 * Layouts (header/footer) are optional partials wrapped around the view.
 *
 * SECURITY (XSS Prevention): Views MUST escape all user-generated output with
 * the global e() helper (htmlspecialchars, defined in Core/helpers.php). This
 * class does not auto-escape so that intentional HTML can be rendered; the
 * convention is enforced via e().
 */
final class View
{
    private static string $base = __DIR__ . '/../Views';

    public static function render(
        string $view,
        array $data = [],
        ?string $header = 'layouts/header',
        ?string $footer = 'layouts/footer'
    ): void {
        $path = self::resolve($view);
        if ($path === null) {
            Response::notFound("View not found: {$view}");
        }
        extract($data, EXTR_SKIP);
        if ($header !== null) {
            $h = self::resolve($header);
            if ($h) require $h;
        }
        require $path;
        if ($footer !== null) {
            $f = self::resolve($footer);
            if ($f) require $f;
        }
    }

    /** Render a partial (no layout) and return as string — useful for includes. */
    public static function partial(string $view, array $data = []): string
    {
        $path = self::resolve($view);
        if ($path === null) return '';
        extract($data, EXTR_SKIP);
        ob_start();
        require $path;
        return (string) ob_get_clean();
    }

    private static function resolve(string $view): ?string
    {
        $file = self::$base . '/' . str_replace('.', '/', $view) . '.php';
        return is_file($file) ? $file : null;
    }
}
