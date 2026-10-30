<?php
/** Featured cars page — dark, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$count = count($cars);
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white flex items-center gap-2.5">
          Featured Cars
          <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.51.44c.5.04.7.66.31.99l-4.2 3.6a.56.56 0 00-.18.55l1.29 5.39a.56.56 0 01-.84.61l-4.72-2.88a.56.56 0 00-.58 0l-4.73 2.88a.56.56 0 01-.84-.6l1.29-5.4a.56.56 0 00-.18-.55l-4.2-3.6a.56.56 0 01.32-.98l5.5-.44a.56.56 0 00.48-.35l2.13-5.12z"/></svg>
        </h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= $count ?> hand-picked listing<?= $count===1?'':'s' ?> &middot; latest active auctions</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">Live bidding</a>
    </div>

    <?php if (empty($cars)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center text-amber-400">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No featured cars yet</h3>
        <p class="mt-1 text-sm text-zinc-500">Check back soon for new listings!</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($cars as $i => $car): ?>
          <?php $stagger = 'stagger-' . (min($i, 7) + 1); $cardDark = true; include __DIR__ . '/../partials/car_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
