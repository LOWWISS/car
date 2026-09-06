<?php
/** Car detail page: gallery, specs, live bid panel, bid history. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$car = $car; $images = $images; $history = $history; $highest = $highest;
$ended = strtotime($car['auction_end']) <= time();
$active = $car['status'] === 'active' && !$ended;
$currentBid = $highest ? (float)$highest['bid_amount'] : (float)$car['starting_price'];
$minIncrement = max(100.0, round($currentBid * 0.01, 2));
$minNext = $currentBid + $minIncrement;
$isOwner = Auth::check() && (int)$car['seller_id'] === Auth::id();
$canBid = Auth::check() && !$isOwner && $active;
$primary = $images[0] ?? null;
$primarySrc = $primary ? $base . '/' . $primary['image_path'] : $base . '/assets/images/placeholder.svg';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <a href="<?= $base ?>/cars" class="text-sm text-brand hover:underline mb-4 inline-flex items-center gap-1">&larr; Back to catalog</a>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Gallery + specs (2 cols) -->
    <div class="lg:col-span-2 space-y-6">
      <div class="card overflow-hidden animate-fade-in-up">
        <div class="relative bg-slate-100">
          <img id="mainImage" src="<?= e($primarySrc) ?>" alt="<?= e($car['title']) ?>" class="w-full h-96 object-cover">
          <?php if ($active): ?>
            <span class="absolute top-3 right-3 badge badge-danger animate-pulse-glow">
              <span class="w-2 h-2 rounded-full bg-auction"></span> LIVE
            </span>
          <?php elseif ($car['status'] === 'sold'): ?>
            <span class="absolute top-3 right-3 badge badge-neutral">SOLD</span>
          <?php endif; ?>
        </div>
        <?php if (count($images) > 1): ?>
          <div class="flex gap-2 p-3 overflow-x-auto">
            <?php foreach ($images as $img): ?>
              <img src="<?= $base ?>/<?= e($img['image_path']) ?>" alt="thumbnail"
                   class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-brand transition"
                   onclick="document.getElementById('mainImage').src=this.src">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="card p-6 animate-fade-in-up stagger-2">
        <h2 class="font-semibold text-lg mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Specifications
        </h2>
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 text-sm">
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Make</dt><dd class="font-medium text-slate-800"><?= e($car['make']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Model</dt><dd class="font-medium text-slate-800"><?= e($car['model']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Year</dt><dd class="font-medium text-slate-800"><?= e($car['year']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Mileage</dt><dd class="font-medium text-slate-800"><?= number_format((int)$car['mileage']) ?> km</dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Body type</dt><dd class="font-medium text-slate-800"><?= e($car['body_type']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Condition</dt><dd class="font-medium text-slate-800"><?= e($car['condition']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Location</dt><dd class="font-medium text-slate-800"><?= e($car['location']) ?></dd></div>
        </dl>
        <?php if (!empty($car['description'])): ?>
          <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="font-semibold mb-2">Description</h3>
            <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed"><?= e($car['description']) ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Bid panel (1 col) -->
    <div class="space-y-6">
      <div class="card p-6 animate-fade-in-up stagger-1">
        <h1 class="text-xl font-bold text-slate-900"><?= e($car['title']) ?></h1>
        <p class="text-sm text-slate-500 mt-1 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Listed by <?= e($car['seller_name']) ?>
        </p>

        <div class="mt-4 p-4 rounded-xl bg-gradient-to-br from-red-50 to-orange-50 border border-red-100">
          <div class="text-xs uppercase tracking-wide text-slate-500">Current highest bid</div>
          <div id="currentBidDisplay" class="text-3xl font-bold text-auction-dark mt-1">&#8369;<?= number_format($currentBid, 2) ?></div>
          <div class="text-xs text-slate-500 mt-1"><?= (int)($car['bid_count'] ?? 0) ?> bid<?= $car['bid_count']==1?'':'s' ?> so far</div>
        </div>

        <div class="mt-4 p-3 rounded-lg bg-slate-50">
          <div class="text-xs uppercase tracking-wide text-slate-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Time remaining
          </div>
          <div id="detailCountdown" class="countdown text-lg font-bold text-auction <?= $active?'':'opacity-60' ?>" data-end="<?= e($car['auction_end']) ?>">
            <?= $active ? 'Loading...' : 'Auction ended' ?>
          </div>
        </div>

        <?php if ($active && $car['buy_now_price']): ?>
          <div class="mt-4 p-3 rounded-lg bg-slate-50 flex items-center justify-between">
            <div>
              <div class="text-xs text-slate-500">Buy Now price</div>
              <div class="font-bold text-slate-900 text-lg">&#8369;<?= number_format((float)$car['buy_now_price'], 2) ?></div>
            </div>
            <?php if ($canBid): ?>
              <form method="post" action="<?= $base ?>/bids/buy-now/<?= (int)$car['id'] ?>" onsubmit="return confirm('Buy this car now for &#8369;<?= number_format((float)$car['buy_now_price'],2) ?>?');">
                <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
                <button type="submit" class="btn-success text-sm">Buy Now</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php if ($canBid): ?>
          <form id="bidForm" class="mt-4 space-y-3" action="<?= $base ?>/bids/ajax/<?= (int)$car['id'] ?>" method="post">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <div>
              <label for="bid_amount" class="form-label">Your bid (min &#8369;<?= number_format($minNext, 2) ?>)</label>
              <input id="bid_amount" type="number" name="bid_amount" step="0.01" min="<?= e($minNext) ?>" required class="form-input" placeholder="Enter your bid amount">
            </div>
            <button id="bidSubmit" type="submit" class="btn-primary w-full">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
              Place Bid
            </button>
          </form>
        <?php elseif (Auth::guest()): ?>
          <a href="<?= $base ?>/login" class="btn-primary w-full text-center mt-4 block">Log in to bid</a>
        <?php elseif ($isOwner): ?>
          <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-100 text-amber-800 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            This is your listing.
          </div>
        <?php elseif (!$active): ?>
          <div class="mt-4 p-3 rounded-lg bg-slate-100 text-slate-600 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            This auction has ended.
          </div>
        <?php endif; ?>

        <?php if ($isOwner || Auth::is('admin')): ?>
          <div class="mt-4 flex gap-2">
            <a href="<?= $base ?>/cars/edit/<?= (int)$car['id'] ?>" class="btn-outline text-sm flex-1 text-center">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit
            </a>
            <form method="post" action="<?= $base ?>/cars/delete/<?= (int)$car['id'] ?>" onsubmit="return confirm('Delete this listing? This cannot be undone.');">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="btn-danger text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
              </button>
            </form>
          </div>
        <?php endif; ?>

        <?php if (Auth::check() && !$isOwner): ?>
          <button id="watchlistBtn" type="button"
                  class="mt-3 w-full text-sm font-medium py-2.5 rounded-lg border border-slate-200 hover:border-brand hover:text-brand transition flex items-center justify-center gap-2"
                  data-car="<?= (int)$car['id'] ?>"
                  data-csrf="<?= e($csrf) ?>"
                  data-watching="<?= $watching ? '1':'0' ?>">
            <?= $watching ? '&#9733; Watching' : '&#9734; Add to watchlist' ?>
          </button>
        <?php endif; ?>
      </div>

      <!-- Bid history -->
      <div class="card p-6 animate-fade-in-up stagger-3">
        <h2 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          Bid History
        </h2>
        <ul id="bidHistory" class="space-y-3 text-sm">
          <?php if (empty($history)): ?>
            <li class="text-slate-400 text-center py-4">No bids yet. Be the first!</li>
          <?php else: ?>
            <?php foreach ($history as $h): ?>
              <li class="flex justify-between items-center border-b border-slate-100 pb-2">
                <span class="font-medium text-slate-700 flex items-center gap-2">
                  <span class="w-7 h-7 rounded-full bg-brand-light text-brand flex items-center justify-center text-xs font-bold"><?= e(strtoupper(substr($h['bidder_name'], 0, 1))) ?></span>
                  <?= e($h['bidder_name']) ?>
                </span>
                <span class="text-auction-dark font-semibold">&#8369;<?= number_format((float)$h['bid_amount'], 2) ?></span>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<script nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
  // Live bid polling + AJAX submission (see app.js for shared helpers)
  window.CAR_DETAIL = { id: <?= (int)$car['id'] ?>, csrf: <?= json_encode($csrf) ?>, active: <?= $active?'true':'false' ?> };
</script>
