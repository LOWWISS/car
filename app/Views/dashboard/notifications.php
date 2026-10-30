<?php
/** Notifications inbox — dark, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL','/car'),'/');

$unreadCount = 0;
foreach ($items as $n) { if (empty($n['is_read'])) $unreadCount++; }

// Icon per notification type
$typeIcon = function (string $type): array {
    switch ($type) {
        case 'bid':
            return ['M13 10V3L4 14h7v7l9-11h-7z', 'bg-amber-400/10 text-amber-300 border-amber-400/20'];
        case 'listing':
            return ['M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2', 'bg-blue-400/10 text-blue-300 border-blue-400/20'];
        case 'transaction':
        case 'payment':
            return ['M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20'];
        case 'won':
        case 'success':
            return ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg-emerald-400/10 text-emerald-300 border-emerald-400/20'];
        default:
            return ['M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1', 'bg-white/5 text-zinc-400 border-white/10'];
    }
};

// Human relative time
$timeAgo = function (string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($ts));
};
?>

<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
      <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold text-white">Notifications</h1>
        <?php if ($unreadCount > 0): ?>
          <span id="notifUnreadPill" class="inline-flex items-center rounded-md bg-brand/25 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-300"><?= $unreadCount ?> unread</span>
        <?php endif; ?>
      </div>
      <?php if ($unreadCount > 0): ?>
        <button type="button" id="markAllRead" data-csrf="<?= e(Csrf::token()) ?>"
                class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-1.5 text-sm text-zinc-200 hover:bg-white/[0.07] transition">
          <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Mark all read
        </button>
      <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
      <!-- Empty state -->
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No notifications</h3>
        <p class="mt-1 text-sm text-zinc-500">You're all caught up. Bids, listings, and payment updates will show here.</p>
      </div>
    <?php else: ?>

      <!-- Inbox list -->
      <ul class="rounded-xl border border-white/[0.07] divide-y divide-white/[0.05] overflow-hidden">
        <?php foreach ($items as $i => $n): ?>
          <?php $unread = empty($n['is_read']); [$icon, $chip] = $typeIcon($n['type'] ?? 'info'); ?>
          <li class="notif-row flex items-start gap-3.5 px-4 py-4 transition <?= $unread ? 'bg-white/[0.03]' : 'opacity-60' ?> animate-fade-in-up stagger-<?= min($i, 7) + 1 ?>">
            <!-- Type icon -->
            <span class="mt-0.5 w-9 h-9 shrink-0 rounded-lg border flex items-center justify-center <?= $chip ?>">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $icon ?>"/>
              </svg>
            </span>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm leading-snug <?= $unread ? 'text-zinc-100 font-medium' : 'text-zinc-400' ?>"><?= e($n['message']) ?></p>
              <p class="text-xs text-zinc-500 mt-1.5 flex items-center gap-1.5">
                <?= e($timeAgo($n['created_at'])) ?>
                <span class="text-zinc-700">&middot;</span>
                <span class="capitalize"><?= e($n['type'] ?? 'info') ?></span>
              </p>
            </div>

            <!-- Right: unread dot + actions -->
            <div class="flex items-center gap-3 shrink-0 self-center">
              <?php if ($unread): ?>
                <span class="notif-dot w-2 h-2 rounded-full bg-auction shrink-0" title="Unread"></span>
                <button type="button" class="notif-view inline-flex items-center gap-1 text-xs font-medium text-zinc-400 hover:text-white transition"
                        data-id="<?= (int)$n['id'] ?>" data-csrf="<?= e(Csrf::token()) ?>">
                  Mark read
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </button>
              <?php endif; ?>
              <?php
                $href  = trim((string)($n['link'] ?? ''));
                // Generic fallbacks mean the target no longer exists — alert instead of navigating.
                $dead  = $href === '' || in_array($href, ['/admin', '/dashboard'], true);
              ?>
              <?php if (!$dead): ?>
                <a href="<?= $base ?><?= e($href) ?>" class="notif-goto group inline-flex items-center gap-1 text-xs font-medium text-zinc-400 hover:text-white transition"
                   data-id="<?= (int)$n['id'] ?>" data-csrf="<?= e(Csrf::token()) ?>" <?= $unread ? 'data-unread="1"' : '' ?>>
                  View
                  <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
              <?php else: ?>
                <button type="button" class="notif-dead inline-flex items-center gap-1 text-xs font-medium text-zinc-400 hover:text-white transition">
                  View
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>

  </div>
</div>
