<?php
/** User dashboard (role-aware). */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$s = $stats;
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Stat widgets -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <?php if ($role === 'seller' || $role === 'admin'): ?>
      <div class="stat-card animate-fade-in-up stagger-1">
        <div class="stat-icon bg-blue-100 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z"/></svg></div>
        <div class="text-xs uppercase text-slate-400">Active listings</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?= (int)($s['active_listings'] ?? 0) ?></div>
      </div>
      <div class="stat-card animate-fade-in-up stagger-2">
        <div class="stat-icon bg-amber-100 text-amber-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div class="text-xs uppercase text-slate-400">Pending approval</div>
        <div class="text-2xl font-bold text-amber-600 mt-1"><?= (int)($s['pending_listings'] ?? 0) ?></div>
      </div>
      <div class="stat-card animate-fade-in-up stagger-3">
        <div class="stat-icon bg-green-100 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
        <div class="text-xs uppercase text-slate-400">Sold</div>
        <div class="text-2xl font-bold text-slate-900 mt-1"><?= (int)($s['sold'] ?? 0) ?></div>
      </div>
      <div class="stat-card animate-fade-in-up stagger-4">
        <div class="stat-icon bg-emerald-100 text-emerald-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg></div>
        <div class="text-xs uppercase text-slate-400">Revenue</div>
        <div class="text-2xl font-bold text-green-600 mt-1">&#8369;<?= number_format((float)($s['revenue'] ?? 0), 0) ?></div>
      </div>
    <?php endif; ?>
    <div class="stat-card animate-fade-in-up stagger-5">
      <div class="stat-icon bg-brand-light text-brand"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Active bids</div>
      <div class="text-2xl font-bold text-brand mt-1"><?= (int)($s['active_bids'] ?? 0) ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-6">
      <div class="stat-icon bg-red-100 text-auction-dark"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Won auctions</div>
      <div class="text-2xl font-bold text-auction-dark mt-1"><?= (int)($s['won_auctions'] ?? 0) ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-7">
      <div class="stat-icon bg-yellow-100 text-yellow-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Watchlist</div>
      <div class="text-2xl font-bold text-slate-900 mt-1"><?= (int)($s['watchlist'] ?? 0) ?></div>
    </div>
    <div class="stat-card flex items-center justify-center animate-fade-in-up stagger-8">
      <?php if ($role === 'seller' || $role === 'admin'): ?>
        <a href="<?= $base ?>/cars/create" class="btn-primary text-sm w-full text-center">+ List a car</a>
      <?php else: ?>
        <a href="<?= $base ?>/cars" class="btn-primary text-sm w-full text-center">Bid a car</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- My listings -->
  <?php if ($role === 'seller' || $role === 'admin'): ?>
    <section class="mb-8 animate-fade-in-up">
      <div class="section-header">
        <h2>My Listings</h2>
        <a href="<?= $base ?>/cars/create" class="text-sm text-brand hover:underline">+ New listing</a>
      </div>
      <?php if (empty($listings)): ?>
        <div class="card">
          <div class="empty-state">
            <div class="empty-state-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z"/></svg></div>
            <h3 class="empty-state-title">No listings yet</h3>
            <p class="empty-state-text">Start selling by creating your first listing.</p>
            <a href="<?= $base ?>/cars/create" class="btn-primary">Create listing</a>
          </div>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <?php foreach ($listings as $i => $car): ?>
            <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <!-- Latest Won Car + Spending Chart (side by side) -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Latest Won Car (2 cols) -->
    <section class="lg:col-span-2 animate-fade-in-up">
      <div class="section-header">
        <h2>Recently Won</h2>
        <?php if ($wonCount > 0): ?>
          <a href="<?= $base ?>/dashboard/won-auctions" class="text-sm text-brand hover:underline">View all (<?= (int)$wonCount ?>)</a>
        <?php endif; ?>
      </div>
      <?php if (!$latestWon): ?>
        <div class="card">
          <div class="empty-state">
            <div class="empty-state-icon"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div>
            <h3 class="empty-state-title">No won auctions yet</h3>
            <p class="empty-state-text">Keep bidding to win your first auction!</p>
            <a href="<?= $base ?>/cars" class="btn-primary">Browse auctions</a>
          </div>
        </div>
      <?php else:
        $car = $latestWon;
        $img = $car['primary_image'] ?? null;
        $imgSrc = $img ? $base . '/' . $img : $base . '/assets/images/placeholder.svg';
        $current = $car['bid_amount'] ?? $car['starting_price'];
        $txStatus = $car['transaction_status'] ?? null;
        $txBadge = match ($txStatus) {
            'payment_pending'    => '<span class="badge badge-warning">Payment Due</span>',
            'payment_confirmed'  => '<span class="badge badge-info">Payment Confirmed</span>',
            'ready_for_handover' => '<span class="badge badge-info">Ready for Handover</span>',
            'completed'          => '<span class="badge badge-success">Completed</span>',
            'cancelled'          => '<span class="badge badge-neutral">Cancelled</span>',
            default              => '<span class="badge badge-neutral">No transaction</span>',
        };
      ?>
        <article class="card overflow-hidden card-hover animate-fade-in-up">
          <div class="grid grid-cols-1 sm:grid-cols-2">
            <!-- Image -->
            <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="relative block bg-slate-100 overflow-hidden img-zoom">
              <img src="<?= e($imgSrc) ?>" alt="<?= e($car['title']) ?>" class="w-full h-full object-cover min-h-[200px] sm:min-h-[280px]">
              <span class="absolute top-3 right-3 bg-slate-700 text-white text-xs font-semibold px-3 py-1.5 rounded-full animate-scale-in flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                WON
              </span>
            </a>
            <!-- Details -->
            <div class="p-5 flex flex-col">
              <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>">
                <h3 class="text-lg font-bold text-slate-900 hover:text-brand transition"><?= e($car['title']) ?></h3>
              </a>
              <p class="text-xs text-slate-500 mt-1"><?= e($car['year']) ?> &middot; <?= e($car['make'] ?? '') ?> &middot; <?= e($car['model'] ?? '') ?> &middot; <?= e($car['location'] ?? '') ?></p>

              <div class="mt-4 p-3 rounded-xl bg-gradient-to-br from-red-50 to-orange-50 border border-red-100">
                <div class="text-xs uppercase tracking-wide text-slate-500">Winning bid</div>
                <div class="text-2xl font-bold text-auction-dark mt-0.5">&#8369;<?= number_format((float)$current, 2) ?></div>
              </div>

              <div class="mt-3 flex items-center justify-between">
                <span class="text-xs text-slate-400">Transaction status</span>
                <?= $txBadge ?>
              </div>

              <div class="mt-auto pt-4 flex gap-2">
                <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="btn-outline text-sm flex-1 text-center">View Listing</a>
                <?php if (!empty($car['transaction_id'])): ?>
                  <a href="<?= $base ?>/transactions/view/<?= (int)$car['transaction_id'] ?>" class="btn-primary text-sm flex-1 text-center">View Transaction</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </article>
      <?php endif; ?>
    </section>

    <!-- Enhanced Spending Chart (1 col) -->
    <section class="animate-fade-in-up stagger-2">
      <div class="section-header">
        <h2>Spending</h2>
      </div>
      <div class="card p-5">
        <?php
        $maxSpend = max(array_map(fn($m) => $m['amount'], $monthlySpend));
        $maxSpend = $maxSpend > 0 ? $maxSpend : 1;
        $totalTx = array_sum(array_map(fn($m) => $m['count'], $monthlySpend));
        $avgSpend = $totalTx > 0 ? ($totalSpent / $totalTx) : 0;
        $breakdown = $spendBreakdown ?? ['completed' => 0, 'pending' => 0, 'in_progress' => 0];
        $completedPct = $totalSpent > 0 ? round(($breakdown['completed'] / $totalSpent) * 100) : 0;
        $pendingPct = $totalSpent > 0 ? round(($breakdown['pending'] / $totalSpent) * 100) : 0;
        $inProgressPct = $totalSpent > 0 ? round(($breakdown['in_progress'] / $totalSpent) * 100) : 0;
        ?>

        <!-- Total + avg summary -->
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="text-xs uppercase tracking-wide text-slate-400">Total spent</div>
            <div class="text-3xl font-bold text-auction-dark mt-0.5">&#8369;<?= number_format((float)$totalSpent, 0) ?></div>
          </div>
          <div class="text-right">
            <div class="text-xs uppercase tracking-wide text-slate-400">Avg / win</div>
            <div class="text-lg font-bold text-slate-700 mt-0.5">&#8369;<?= number_format((float)$avgSpend, 0) ?></div>
          </div>
        </div>

        <!-- Status breakdown bar (stacked) -->
        <?php if ($totalSpent > 0): ?>
        <div class="mb-4">
          <div class="flex h-2.5 rounded-full overflow-hidden bg-slate-100">
            <?php if ($completedPct > 0): ?>
              <div class="bg-green-500 transition-all duration-700" style="width: <?= $completedPct ?>%" title="Completed: &#8369;<?= number_format($breakdown['completed'], 0) ?>"></div>
            <?php endif; ?>
            <?php if ($inProgressPct > 0): ?>
              <div class="bg-blue-500 transition-all duration-700" style="width: <?= $inProgressPct ?>%" title="In progress: &#8369;<?= number_format($breakdown['in_progress'], 0) ?>"></div>
            <?php endif; ?>
            <?php if ($pendingPct > 0): ?>
              <div class="bg-amber-400 transition-all duration-700" style="width: <?= $pendingPct ?>%" title="Payment pending: &#8369;<?= number_format($breakdown['pending'], 0) ?>"></div>
            <?php endif; ?>
          </div>
          <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-[11px]">
            <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>Completed &#8369;<?= number_format($breakdown['completed'], 0) ?></span>
            <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>In progress &#8369;<?= number_format($breakdown['in_progress'], 0) ?></span>
            <span class="flex items-center gap-1.5 text-slate-500"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>Pending &#8369;<?= number_format($breakdown['pending'], 0) ?></span>
          </div>
        </div>
        <?php endif; ?>

        <!-- Monthly bar chart -->
        <div class="pt-3 border-t border-slate-100">
          <div class="text-xs uppercase tracking-wide text-slate-400 mb-3">Last 6 months</div>
          <div class="space-y-2.5">
            <?php foreach ($monthlySpend as $m):
              $pct = $m['amount'] > 0 ? max(6, round(($m['amount'] / $maxSpend) * 100)) : 0;
              $hasSpend = $m['amount'] > 0;
              $isPeak = $hasSpend && $m['amount'] === $maxSpend;
            ?>
              <div class="group/row">
                <div class="flex items-center justify-between text-xs mb-1">
                  <span class="text-slate-500 font-medium flex items-center gap-1">
                    <?= e($m['label']) ?>
                    <?php if ($isPeak): ?>
                      <span class="text-[9px] text-auction-dark bg-red-50 px-1 rounded">PEAK</span>
                    <?php endif; ?>
                  </span>
                  <span class="<?= $hasSpend ? 'text-slate-700 font-semibold' : 'text-slate-300' ?>"><?= $hasSpend ? '&#8369;' . number_format($m['amount'], 0) : '—' ?></span>
                </div>
                <div class="h-5 bg-slate-100 rounded-md overflow-hidden relative">
                  <?php if ($hasSpend): ?>
                    <div class="h-full rounded-md transition-all duration-700 ease-out <?= $isPeak ? 'bg-gradient-to-r from-auction to-brand' : 'bg-gradient-to-r from-brand/70 to-brand' ?>"
                         style="width: <?= $pct ?>%"
                         title="<?= e($m['label']) ?>: &#8369;<?= number_format($m['amount'], 2) ?> (<?= (int)$m['count'] ?> tx)">
                    </div>
                    <?php if ($m['count'] > 0): ?>
                      <span class="absolute right-1.5 top-1/2 -translate-y-1/2 text-[9px] font-bold text-white/90"><?= (int)$m['count'] ?>tx</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Footer link -->
        <div class="mt-4 pt-3 border-t border-slate-100">
          <a href="<?= $base ?>/transactions" class="text-xs text-brand hover:underline flex items-center gap-1">
            View all transactions
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
          </a>
        </div>
      </div>
    </section>
  </div>

</div>
