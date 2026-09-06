<?php
/**
 * Layout header. Sets up <head>, Tailwind config (Poppins default font),
 * navbar, flash messages, and toast container.
 *
 * SECURITY (XSS): all dynamic output uses e() (htmlspecialchars). The inline
 * Tailwind config script is allowed via a per-request CSP nonce.
 */
$nonce = SecurityHeaders::cspNonce();
$appUrl = rtrim(Config::get('APP_URL', '/car'), '/');
$assetBase = $appUrl . '/assets';
$currentUser = Auth::user();
$unread = 0;
if ($currentUser) {
    try { $unread = (new Notification())->unreadCount((int) $currentUser['id']); } catch (\Throwable $e) { $unread = 0; }
}
$pageTitle = $pageTitle ?? 'Car Auction System';

// Page subtitle/description map — shown in the navbar header.
// For dynamic subtitles, falls back to role-aware logic below.
$pageSubtitle = $pageSubtitle ?? '';
if ($pageSubtitle === '' && $currentUser) {
    $role = Auth::role();
    $subtitleMap = [
        'Admin Dashboard'   => 'System overview and management',
        'User Management'   => 'Manage user roles and account status',
        'Audit Logs'        => 'System activity and security event history',
        'Dashboard'         => 'Your auction activity at a glance',
        'My Bids'           => 'Track all your active and past bids',
        'Won Auctions'      => 'Auctions you\'ve won and need to complete payment for',
        'My Watchlist'      => 'Cars you\'re tracking',
        'Notifications'     => 'Stay updated on your bids and auctions',
        'Browse Cars'       => 'Find your next vehicle from our auction listings',
        'Live Bidding'      => 'Active auctions ending soonest. Place your bid before time runs out!',
        'Featured Cars'     => 'Hand-picked and newest listings on CarAuction',
        'Create Listing'    => 'List your car for auction and start receiving bids',
        'Edit Listing'      => 'Update your car listing details',
        'Home'              => 'Welcome to CarAuction',
    ];
    $pageSubtitle = $subtitleMap[$pageTitle] ?? '';

    // Role-dependent subtitles
    if ($pageTitle === 'Transactions') {
        $pageSubtitle = $role === 'admin'   ? 'All platform sales and purchases'
                      : ($role === 'seller' ? 'Your sales and purchases'
                      : 'Auctions you\'ve won and need to complete payment for');
    }
    // Transaction detail page: "Transaction #123"
    if (str_starts_with($pageTitle, 'Transaction #')) {
        $pageSubtitle = 'View and manage transaction details';
    }
    // Car detail page (pageTitle is the car title)
    if ($pageTitle !== 'Browse Cars' && $pageTitle !== 'Live Bidding' && $pageTitle !== 'Featured Cars'
        && $pageTitle !== 'Create Listing' && $pageTitle !== 'Edit Listing'
        && !isset($subtitleMap[$pageTitle]) && !str_starts_with($pageTitle, 'Transaction #')) {
        $pageSubtitle = 'View listing details and place your bids';
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> &middot; <?= e(Config::get('APP_NAME', 'Car Auction')) ?></title>
    <meta name="description" content="Bid on and buy cars through secure online auctions.">
    <meta name="page-subtitle" content="<?= e($pageSubtitle) ?>">

    <!-- Google Fonts: Poppins (default site typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Play CDN (config applied below via nonce'd inline script) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script nonce="<?= e($nonce) ?>">
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: {
              auction: { DEFAULT: '#ef4444', dark: '#b91c1c' },
              brand:   { DEFAULT: '#4f46e5', dark: '#4338ca', light: '#e0e7ff' },
            },
          },
        },
      };
    </script>

    <!-- App CSS (reusable component classes via @apply) -->
    <link rel="stylesheet" href="<?= e($assetBase) ?>/css/app.css?v=4">
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased min-h-screen">

<?php if (Auth::check()): ?>
<!-- ===== Sidebar (full height, left of header) ===== -->
<div class="flex min-h-screen">
  <aside id="sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 transition-all duration-200 overflow-y-auto shrink-0 flex flex-col">
    <div id="sidebarHeader" class="h-16 flex items-center gap-2 px-4 border-b border-slate-200 font-bold text-lg text-brand-dark shrink-0">
      <span id="sidebarLogo" class="flex items-center gap-2 overflow-hidden">
        <svg class="w-7 h-7 text-brand shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
        </svg>
        <span id="sidebarLogoText">CarAuction</span>
      </span>
      <button type="button" data-toggle="collapse-sidebar" class="ml-auto p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-brand transition shrink-0" aria-label="Collapse sidebar" title="Collapse sidebar">
        <svg id="collapseIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
      </button>
    </div>
    <nav class="p-4 space-y-1">
      <a href="<?= $appUrl ?><?= Auth::is('admin') ? '/admin' : '/dashboard' ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-1">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <span class="sidebar-label">Home</span>
      </a>
      <?php if (!Auth::is('admin')): ?>
      <!-- Customer-only links -->
      <a href="<?= $appUrl ?>/cars" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-2">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
        <span class="sidebar-label">Browse</span>
      </a>
      <a href="<?= $appUrl ?>/cars/bidding" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-3">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="sidebar-label">Bidding</span>
      </a>
      <a href="<?= $appUrl ?>/cars/featured" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-4">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        <span class="sidebar-label">Featured Cars</span>
      </a>
      <a href="<?= $appUrl ?>/dashboard/my-bids" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-5">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span class="sidebar-label">My Bids</span>
      </a>
      <a href="<?= $appUrl ?>/dashboard/won-auctions" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-6">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        <span class="sidebar-label">Won Auctions</span>
      </a>
      <a href="<?= $appUrl ?>/transactions" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-7">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <span class="sidebar-label">Transactions</span>
      </a>
      <?php endif; ?>

      <?php if (Auth::is('admin')): ?>
      <!-- Admin-only links -->
      <a href="<?= $appUrl ?>/admin/users" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-8">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        <span class="sidebar-label">Manage Users</span>
      </a>
      <a href="<?= $appUrl ?>/admin/logs" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition animate-slide-in-left stagger-9">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        <span class="sidebar-label">Audit Logs</span>
      </a>
      <?php endif; ?>
    </nav>

    <!-- Sidebar footer with user info (matches main footer height) -->
    <div class="mt-auto shrink-0">
      <div id="sidebarFooter" class="border-t border-slate-200 bg-white p-4 flex items-center gap-3" style="height: 130px;">
        <span class="w-9 h-9 rounded-full bg-brand text-white flex items-center justify-center text-sm font-semibold shrink-0">
          <?= e(strtoupper(substr($currentUser['name'], 0, 1))) ?>
        </span>
        <div class="sidebar-label min-w-0">
          <div class="text-sm font-medium text-slate-800 truncate"><?= e($currentUser['name']) ?></div>
          <div class="text-xs text-slate-400 truncate"><?= e($currentUser['email'] ?? '') ?></div>
          <span class="badge badge-info mt-1"><?= e(ucfirst(Auth::role())) ?></span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Sidebar overlay (mobile) -->
  <div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/40 lg:hidden hidden" data-toggle="mobile-sidebar"></div>

  <!-- Right column: header + content -->
  <div id="mainColumn" class="flex-1 flex flex-col min-w-0 lg:ml-64">
<?php endif; ?>

<!-- ===== Navbar ===== -->
<header id="siteHeader" class="fixed top-0 left-0 right-0 <?= Auth::check() ? 'lg:left-64' : '' ?> z-40 bg-white/90 backdrop-blur border-b border-slate-200">
  <nav class="px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
    <?php if (Auth::check()): ?>
      <!-- Mobile sidebar toggle (logged-in only) -->
      <button type="button" data-toggle="mobile-sidebar" class="lg:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="Toggle menu">
        <svg class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Page indicator (title + subtitle) -->
      <div id="navPageTitle" class="min-w-0 hidden sm:block">
        <div class="text-sm font-semibold text-slate-800 truncate leading-tight"><?= e($pageTitle) ?></div>
        <div id="navPageSubtitle" class="text-xs text-slate-400 truncate leading-tight"><?= e($pageSubtitle) ?></div>
      </div>
    <?php else: ?>
      <!-- Logo (guests only) -->
      <span class="flex items-center gap-2 font-bold text-lg text-brand-dark shrink-0 cursor-default select-none">
        <svg class="w-7 h-7 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
        </svg>
        CarAuction
      </span>
      <!-- Center: Navigation (guests only, no sidebar) -->
      <div class="flex items-center justify-center gap-1 mx-auto">
        <a href="<?= $appUrl ?>/" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition">Home</a>
        <a href="<?= $appUrl ?>/cars" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition">Browse</a>
        <a href="<?= $appUrl ?>/cars/bidding" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition">Bidding</a>
        <a href="<?= $appUrl ?>/cars/featured" class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-brand hover:bg-slate-100 transition">Featured Cars</a>
      </div>
    <?php endif; ?>

    <!-- Right side -->
    <div class="flex items-center gap-3 ml-auto shrink-0">

      <?php if (Auth::check()): ?>
        <!-- Notifications bell -->
        <a href="<?= $appUrl ?>/notifications" class="relative p-2 rounded-full hover:bg-slate-100" aria-label="Notifications">
          <svg class="w-6 h-6 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
          </svg>
          <?php if ($unread > 0): ?>
            <span class="absolute -top-0.5 -right-0.5 bg-auction text-white text-[10px] font-semibold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"><?= (int)$unread ?></span>
          <?php endif; ?>
        </a>
        <!-- Logout button -->
        <form method="post" action="<?= $appUrl ?>/logout">
          <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
          <button type="submit" class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-auction border border-red-200 hover:bg-red-50 transition" aria-label="Log out" title="Log out">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="hidden sm:inline">Log out</span>
          </button>
        </form>
      <?php else: ?>
        <a href="<?= $appUrl ?>/register" class="text-sm font-medium text-slate-700 hover:text-brand">Sign up</a>
        <a href="<?= $appUrl ?>/login" class="btn-primary text-sm">Log in</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<!-- ===== Flash messages ===== -->
<div class="pt-16">
<?php if (Session::hasFlash('success')): ?>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm flash-slide animate-fade-in-up"><?= e(Session::getFlash('success')) ?></div>
  </div>
<?php endif; ?>
<?php if (Session::hasFlash('error')): ?>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flash-slide animate-fade-in-up"><?= e(Session::getFlash('error')) ?></div>
  </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
    <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flash-slide animate-fade-in-up">Security token expired. Please try again.</div>
  </div>
<?php endif; ?>

<!-- ===== Toast container (for AJAX notifications) ===== -->
<div id="toast" class="fixed bottom-4 right-4 z-50 space-y-2"></div>

<main class="flex-1 min-w-0">
