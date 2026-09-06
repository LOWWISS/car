<?php
/**
 * Partial: car card. Reusable across catalog, search results, dashboard.
 * Expects $car (array) with: id, title, make, model, year, primary_image,
 * current_bid, starting_price, bid_count, auction_end, status.
 *
 * SECURITY (XSS): every dynamic value is escaped with e().
 */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$img = $car['primary_image'] ?? null;
$imgSrc = $img ? $base . '/' . $img : $base . '/assets/images/placeholder.svg';
$current = $car['current_bid'] ?? $car['starting_price'];
$ended = !empty($car['auction_end']) && strtotime($car['auction_end']) <= time();
$status = $car['status'] ?? 'active';
$badge = match ($status) {
    'active'   => $ended ? null : '<span class="absolute top-2 right-2 bg-auction text-white text-xs font-semibold px-2 py-1 rounded animate-pulse-glow">LIVE</span>',
    'sold'     => '<span class="absolute top-2 right-2 bg-slate-700 text-white text-xs font-semibold px-2 py-1 rounded animate-scale-in">SOLD</span>',
    'closed'   => '<span class="absolute top-2 right-2 bg-slate-500 text-white text-xs font-semibold px-2 py-1 rounded">CLOSED</span>',
    'pending'  => '<span class="absolute top-2 right-2 bg-amber-500 text-white text-xs font-semibold px-2 py-1 rounded animate-scale-in">PENDING</span>',
    'rejected' => '<span class="absolute top-2 right-2 bg-red-700 text-white text-xs font-semibold px-2 py-1 rounded">REJECTED</span>',
    default    => null,
};
$staggerClass = isset($stagger) ? $stagger : '';
?>
<article class="card group card-hover animate-fade-in-up <?= $staggerClass ?>">
  <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="block">
    <div class="relative aspect-[4/3] bg-slate-100 overflow-hidden rounded-t-lg img-zoom">
      <img src="<?= e($imgSrc) ?>" alt="<?= e($car['title']) ?>" class="w-full h-full object-cover" loading="lazy">
      <?= $badge ?? '' ?>
    </div>
    <div class="p-4 space-y-2">
      <h3 class="font-semibold text-slate-900 truncate"><?= e($car['title']) ?></h3>
      <p class="text-xs text-slate-500"><?= e($car['year']) ?> &middot; <?= e($car['make'] ?? '') ?> &middot; <?= e($car['model'] ?? '') ?></p>

      <div class="flex items-end justify-between pt-2">
        <div>
          <div class="text-[11px] uppercase tracking-wide text-slate-400">Current bid</div>
          <div class="text-lg font-bold text-auction-dark">&#8369;<?= number_format((float)$current, 0) ?></div>
        </div>
        <div class="text-right">
          <div class="text-[11px] uppercase tracking-wide text-slate-400">Bids</div>
          <div class="text-sm font-semibold"><?= (int)($car['bid_count'] ?? 0) ?></div>
        </div>
      </div>

      <?php if ($status === 'active' && !$ended): ?>
        <div class="countdown text-center text-xs font-semibold text-auction bg-red-50 rounded py-1.5 mt-2"
             data-end="<?= e($car['auction_end']) ?>">Loading...</div>
        <span class="btn-primary w-full text-center text-sm mt-2 block">Bid Now</span>
      <?php elseif ($status === 'sold'): ?>
        <div class="text-center text-xs font-semibold text-slate-600 bg-slate-100 rounded py-1.5 mt-2">Auction ended</div>
      <?php else: ?>
        <div class="text-center text-xs font-semibold text-slate-500 bg-slate-100 rounded py-1.5 mt-2"><?= e(ucfirst($status)) ?></div>
      <?php endif; ?>
    </div>
  </a>
</article>
