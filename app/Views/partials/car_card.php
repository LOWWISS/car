<?php
/**
 * Partial: car card. Reusable across catalog, search results, dashboard.
 * Expects $car (array) with: id, title, make, model, year, primary_image,
 * current_bid, starting_price, bid_count, auction_end, status.
 *
 * Set $cardDark = true before including for the dark dashboard-style card.
 *
 * SECURITY (XSS): every dynamic value is escaped with e().
 */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$img = $car['primary_image'] ?? null;
$imgSrc = $img ? $base . '/' . $img : $base . '/assets/images/placeholder.svg';
$current = $car['current_bid'] ?? $car['starting_price'];
$ended = !empty($car['auction_end']) && strtotime($car['auction_end']) <= time();
$status = $car['status'] ?? 'active';
$dk = !empty($cardDark);
$badge = match ($status) {
    'active'   => $ended ? null : '<span class="absolute top-2 right-2 bg-auction text-white text-xs font-semibold px-2 py-1 rounded animate-pulse-glow">LIVE</span>',
    'sold'     => '<span class="absolute top-2 right-2 bg-slate-700 text-white text-xs font-semibold px-2 py-1 rounded animate-scale-in">SOLD</span>',
    'closed'   => '<span class="absolute top-2 right-2 bg-slate-500 text-white text-xs font-semibold px-2 py-1 rounded">CLOSED</span>',
    'pending'  => '<span class="absolute top-2 right-2 bg-amber-500 text-white text-xs font-semibold px-2 py-1 rounded animate-scale-in">PENDING</span>',
    'rejected' => '<span class="absolute top-2 right-2 bg-red-700 text-white text-xs font-semibold px-2 py-1 rounded">REJECTED</span>',
    default    => null,
};
$staggerClass = isset($stagger) ? $stagger : '';
$cardCls = $dk
    ? 'group rounded-xl border border-white/[0.07] bg-white/[0.02] overflow-hidden hover:bg-white/[0.05] hover:border-white/15 transition animate-fade-in-up'
    : 'card group card-hover animate-fade-in-up';
$titleCls = $dk ? 'font-semibold text-white truncate' : 'font-semibold text-slate-900 truncate';
$metaCls  = $dk ? 'text-xs text-zinc-500' : 'text-xs text-slate-500';
$labCls   = 'text-[11px] uppercase tracking-wide ' . ($dk ? 'text-zinc-500' : 'text-slate-400');
$priceCls = $dk ? 'text-lg font-bold text-white' : 'text-lg font-bold text-auction-dark';
$bidsCls  = $dk ? 'text-sm font-semibold text-zinc-300' : 'text-sm font-semibold';
$cdCls    = $dk ? 'bg-auction/15 text-red-300' : 'bg-red-50 text-auction';
$noteCls  = $dk ? 'text-zinc-400 bg-white/5' : 'text-slate-500 bg-slate-100';
?>
<article class="<?= $cardCls ?> <?= $staggerClass ?>">
  <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="block">
    <div class="relative aspect-[4/3] <?= $dk ? 'bg-white/5' : 'bg-slate-100' ?> overflow-hidden <?= $dk ? '' : 'rounded-t-lg' ?> img-zoom">
      <img src="<?= e($imgSrc) ?>" alt="<?= e($car['title']) ?>" class="w-full h-full object-cover" loading="lazy">
      <?= $badge ?? '' ?>
    </div>
    <div class="p-4 space-y-2">
      <h3 class="<?= $titleCls ?>"><?= e($car['title']) ?></h3>
      <p class="<?= $metaCls ?>"><?= e($car['year']) ?> &middot; <?= e($car['make'] ?? '') ?> &middot; <?= e($car['model'] ?? '') ?></p>

      <div class="flex items-end justify-between pt-2">
        <div>
          <div class="<?= $labCls ?>">Current bid</div>
          <div class="<?= $priceCls ?>">&#8369;<?= number_format((float)$current, 0) ?></div>
        </div>
        <div class="text-right">
          <div class="<?= $labCls ?>">Bids</div>
          <div class="<?= $bidsCls ?>"><?= (int)($car['bid_count'] ?? 0) ?></div>
        </div>
      </div>

      <?php if ($status === 'active' && !$ended): ?>
        <div class="countdown text-center text-xs font-semibold <?= $cdCls ?> rounded py-1.5 mt-2"
             data-end="<?= e($car['auction_end']) ?>">Loading...</div>
        <span class="btn-primary w-full text-center text-sm mt-2 block">Bid Now</span>
      <?php elseif ($status === 'sold'): ?>
        <div class="text-center text-xs font-semibold <?= $noteCls ?> rounded py-1.5 mt-2">Auction ended</div>
      <?php else: ?>
        <div class="text-center text-xs font-semibold <?= $noteCls ?> rounded py-1.5 mt-2"><?= e(ucfirst($status)) ?></div>
      <?php endif; ?>
    </div>
  </a>
</article>
