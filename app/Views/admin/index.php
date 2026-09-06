<?php /** Admin dashboard. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); $a = $analytics; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Analytics -->
  <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <div class="stat-card animate-fade-in-up stagger-1">
      <div class="stat-icon bg-blue-100 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Listings</div><div class="text-2xl font-bold mt-1"><?= (int)$a['total_cars'] ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-2">
      <div class="stat-icon bg-indigo-100 text-indigo-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Users</div><div class="text-2xl font-bold mt-1"><?= (int)$a['total_users'] ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-3">
      <div class="stat-icon bg-purple-100 text-purple-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Total bids</div><div class="text-2xl font-bold mt-1"><?= (int)$a['total_bids'] ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-4">
      <div class="stat-icon bg-green-100 text-green-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Active auctions</div><div class="text-2xl font-bold text-brand mt-1"><?= (int)$a['active_auctions'] ?></div>
    </div>
    <div class="stat-card animate-fade-in-up stagger-5">
      <div class="stat-icon bg-emerald-100 text-emerald-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg></div>
      <div class="text-xs uppercase text-slate-400">Gross GMV</div><div class="text-2xl font-bold text-green-600 mt-1">&#8369;<?= number_format((float)$a['gmv'], 0) ?></div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Pending approvals -->
    <section class="card p-5 animate-fade-in-up stagger-2">
      <h2 class="font-bold text-lg mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Pending Approvals
      </h2>
      <?php if (empty($pending)): ?>
        <div class="empty-state">
          <div class="empty-state-icon"><svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
          <p class="empty-state-text">No listings awaiting approval.</p>
        </div>
      <?php else: ?>
        <ul class="space-y-3">
          <?php foreach ($pending as $c): ?>
            <li class="flex items-center justify-between border-b border-slate-100 pb-3">
              <div>
                <a href="<?= $base ?>/cars/view/<?= (int)$c['id'] ?>" class="font-medium text-slate-800 hover:text-brand"><?= e($c['title']) ?></a>
                <p class="text-xs text-slate-500">by <?= e($c['seller_name']) ?> &middot; &#8369;<?= number_format((float)$c['starting_price'], 0) ?></p>
              </div>
              <div class="flex gap-2">
                <a href="<?= $base ?>/admin/review/<?= (int)$c['id'] ?>" class="btn-primary text-xs inline-flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  Review
                </a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <!-- Recent audit logs -->
    <section class="card p-5 animate-fade-in-up stagger-3">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-bold text-lg flex items-center gap-2">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Recent Activity
        </h2>
        <a href="<?= $base ?>/admin/logs" class="text-sm text-brand hover:underline">View all</a>
      </div>
      <ul class="space-y-2 text-sm max-h-80 overflow-y-auto">
        <?php foreach ($logs as $log): ?>
          <li class="border-b border-slate-100 pb-2 flex items-start gap-2">
            <span class="w-2 h-2 rounded-full bg-brand mt-1.5 shrink-0"></span>
            <div class="flex-1 min-w-0">
              <span class="font-medium"><?= e($log['action']) ?></span>
              <span class="text-slate-400 text-xs block"><?= e($log['created_at']) ?><?php if (!empty($log['user_name'])): ?> — <?= e($log['user_name']) ?><?php endif; ?></span>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>

</div>
