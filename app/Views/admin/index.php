<?php
/**
 * Admin dashboard — dark analytics overview.
 * Layout: trend stat cards -> activity area chart -> tabbed data table.
 */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$a = $analytics;

// Trend badge: "+12.5%" / "-20%" pill like the reference.
$trendBadge = function (int $p): string {
    $up = $p >= 0;
    $cls = $up ? 'text-emerald-400' : 'text-rose-400';
    $path = $up
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6 6 4-4 8 8m0 0h-5m5 0V12"/>';
    return '<span class="inline-flex items-center gap-1 rounded-md border border-white/10 px-1.5 py-0.5 text-xs font-medium ' . $cls . '">'
         . '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">' . $path . '</svg>'
         . ($up ? '+' : '') . $p . '%</span>';
};

$statCard = function (string $label, string $value, int $pct, string $trendText, string $caption) use ($trendBadge): void {
    $up = $pct >= 0;
    ?>
    <div class="rounded-xl border border-white/[0.07] bg-[#0f0f12] p-5">
      <div class="flex items-center justify-between gap-2">
        <span class="text-sm text-zinc-400"><?= e($label) ?></span>
        <?= $trendBadge($pct) ?>
      </div>
      <div class="mt-2 text-[1.7rem] leading-tight font-semibold text-white tabular-nums"><?= $value ?></div>
      <div class="mt-4 flex items-center gap-1.5 text-sm font-medium text-zinc-200">
        <?= e($trendText) ?>
        <svg class="w-4 h-4 <?= $up ? 'text-emerald-400' : 'text-rose-400 rotate-180' ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5"/>
        </svg>
      </div>
      <p class="mt-1 text-xs text-zinc-500"><?= e($caption) ?></p>
    </div>
    <?php
};

$statusPill = function (string $status): string {
    $map = [
        'active'   => ['Live',     'text-emerald-300 border-emerald-400/25 bg-emerald-400/10',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.6 4.6A9 9 0 1112 3a9 9 0 018.6 9.6z"/>'],
        'pending'  => ['Pending',  'text-amber-300 border-amber-400/25 bg-amber-400/10',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'sold'     => ['Sold',     'text-sky-300 border-sky-400/25 bg-sky-400/10',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>'],
        'closed'   => ['Closed',   'text-zinc-400 border-white/15 bg-white/5',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
        'rejected' => ['Rejected', 'text-rose-300 border-rose-400/25 bg-rose-400/10',
            '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>'],
    ];
    [$label, $cls, $icon] = $map[$status] ?? $map['closed'];
    return '<span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium whitespace-nowrap ' . $cls . '">'
         . '<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">' . $icon . '</svg>'
         . e($label) . '</span>';
};

$kebab = '<button type="button" data-menu-btn class="p-1.5 rounded-md text-zinc-500 hover:text-white hover:bg-white/10 transition" aria-label="Row actions">'
       . '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">'
       . '<circle cx="12" cy="5" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="12" cy="19" r="1.6"/></svg></button>';

$menuItem = 'class="flex items-center gap-2 rounded-md px-2.5 py-2 text-sm text-zinc-300 hover:bg-white/10 hover:text-white transition"';

$money = fn($v) => '&#8369;' . number_format((float) $v, 0);

$pagerFooter = function (int $total, bool $extraLink = false, string $extraHref = '', string $extraLabel = ''): void {
    ?>
    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t border-white/[0.06]">
      <p class="text-xs text-zinc-500" data-sel-label>0 of <?= (int)$total ?> row(s) selected.</p>
      <div class="flex items-center gap-4 ml-auto">
        <?php if ($extraLink): ?>
          <a href="<?= e($extraHref) ?>" class="text-xs text-zinc-400 hover:text-white transition"><?= e($extraLabel) ?></a>
        <?php endif; ?>
        <label class="hidden sm:flex items-center gap-2 text-xs text-zinc-500">Rows per page
          <select data-page-size class="bg-[#1c1c22] border border-white/10 rounded-md px-1.5 py-1 text-xs text-zinc-300 focus:outline-none">
            <option>10</option><option>20</option><option>50</option>
          </select>
        </label>
        <span class="text-xs text-zinc-500 whitespace-nowrap" data-page-label>Page 1 of 1</span>
        <div class="flex items-center gap-1">
          <button type="button" data-nav="first" class="p-1.5 rounded-md border border-white/10 text-zinc-400 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition" aria-label="First page">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 17l-5-5 5-5m8 10l-5-5 5-5"/></svg>
          </button>
          <button type="button" data-nav="prev" class="p-1.5 rounded-md border border-white/10 text-zinc-400 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition" aria-label="Previous page">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button type="button" data-nav="next" class="p-1.5 rounded-md border border-white/10 text-zinc-400 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition" aria-label="Next page">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
          <button type="button" data-nav="last" class="p-1.5 rounded-md border border-white/10 text-zinc-400 hover:bg-white/10 disabled:opacity-30 disabled:hover:bg-transparent transition" aria-label="Last page">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 17l5-5-5-5M6 17l5-5-5-5"/></svg>
          </button>
        </div>
      </div>
    </div>
    <?php
};

$th = fn(string $col, string $label, bool $right = false) =>
    '<th data-col="' . $col . '" data-label="' . e($label) . '" class="px-3 py-3 text-xs font-medium text-zinc-500 whitespace-nowrap ' . ($right ? 'text-right' : 'text-left') . '">' . e($label) . '</th>';
$td = fn(string $col, string $extra = '') => 'data-col="' . $col . '" class="px-3 py-3 text-left ' . $extra . '"';
?>

<div id="adminDash" class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <!-- ===== Stat cards ===== -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <?php
        $statCard('Gross volume', $money($a['gmv']), $a['gmv_pct'], $a['gmv_pct'] >= 0 ? 'Trending up this month' : 'Trending down this month', 'Total bid value across all auctions');
        $statCard('New users', number_format($a['users30']), $a['users_pct'], $a['users_pct'] >= 0 ? 'Signups trending up' : 'Acquisition needs attention', 'Registered in the last 30 days');
        $statCard('Bids placed', number_format($a['bids30']), $a['bids_pct'], $a['bids_pct'] >= 0 ? 'Strong bidding activity' : 'Bidding is slowing down', 'Placed in the last 30 days');
        $statCard('Live auctions', number_format($a['active_auctions']), $a['listings_pct'], $a['listings_pct'] >= 0 ? 'Steady listing growth' : 'Fewer new listings', 'Accepting bids right now');
      ?>
    </div>

    <!-- ===== Activity chart ===== -->
    <section class="rounded-xl border border-white/[0.07] bg-[#0f0f12] overflow-hidden">
      <div class="flex flex-wrap items-center justify-between gap-3 px-5 pt-5 pb-4">
        <div>
          <h2 class="text-base font-semibold text-white">Bidding activity</h2>
          <p class="mt-0.5 text-xs text-zinc-500 flex flex-wrap items-center gap-x-4 gap-y-1">
            <span>Daily totals for the last <span id="admChartDays">90</span> days</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-white/80"></span>Bids</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-zinc-500"></span>New listings</span>
          </p>
        </div>
        <div class="flex rounded-lg bg-white/[0.04] border border-white/[0.06] p-1 gap-1" role="tablist" aria-label="Chart range">
          <button type="button" data-range="90" class="adm-range rounded-md px-3 py-1.5 text-xs font-medium bg-white/10 text-white transition">Last 3 months</button>
          <button type="button" data-range="30" class="adm-range rounded-md px-3 py-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">Last 30 days</button>
          <button type="button" data-range="7" class="adm-range rounded-md px-3 py-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">Last 7 days</button>
        </div>
      </div>
      <div id="admChart" class="relative h-[240px] sm:h-[280px] px-2 pb-2 cursor-crosshair"></div>
    </section>

    <!-- ===== Data table ===== -->
    <section class="rounded-xl border border-white/[0.07] bg-[#0f0f12] overflow-hidden">
      <!-- Toolbar -->
      <div class="flex flex-wrap items-center justify-between gap-3 px-4 pt-4 pb-3">
        <div class="flex flex-wrap rounded-lg bg-white/[0.04] border border-white/[0.06] p-1 gap-1" role="tablist" aria-label="Tables">
          <button type="button" data-adm-tab="listings" class="adm-tab rounded-md px-3 py-1.5 text-xs font-medium bg-white/10 text-white transition">Listings <span class="ml-1 text-zinc-500"><?= (int)$a['total_cars'] ?></span></button>
          <button type="button" data-adm-tab="pending" class="adm-tab rounded-md px-3 py-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">Pending review <span class="ml-1 text-amber-400"><?= count($pending) ?></span></button>
          <button type="button" data-adm-tab="users" class="adm-tab rounded-md px-3 py-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">Users <span class="ml-1 text-zinc-500"><?= (int)$userCount ?></span></button>
          <button type="button" data-adm-tab="activity" class="adm-tab rounded-md px-3 py-1.5 text-xs font-medium text-zinc-400 hover:text-zinc-200 transition">Activity</button>
        </div>
        <div class="flex items-center gap-2">
          <button type="button" data-menu-btn="cols" class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-1.5 text-sm text-zinc-200 hover:bg-white/[0.07] transition">
            <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
            </svg>
            Customize Columns
            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
          </button>
          <a href="<?= $base ?>/cars/create" class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-1.5 text-sm text-zinc-200 hover:bg-white/[0.07] transition">
            <svg class="w-4 h-4 text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New listing
          </a>
        </div>
      </div>

      <!-- Panel: Listings -->
      <div data-adm-panel="listings">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[760px]">
            <thead>
              <tr class="border-b border-white/[0.06]">
                <th class="pl-4 pr-1 py-3 w-8"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select all"></th>
                <?= $th('listing', 'Listing') ?>
                <?= $th('type', 'Type') ?>
                <?= $th('status', 'Status') ?>
                <?= $th('bids', 'Bids', true) ?>
                <?= $th('bid', 'Current bid', true) ?>
                <?= $th('seller', 'Seller') ?>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($recentListings)): ?>
                <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">No listings yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($recentListings as $c): ?>
                <tr class="border-b border-white/[0.05] hover:bg-white/[0.03] transition">
                  <td class="pl-4 pr-1 py-3"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select row"></td>
                  <td <?= $td('listing') ?>>
                    <a href="<?= $base ?>/cars/view/<?= (int)$c['id'] ?>" class="group inline-flex items-center gap-1 font-medium text-zinc-100 hover:text-white">
                      <?= e($c['title']) ?>
                      <svg class="w-3.5 h-3.5 text-zinc-600 group-hover:text-zinc-300 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <div class="text-xs text-zinc-500"><?= e($c['make']) ?> <?= e($c['model']) ?> &middot; <?= e($c['year']) ?></div>
                  </td>
                  <td <?= $td('type') ?>><span class="inline-flex rounded-md border border-white/15 px-2 py-0.5 text-xs text-zinc-300 whitespace-nowrap"><?= e($c['body_type'] ?: 'Other') ?></span></td>
                  <td <?= $td('status') ?>><?= $statusPill($c['status']) ?></td>
                  <td <?= $td('bids', 'text-right tabular-nums text-zinc-300') ?>><?= (int)$c['bid_count'] ?></td>
                  <td <?= $td('bid', 'text-right tabular-nums font-medium text-zinc-100') ?>><?= $c['current_bid'] !== null ? $money($c['current_bid']) : '<span class="text-zinc-600">—</span>' ?></td>
                  <td <?= $td('seller', 'text-zinc-400') ?>><?= e($c['seller_name']) ?></td>
                  <td class="pr-3 py-3 text-right">
                    <div class="relative inline-block">
                      <?= $kebab ?>
                      <template data-menu-src>
                        <a href="<?= $base ?>/cars/view/<?= (int)$c['id'] ?>" <?= $menuItem ?>>View listing</a>
                        <?php if ($c['status'] === 'pending'): ?>
                          <a href="<?= $base ?>/admin/review/<?= (int)$c['id'] ?>" <?= $menuItem ?>>Review &amp; approve</a>
                        <?php endif; ?>
                      </template>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php $pagerFooter(count($recentListings), true, $base . '/cars', 'View all listings'); ?>
      </div>

      <!-- Panel: Pending -->
      <div data-adm-panel="pending" class="hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[720px]">
            <thead>
              <tr class="border-b border-white/[0.06]">
                <th class="pl-4 pr-1 py-3 w-8"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select all"></th>
                <?= $th('listing', 'Listing') ?>
                <?= $th('type', 'Type') ?>
                <?= $th('status', 'Status') ?>
                <?= $th('price', 'Start price', true) ?>
                <?= $th('seller', 'Seller') ?>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($pending)): ?>
                <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-zinc-500">No listings awaiting approval.</td></tr>
              <?php endif; ?>
              <?php foreach ($pending as $c): ?>
                <tr class="border-b border-white/[0.05] hover:bg-white/[0.03] transition">
                  <td class="pl-4 pr-1 py-3"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select row"></td>
                  <td <?= $td('listing') ?>>
                    <a href="<?= $base ?>/admin/review/<?= (int)$c['id'] ?>" class="group inline-flex items-center gap-1 font-medium text-zinc-100 hover:text-white">
                      <?= e($c['title']) ?>
                      <svg class="w-3.5 h-3.5 text-zinc-600 group-hover:text-zinc-300 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                    <div class="text-xs text-zinc-500">Submitted <?= e(date('M j, Y', strtotime($c['created_at']))) ?></div>
                  </td>
                  <td <?= $td('type') ?>><span class="inline-flex rounded-md border border-white/15 px-2 py-0.5 text-xs text-zinc-300 whitespace-nowrap"><?= e($c['body_type'] ?? 'Other') ?></span></td>
                  <td <?= $td('status') ?>><?= $statusPill('pending') ?></td>
                  <td <?= $td('price', 'text-right tabular-nums font-medium text-zinc-100') ?>><?= $money($c['starting_price']) ?></td>
                  <td <?= $td('seller', 'text-zinc-400') ?>><?= e($c['seller_name']) ?></td>
                  <td class="pr-3 py-3 text-right">
                    <div class="relative inline-block">
                      <?= $kebab ?>
                      <template data-menu-src>
                        <a href="<?= $base ?>/admin/review/<?= (int)$c['id'] ?>" <?= $menuItem ?>>Review &amp; approve</a>
                        <a href="<?= $base ?>/cars/view/<?= (int)$c['id'] ?>" <?= $menuItem ?>>View listing</a>
                      </template>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php $pagerFooter(count($pending)); ?>
      </div>

      <!-- Panel: Users -->
      <div data-adm-panel="users" class="hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[720px]">
            <thead>
              <tr class="border-b border-white/[0.06]">
                <th class="pl-4 pr-1 py-3 w-8"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select all"></th>
                <?= $th('user', 'User') ?>
                <?= $th('role', 'Role') ?>
                <?= $th('status', 'Status') ?>
                <?= $th('listings', 'Listings', true) ?>
                <?= $th('bids', 'Bids', true) ?>
                <?= $th('joined', 'Joined') ?>
                <th class="w-10"></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($users)): ?>
                <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-zinc-500">No users found.</td></tr>
              <?php endif; ?>
              <?php foreach ($users as $u): ?>
                <tr class="border-b border-white/[0.05] hover:bg-white/[0.03] transition">
                  <td class="pl-4 pr-1 py-3"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select row"></td>
                  <td <?= $td('user') ?>>
                    <div class="font-medium text-zinc-100"><?= e($u['name']) ?></div>
                    <div class="text-xs text-zinc-500"><?= e($u['email']) ?></div>
                  </td>
                  <td <?= $td('role') ?>><span class="inline-flex rounded-md border border-white/15 px-2 py-0.5 text-xs text-zinc-300 capitalize"><?= e($u['role']) ?></span></td>
                  <td <?= $td('status') ?>>
                    <?php if ($u['is_active']): ?>
                      <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/25 bg-emerald-400/10 px-2.5 py-1 text-xs font-medium text-emerald-300">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Active
                      </span>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-2.5 py-1 text-xs font-medium text-zinc-400">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>Disabled
                      </span>
                    <?php endif; ?>
                  </td>
                  <td <?= $td('listings', 'text-right tabular-nums text-zinc-300') ?>><?= (int)$u['listings'] ?></td>
                  <td <?= $td('bids', 'text-right tabular-nums text-zinc-300') ?>><?= (int)$u['bids'] ?></td>
                  <td <?= $td('joined', 'text-zinc-400 whitespace-nowrap') ?>><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                  <td class="pr-3 py-3 text-right">
                    <div class="relative inline-block">
                      <?= $kebab ?>
                      <template data-menu-src>
                        <a href="<?= $base ?>/admin/users" <?= $menuItem ?>>Manage in user list</a>
                      </template>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php $pagerFooter(count($users), true, $base . '/admin/users', 'Manage all users'); ?>
      </div>

      <!-- Panel: Activity -->
      <div data-adm-panel="activity" class="hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-[640px]">
            <thead>
              <tr class="border-b border-white/[0.06]">
                <th class="pl-4 pr-1 py-3 w-8"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select all"></th>
                <?= $th('event', 'Event') ?>
                <?= $th('user', 'User') ?>
                <?= $th('ip', 'IP address') ?>
                <?= $th('time', 'Time') ?>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($logs)): ?>
                <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-zinc-500">No activity recorded yet.</td></tr>
              <?php endif; ?>
              <?php foreach ($logs as $log): ?>
                <tr class="border-b border-white/[0.05] hover:bg-white/[0.03] transition">
                  <td class="pl-4 pr-1 py-3"><input type="checkbox" class="h-4 w-4 rounded border-white/20 bg-white/5 accent-zinc-300 align-middle" aria-label="Select row"></td>
                  <td <?= $td('event') ?>><span class="font-medium text-zinc-100"><?= e($log['action']) ?></span></td>
                  <td <?= $td('user', 'text-zinc-400') ?>><?= e($log['user_name'] ?? 'System') ?></td>
                  <td <?= $td('ip', 'text-zinc-500 font-mono text-xs') ?>><?= e($log['ip_address'] ?? '') ?></td>
                  <td <?= $td('time', 'text-zinc-400 whitespace-nowrap') ?>><?= e(date('M j, g:i a', strtotime($log['created_at']))) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php $pagerFooter(count($logs), true, $base . '/admin/logs', 'View full audit log'); ?>
      </div>
    </section>

  </div>
</div>

<script nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
(function () {
  var root = document.getElementById('adminDash');
  if (!root) return;

  /* ================= Chart ================= */
  var SERIES = <?= json_encode($chart ?? [], JSON_UNESCAPED_SLASHES) ?>;
  var chartWrap = document.getElementById('admChart');
  var daysLabel = document.getElementById('admChartDays');
  var rangeBtns = root.querySelectorAll('[data-range]');
  var rangeDays = 90;
  var hoverState = null;

  function fmtDate(d) {
    var dt = new Date(d + 'T00:00:00');
    return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
  }

  function smooth(pts) {
    if (pts.length < 2) return 'M' + pts[0][0] + ',' + pts[0][1];
    var d = 'M' + pts[0][0].toFixed(1) + ',' + pts[0][1].toFixed(1);
    for (var i = 0; i < pts.length - 1; i++) {
      var p0 = pts[Math.max(0, i - 1)], p1 = pts[i], p2 = pts[i + 1], p3 = pts[Math.min(pts.length - 1, i + 2)];
      d += 'C' + (p1[0] + (p2[0] - p0[0]) / 6).toFixed(1) + ',' + (p1[1] + (p2[1] - p0[1]) / 6).toFixed(1)
         + ' ' + (p2[0] - (p3[0] - p1[0]) / 6).toFixed(1) + ',' + (p2[1] - (p3[1] - p1[1]) / 6).toFixed(1)
         + ' ' + p2[0].toFixed(1) + ',' + p2[1].toFixed(1);
    }
    return d;
  }

  function drawChart() {
    if (!chartWrap) return;
    var W = chartWrap.clientWidth, H = chartWrap.clientHeight;
    if (!W || !H || !SERIES.length) {
      chartWrap.innerHTML = '<div class="h-full flex items-center justify-center text-sm text-zinc-600">No activity data yet.</div>';
      return;
    }
    var data = SERIES.slice(-rangeDays);
    var pad = { t: 14, r: 14, b: 26, l: 14 };
    var iw = W - pad.l - pad.r, ih = H - pad.t - pad.b;
    var max = 1;
    data.forEach(function (d) { max = Math.max(max, d.bids, d.listings); });
    max = Math.ceil(max * 1.15);
    var x = function (i) { return pad.l + (data.length < 2 ? iw / 2 : i * (iw / (data.length - 1))); };
    var y = function (v) { return pad.t + ih - (v / max) * ih; };
    var base = pad.t + ih;

    var svg = '<svg width="' + W + '" height="' + H + '" class="block">'
      + '<defs>'
      + '<linearGradient id="gA" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="rgba(255,255,255,0.28)"/><stop offset="1" stop-color="rgba(255,255,255,0)"/></linearGradient>'
      + '<linearGradient id="gB" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="rgba(161,161,170,0.16)"/><stop offset="1" stop-color="rgba(161,161,170,0)"/></linearGradient>'
      + '</defs>';

    // horizontal grid
    for (var g = 1; g <= 4; g++) {
      var gy = pad.t + (ih * g / 4);
      svg += '<line x1="' + pad.l + '" x2="' + (W - pad.r) + '" y1="' + gy + '" y2="' + gy + '" stroke="rgba(255,255,255,0.05)"/>';
    }

    function area(key) {
      var pts = data.map(function (d, i) { return [x(i), y(d[key])]; });
      return smooth(pts) + 'L' + pts[pts.length - 1][0].toFixed(1) + ',' + base + 'L' + pts[0][0].toFixed(1) + ',' + base + 'Z';
    }
    function line(key) {
      return smooth(data.map(function (d, i) { return [x(i), y(d[key])]; }));
    }

    svg += '<path d="' + area('listings') + '" fill="url(#gB)"/>'
         + '<path d="' + line('listings') + '" fill="none" stroke="rgba(161,161,170,0.7)" stroke-width="1.5"/>'
         + '<path d="' + area('bids') + '" fill="url(#gA)"/>'
         + '<path d="' + line('bids') + '" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="1.8"/>';

    // x tick labels (~6)
    var ticks = Math.min(6, data.length);
    for (var t = 0; t < ticks; t++) {
      var i = Math.round(t * (data.length - 1) / Math.max(1, ticks - 1));
      svg += '<text x="' + x(i) + '" y="' + (H - 6) + '" text-anchor="middle" font-size="10" fill="#71717a">' + fmtDate(data[i].d) + '</text>';
    }

    // hover layer
    svg += '<g id="admHover" style="display:none">'
         + '<line id="admHoverLine" y1="' + pad.t + '" y2="' + base + '" stroke="rgba(255,255,255,0.15)"/>'
         + '<circle id="admDotA" r="3.5" fill="#fff"/>'
         + '<circle id="admDotB" r="3.5" fill="#a1a1aa"/>'
         + '</g></svg>';

    chartWrap.innerHTML = svg;

    var svgEl = chartWrap.querySelector('svg');
    var hoverG = chartWrap.querySelector('#admHover');
    var hLine = chartWrap.querySelector('#admHoverLine');
    var dotA = chartWrap.querySelector('#admDotA');
    var dotB = chartWrap.querySelector('#admDotB');

    if (!hoverState) {
      hoverState = document.createElement('div');
      hoverState.className = 'pointer-events-none absolute z-10 hidden rounded-lg border border-white/10 bg-[#1c1c22] px-3 py-2 text-xs shadow-xl shadow-black/50';
      chartWrap.appendChild(hoverState);
    }

    svgEl.addEventListener('mousemove', function (e) {
      var rect = svgEl.getBoundingClientRect();
      var mx = e.clientX - rect.left;
      var i = Math.max(0, Math.min(data.length - 1, Math.round((mx - pad.l) / (iw / (data.length - 1)))));
      var d = data[i];
      hoverG.style.display = '';
      hLine.setAttribute('x1', x(i)); hLine.setAttribute('x2', x(i));
      dotA.setAttribute('cx', x(i)); dotA.setAttribute('cy', y(d.bids));
      dotB.setAttribute('cx', x(i)); dotB.setAttribute('cy', y(d.listings));
      hoverState.innerHTML = '<div class="font-medium text-zinc-200 mb-1">' + fmtDate(d.d) + '</div>'
        + '<div class="flex items-center gap-2 text-zinc-400"><span class="w-2 h-2 rounded-full bg-white/80"></span>' + d.bids + ' bid' + (d.bids === 1 ? '' : 's') + '</div>'
        + '<div class="flex items-center gap-2 text-zinc-400 mt-0.5"><span class="w-2 h-2 rounded-full bg-zinc-500"></span>' + d.listings + ' listing' + (d.listings === 1 ? '' : 's') + '</div>';
      hoverState.classList.remove('hidden');
      var tx = Math.min(Math.max(x(i) - hoverState.offsetWidth / 2, 4), rect.width - hoverState.offsetWidth - 4);
      hoverState.style.left = tx + 'px';
      hoverState.style.top = Math.max(0, y(Math.max(d.bids, d.listings)) - hoverState.offsetHeight - 14) + 'px';
    });
    svgEl.addEventListener('mouseleave', function () {
      hoverG.style.display = 'none';
      hoverState.classList.add('hidden');
    });
  }

  rangeBtns.forEach(function (b) {
    b.addEventListener('click', function () {
      rangeDays = parseInt(b.getAttribute('data-range'), 10);
      if (daysLabel) daysLabel.textContent = rangeDays;
      rangeBtns.forEach(function (o) {
        var on = o === b;
        o.classList.toggle('bg-white/10', on);
        o.classList.toggle('text-white', on);
        o.classList.toggle('text-zinc-400', !on);
      });
      drawChart();
    });
  });
  new ResizeObserver(drawChart).observe(chartWrap);
  drawChart();

  /* ================= Tabs ================= */
  var tabBtns = root.querySelectorAll('[data-adm-tab]');
  var panels = root.querySelectorAll('[data-adm-panel]');
  function setTab(name) {
    panels.forEach(function (p) { p.classList.toggle('hidden', p.getAttribute('data-adm-panel') !== name); });
    tabBtns.forEach(function (b) {
      var on = b.getAttribute('data-adm-tab') === name;
      b.classList.toggle('bg-white/10', on);
      b.classList.toggle('text-white', on);
      b.classList.toggle('text-zinc-400', !on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
  }
  tabBtns.forEach(function (b) {
    b.addEventListener('click', function () { setTab(b.getAttribute('data-adm-tab')); });
  });

  function activePanel() {
    for (var i = 0; i < panels.length; i++) {
      if (!panels[i].classList.contains('hidden')) return panels[i];
    }
    return panels[0];
  }

  /* ================= Pagination + row selection ================= */
  panels.forEach(function (panel) {
    var table = panel.querySelector('table');
    if (!table || !table.tBodies.length) return;
    var rows = Array.prototype.slice.call(table.tBodies[0].rows);
    var sizeSel = panel.querySelector('[data-page-size]');
    var pageLbl = panel.querySelector('[data-page-label]');
    var selLbl = panel.querySelector('[data-sel-label]');
    var navBtns = panel.querySelectorAll('[data-nav]');
    var checkAll = table.querySelector('thead input[type=checkbox]');
    var page = 0;

    function updateSel() {
      var n = 0;
      rows.forEach(function (r) {
        var c = r.querySelector('input[type=checkbox]');
        if (c && c.checked) n++;
      });
      if (selLbl) selLbl.textContent = n + ' of ' + rows.length + ' row(s) selected.';
      if (checkAll) {
        checkAll.checked = rows.length > 0 && n === rows.length;
        checkAll.indeterminate = n > 0 && n < rows.length;
      }
    }

    function layout() {
      var per = parseInt(sizeSel ? sizeSel.value : '10', 10) || 10;
      var pages = Math.max(1, Math.ceil(rows.length / per));
      if (page >= pages) page = pages - 1;
      rows.forEach(function (r, i) {
        r.classList.toggle('hidden', !(i >= page * per && i < (page + 1) * per));
      });
      if (pageLbl) pageLbl.textContent = 'Page ' + (page + 1) + ' of ' + pages;
      navBtns.forEach(function (b) {
        var n = b.getAttribute('data-nav');
        b.disabled = (n === 'first' || n === 'prev') ? page === 0 : page >= pages - 1;
      });
      updateSel();
    }

    if (checkAll) checkAll.addEventListener('change', function () {
      rows.forEach(function (r) {
        var c = r.querySelector('input[type=checkbox]');
        if (c) c.checked = checkAll.checked;
      });
      updateSel();
    });
    rows.forEach(function (r) {
      var c = r.querySelector('input[type=checkbox]');
      if (c) c.addEventListener('change', updateSel);
    });
    if (sizeSel) sizeSel.addEventListener('change', function () { page = 0; layout(); });
    navBtns.forEach(function (b) {
      b.addEventListener('click', function () {
        var per = parseInt(sizeSel ? sizeSel.value : '10', 10) || 10;
        var pages = Math.max(1, Math.ceil(rows.length / per));
        var n = b.getAttribute('data-nav');
        page = n === 'first' ? 0 : n === 'prev' ? page - 1 : n === 'next' ? page + 1 : pages - 1;
        page = Math.max(0, Math.min(pages - 1, page));
        layout();
      });
    });
    layout();
  });

  /* ================= Floating menu (kebab + customize columns) ================= */
  var floatMenu = document.createElement('div');
  floatMenu.setAttribute('data-fmenu', '');
  floatMenu.className = 'fixed z-50 hidden w-52 rounded-xl border border-white/10 bg-[#1c1c22] p-1 shadow-2xl shadow-black/60';
  document.body.appendChild(floatMenu);

  function closeMenu() { floatMenu.classList.add('hidden'); }

  function openMenu(trigger, html) {
    floatMenu.innerHTML = html;
    floatMenu.classList.remove('hidden');
    var r = trigger.getBoundingClientRect();
    var w = floatMenu.offsetWidth, h = floatMenu.offsetHeight;
    floatMenu.style.left = Math.max(8, Math.min(r.right - w, window.innerWidth - w - 8)) + 'px';
    floatMenu.style.top = (r.bottom + h + 8 > window.innerHeight ? r.top - h - 6 : r.bottom + 6) + 'px';
  }

  function buildColsMenu() {
    var panel = activePanel();
    if (!panel) return '';
    var html = '<div class="px-2.5 py-1.5 text-xs font-medium text-zinc-500">Toggle columns</div>';
    panel.querySelectorAll('th[data-col]').forEach(function (th, idx) {
      var on = !th.classList.contains('hidden');
      html += '<button type="button" data-col-toggle="' + th.getAttribute('data-col') + '" class="flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">'
        + '<span class="w-4 h-4 flex items-center justify-center text-emerald-400">'
        + (on ? '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : '')
        + '</span><span>' + (th.getAttribute('data-label') || th.textContent.trim()) + '</span></button>';
    });
    return html;
  }

  document.addEventListener('click', function (e) {
    // Column toggle inside the floating menu
    var colBtn = e.target.closest('[data-col-toggle]');
    if (colBtn) {
      var panel = activePanel();
      if (panel) {
        var key = colBtn.getAttribute('data-col-toggle');
        var hide = !panel.querySelector('th[data-col="' + key + '"]').classList.contains('hidden');
        panel.querySelectorAll('[data-col="' + key + '"]').forEach(function (el) {
          el.classList.toggle('hidden', hide);
        });
      }
      floatMenu.innerHTML = buildColsMenu();
      e.stopPropagation();
      return;
    }

    var trigger = e.target.closest('[data-menu-btn]');
    if (trigger) {
      e.stopPropagation();
      var wasOpen = !floatMenu.classList.contains('hidden') && floatMenu._for === trigger;
      if (wasOpen) { closeMenu(); return; }
      if (trigger.getAttribute('data-menu-btn') === 'cols') {
        openMenu(trigger, buildColsMenu());
      } else {
        var tpl = trigger.parentElement.querySelector('template[data-menu-src]');
        if (tpl) openMenu(trigger, tpl.innerHTML);
      }
      floatMenu._for = trigger;
      return;
    }
    if (!e.target.closest('[data-fmenu]')) closeMenu();
    var link = e.target.closest('[data-fmenu] a');
    if (link) closeMenu();
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeMenu(); });
  window.addEventListener('scroll', closeMenu, { passive: true, capture: true });
})();
</script>
