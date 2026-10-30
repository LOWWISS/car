<?php
/** Live bidding page — dark, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$liveCount = count($cars);
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white flex items-center gap-2.5">
          Live Bidding
          <?php if ($liveCount > 0): ?>
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-auction opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-auction"></span>
            </span>
          <?php endif; ?>
        </h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= $liveCount ?> live auction<?= $liveCount===1?'':'s' ?> &middot; ending soonest first</p>
      </div>
      <a href="<?= $base ?>/cars" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">Browse all cars</a>
    </div>

    <?php if (empty($cars)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No active auctions right now</h3>
        <p class="mt-1 text-sm text-zinc-500">Check back later or browse all cars.</p>
        <a href="<?= $base ?>/cars" class="inline-block mt-4 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse all cars</a>
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
