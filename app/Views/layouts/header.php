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
$sbActive = $sbBids = $sbWatch = 0;
if ($currentUser) {
    try { $unread = (new Notification())->unreadCount((int) $currentUser['id']); } catch (\Throwable $e) { $unread = 0; }
    try {
        $uid = (int) $currentUser['id'];
        $db = Database::getInstance();
        $sbActive = (int) $db->query("SELECT COUNT(*) FROM cars WHERE status='active' AND auction_end > NOW()")->fetchColumn();
        $sbBids   = (int) $db->query("SELECT COUNT(*) FROM bids WHERE bidder_id = $uid")->fetchColumn();
        $sbWatch  = (int) $db->query("SELECT COUNT(*) FROM watchlist WHERE user_id = $uid")->fetchColumn();
    } catch (\Throwable $e) { $sbActive = $sbBids = $sbWatch = 0; }
}
$pageTitle = $pageTitle ?? 'Car Auction System';

// Current path — used for guest navbar active-link highlighting.
try { $currentPath = (new Request())->path(); } catch (\Throwable $e) { $currentPath = '/'; }
$guestLinks = [
    '/'              => 'Home',
    '/cars'          => 'Browse',
    '/cars/bidding'  => 'Bidding',
    '/cars/featured' => 'Featured Cars',
];
$activeLink = '';
$bestMatch = -1;
foreach ($guestLinks as $p => $label) {
    $matches = $p === '/' ? $currentPath === '/'
                          : ($currentPath === $p || str_starts_with($currentPath, $p . '/'));
    if ($matches && strlen($p) > $bestMatch) { $bestMatch = strlen($p); $activeLink = $p; }
}

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
// Sidebar nav helpers (used by the logged-in aside below).
$sbBadge = function (int $n, bool $accent = false): string {
    if ($n <= 0) return '';
    $cls = $accent ? 'bg-brand/25 text-indigo-300' : 'bg-white/10 text-zinc-300';
    return '<span class="sidebar-label ml-auto rounded-md px-1.5 py-0.5 text-[11px] font-semibold ' . $cls . '">' . $n . '</span>';
};
$sbLink = function (string $href, string $label, string $icon, string $right = '', string $stagger = '') use ($appUrl): string {
    return '<a href="' . $appUrl . $href . '" class="sidebar-link group flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium text-zinc-400 hover:bg-white/5 hover:text-white transition animate-slide-in-left ' . $stagger . '">'
        . '<svg class="w-[18px] h-[18px] shrink-0 text-zinc-500 transition group-hover:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="' . $icon . '"/></svg>'
        . '<span class="sidebar-label truncate">' . e($label) . '</span>' . $right . '</a>';
};
$sbSection = function (string $label, string $plusHref = '', string $plusTitle = '') use ($appUrl): string {
    $plus = '';
    if ($plusHref !== '') {
        $plus = '<a href="' . $appUrl . $plusHref . '" class="p-1 rounded-md text-zinc-500 hover:text-white hover:bg-white/10 transition" title="' . e($plusTitle) . '" aria-label="' . e($plusTitle) . '">'
            . '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg></a>';
    }
    return '<div class="sidebar-label flex items-center justify-between px-2.5 pb-1.5">'
        . '<span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">' . e($label) . '</span>' . $plus . '</div>';
};
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
    <link rel="stylesheet" href="<?= e($assetBase) ?>/css/app.css?v=9">
</head>
<body class="h-full bg-slate-50 text-slate-800 font-sans antialiased min-h-screen">

<?php if (Auth::check()): ?>
<!-- ===== Sidebar (full height, left of header) ===== -->
<div class="flex min-h-screen">
  <aside id="sidebar" class="fixed top-0 left-0 z-50 w-64 h-screen bg-[#0f0f12] border-r border-white/10 transform -translate-x-full lg:translate-x-0 transition-all duration-200 overflow-y-auto shrink-0 flex flex-col">
    <!-- Workspace header -->
    <div id="sidebarHeader" class="h-14 flex items-center gap-2 px-3.5 border-b border-white/10 shrink-0">
      <a id="sidebarLogo" href="<?= $appUrl ?><?= Auth::is('admin') ? '/admin' : '/dashboard' ?>" class="flex items-center gap-2.5 min-w-0 group" aria-label="CarAuction home">
        <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand to-blue-500 flex items-center justify-center shadow-sm shadow-brand/25 shrink-0 transition group-hover:shadow-md group-hover:shadow-brand/40 group-hover:scale-105">
          <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
          </svg>
        </span>
        <span id="sidebarLogoText" class="font-semibold text-[15px] tracking-tight text-white truncate">CarAuction</span>
      </a>
      <button type="button" data-toggle="collapse-sidebar" class="ml-auto p-1.5 rounded-lg hover:bg-white/10 text-zinc-500 hover:text-white transition shrink-0" aria-label="Collapse sidebar" title="Collapse sidebar">
        <svg id="collapseIcon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
        </svg>
      </button>
    </div>

    <!-- Search -->
    <div class="px-3 pt-3 sidebar-label">
      <form action="<?= $appUrl ?>/cars" method="get" role="search">
        <div class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-2.5 py-[7px] focus-within:border-white/25 focus-within:bg-white/[0.08] focus-within:ring-2 focus-within:ring-white/10 transition">
          <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
          </svg>
          <input id="sidebarSearch" name="q" type="text" placeholder="Search cars" autocomplete="off"
                 class="w-full bg-transparent text-sm text-zinc-200 placeholder:text-zinc-500 focus:outline-none">
          <kbd class="hidden sm:inline shrink-0 rounded border border-white/10 bg-white/5 px-1.5 py-px text-[10px] font-semibold text-zinc-500">/</kbd>
        </div>
      </form>
    </div>

    <nav class="p-3 space-y-6">
      <!-- Primary -->
      <div class="space-y-0.5">
        <?= $sbLink(Auth::is('admin') ? '/admin' : '/dashboard', 'Home', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', '', 'stagger-1') ?>
        <?= $sbLink('/notifications', 'Notifications', 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1', $sbBadge($unread, true), 'stagger-2') ?>
        <?php if (!Auth::is('admin')): ?>
          <?= $sbLink('/dashboard/my-bids', 'My Bids', 'M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 007.5 7.5h4.5a7.5 7.5 0 007.5-7.5v-4a1.5 1.5 0 00-3 0m-6-1.5v-7a1.5 1.5 0 013 0v7m0 0v-3a1.5 1.5 0 013 0v3', $sbBadge($sbBids), 'stagger-3') ?>
        <?php endif; ?>
      </div>

      <!-- Marketplace -->
      <div>
        <?= $sbSection('Marketplace', Auth::inRoles(['seller', 'admin']) ? '/cars/create' : '', 'New listing') ?>
        <div class="space-y-0.5">
          <?= $sbLink('/cars', 'Browse Cars', 'M4 6h16M4 10h16M4 14h16M4 18h16', '', 'stagger-4') ?>
          <?= $sbLink('/cars/bidding', 'Live Bidding', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', $sbBadge($sbActive), 'stagger-5') ?>
          <?= $sbLink('/cars/featured', 'Featured', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l-3.976-2.888a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', '', 'stagger-6') ?>
        </div>
      </div>

      <?php if (!Auth::is('admin')): ?>
      <!-- My account -->
      <div>
        <?= $sbSection('My Account') ?>
        <div class="space-y-0.5">
          <?= $sbLink('/dashboard/won-auctions', 'Won Auctions', 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z', '', 'stagger-7') ?>
          <?= $sbLink('/dashboard/watchlist', 'Watchlist', 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', $sbBadge($sbWatch), 'stagger-8') ?>
          <?= $sbLink('/transactions', 'Transactions', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', '', 'stagger-9') ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (Auth::is('admin')): ?>
      <!-- Admin -->
      <div>
        <?= $sbSection('Admin') ?>
        <div class="space-y-0.5">
          <?= $sbLink('/admin', 'Dashboard', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', '', 'stagger-7') ?>
          <?= $sbLink('/admin/users', 'Users', 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 11-8 0 4 4 0 018 0zm6-3a4 4 0 11-8 0 4 4 0 018 0z', '', 'stagger-8') ?>
          <?= $sbLink('/admin/logs', 'Audit Logs', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', '', 'stagger-9') ?>
        </div>
      </div>
      <?php endif; ?>
    </nav>

    <!-- Sidebar footer with user info -->
    <div class="mt-auto shrink-0">
      <div id="sidebarFooter" class="border-t border-white/10 bg-[#0f0f12] p-3.5 flex items-center gap-3">
        <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand to-blue-500 text-white flex items-center justify-center text-sm font-semibold shrink-0 shadow-sm shadow-brand/25">
          <?= e(strtoupper(substr($currentUser['name'], 0, 1))) ?>
        </span>
        <div class="sidebar-label min-w-0">
          <div class="text-sm font-medium text-zinc-100 truncate"><?= e($currentUser['name']) ?></div>
          <div class="text-xs text-zinc-500 truncate"><?= e($currentUser['email'] ?? '') ?></div>
          <span class="inline-flex items-center rounded-md bg-white/10 px-1.5 py-0.5 text-[11px] font-semibold text-zinc-300 mt-1"><?= e(ucfirst(Auth::role())) ?></span>
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
<header id="siteHeader" class="fixed top-0 left-0 right-0 <?= Auth::check() ? 'lg:left-64' : '' ?> z-40 backdrop-blur border-b <?= Auth::check() ? 'bg-[#0f0f12] border-white/10' : 'bg-white/90 border-slate-200' ?>">
  <nav class="px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
    <?php if (Auth::check()): ?>
      <!-- Mobile sidebar toggle (logged-in only) -->
      <button type="button" data-toggle="mobile-sidebar" class="lg:hidden p-2 rounded-lg hdr-icon-btn text-zinc-400 hover:bg-white/10 hover:text-white transition" aria-label="Toggle menu">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Page indicator (title + subtitle) -->
      <div id="navPageTitle" class="min-w-0 hidden sm:block">
        <div class="text-sm font-semibold hdr-title text-zinc-100 truncate leading-tight"><?= e($pageTitle) ?></div>
        <div id="navPageSubtitle" class="text-xs hdr-sub text-zinc-500 truncate leading-tight"><?= e($pageSubtitle) ?></div>
      </div>
    <?php else: ?>
      <!-- Logo (guests only) -->
      <a href="<?= $appUrl ?>/" class="flex items-center gap-2.5 shrink-0 group" aria-label="CarAuction home">
        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand to-blue-500 flex items-center justify-center shadow-md shadow-brand/25 transition duration-200 group-hover:shadow-lg group-hover:shadow-brand/40 group-hover:scale-105">
          <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
          </svg>
        </span>
        <span class="font-bold text-lg tracking-tight hdr-logo text-slate-900">Car<span class="text-brand hdr-logo-accent">Auction</span></span>
      </a>
      <!-- Center: Navigation (guests only, no sidebar) -->
      <div class="hidden md:flex items-center justify-center gap-1 mx-auto">
        <?php foreach ($guestLinks as $p => $label): ?>
          <a href="<?= $appUrl . $p ?>" class="nav-link relative px-3.5 py-2 rounded-lg text-sm font-medium transition<?= $activeLink === $p ? ' active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Right side -->
    <div class="flex items-center gap-3 ml-auto shrink-0">

      <?php if (Auth::check()): ?>
        <!-- Notifications bell -->
        <a href="<?= $appUrl ?>/notifications" class="relative p-2 rounded-full hdr-icon-btn text-zinc-400 hover:bg-white/10 hover:text-white transition" aria-label="Notifications">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
          </svg>
          <?php if ($unread > 0): ?>
            <span class="absolute -top-0.5 -right-0.5 bg-auction text-white text-[10px] font-semibold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1"><?= (int)$unread ?></span>
          <?php endif; ?>
        </a>
        <!-- Logout button -->
        <form method="post" action="<?= $appUrl ?>/logout">
          <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
          <button type="submit" class="hdr-logout flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium text-red-400 border border-red-400/30 hover:bg-red-500/10 transition" aria-label="Log out" title="Log out">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="hidden sm:inline">Log out</span>
          </button>
        </form>
      <?php else: ?>
        <a href="<?= $appUrl ?>/login" class="hdr-login hidden sm:inline-block text-sm font-medium text-slate-700 hover:text-brand px-3 py-2 transition">Log in</a>
        <a href="<?= $appUrl ?>/register" class="text-sm font-semibold px-5 py-2 rounded-full text-white bg-gradient-to-r from-brand to-blue-500 shadow-md shadow-brand/25 hover:shadow-lg hover:shadow-brand/40 hover:brightness-110 hover:-translate-y-px transition">Get Started</a>
        <!-- Mobile menu toggle (guests only) -->
        <button type="button" data-toggle="guest-menu" class="md:hidden p-2 rounded-lg hdr-icon-btn text-slate-600 hover:bg-slate-100 transition" aria-label="Toggle menu">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      <?php endif; ?>
    </div>
  </nav>

  <?php if (Auth::guest()): ?>
  <!-- Mobile dropdown menu (guests only) -->
  <div id="guestMenu" class="md:hidden hidden bg-white/95 backdrop-blur-xl border-t border-slate-200 shadow-lg">
    <div class="px-4 py-3 space-y-1">
      <?php foreach ($guestLinks as $p => $label): ?>
        <a href="<?= $appUrl . $p ?>" class="nav-link relative block px-3.5 py-2.5 rounded-lg text-sm font-medium transition<?= $activeLink === $p ? ' active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <div class="pt-2 flex gap-2">
        <a href="<?= $appUrl ?>/login" class="btn-outline flex-1 justify-center">Log in</a>
        <a href="<?= $appUrl ?>/register" class="btn-primary flex-1 justify-center">Get Started</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</header>

<!-- ===== Flash messages (hidden — JS upgrades them to the centered modal) ===== -->
<div class="pt-16">
<?php if (Session::hasFlash('success') || Session::hasFlash('error') || (isset($_GET['error']) && $_GET['error'] === 'csrf')): ?>
  <div hidden>
    <?php if (Session::hasFlash('success')): ?>
      <div class="rounded-lg bg-green-50 border border-green-200 flash-slide"><?= e(Session::getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Session::hasFlash('error')): ?>
      <div class="rounded-lg bg-red-50 border border-red-200 flash-slide"><?= e(Session::getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'csrf'): ?>
      <div class="rounded-lg bg-red-50 border border-red-200 flash-slide">Security token expired. Please try again.</div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- ===== Toast container (for AJAX notifications) ===== -->
<div id="toast" class="fixed top-20 left-1/2 -translate-x-1/2 z-50 space-y-2 flex flex-col items-center"></div>

<main class="flex-1 min-w-0">
