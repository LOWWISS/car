<?php /** Notifications list. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="flex items-center justify-end mb-6 animate-fade-in">
    <?php if (!empty($items)): ?>
      <button type="button" id="markAllRead" class="btn-outline text-sm" data-csrf="<?= e(Csrf::token()) ?>">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Mark all read
      </button>
    <?php endif; ?>
  </div>
  <?php if (empty($items)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
        </div>
        <h3 class="empty-state-title">No notifications</h3>
        <p class="empty-state-text">You're all caught up!</p>
      </div>
    </div>
  <?php else: ?>
    <ul class="space-y-2">
      <?php foreach ($items as $i => $n): ?>
        <li class="card p-4 flex items-start gap-3 animate-fade-in-up <?= $n['is_read']?'opacity-60':'' ?> stagger-<?= min($i, 7) + 1 ?>">
          <span class="mt-1 w-2.5 h-2.5 rounded-full shrink-0 <?= $n['is_read']?'bg-slate-300':'bg-auction animate-pulse-glow' ?>"></span>
          <div class="flex-1 min-w-0">
            <p class="text-sm text-slate-800"><?= e($n['message']) ?></p>
            <p class="text-xs text-slate-400 mt-1"><?= e($n['created_at']) ?></p>
          </div>
          <?php if (!empty($n['link'])): ?>
            <a href="<?= $base ?><?= e($n['link']) ?>" class="text-brand text-sm hover:underline shrink-0">View</a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>
