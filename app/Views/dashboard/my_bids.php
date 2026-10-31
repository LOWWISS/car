<?php
/** My Bids — dark analytics layout mirroring the admin dashboard. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$nonce = SecurityHeaders::cspNonce();

$pct = function (float $cur, float $prev): int {
    return $prev > 0 ? (int) round(($cur - $prev) / $prev * 100) : ($cur > 0 ? 100 : 0);
};
$trendBadge = function (int $p): string {
    $up = $p >= 0;
    $cls = $up ? 'bg-emerald-400/10 text-emerald-300' : 'bg-rose-400/10 text-rose-300';
    $arrow = $up ? 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' : 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6';
    return '<span class="inline-flex items-center gap-1 rounded-md ' . $cls . ' px-1.5 py-0.5 text-[11px] font-semibold">' .
        '<svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="' . $arrow . '"/></svg>' .
        ($up ? '+' : '') . $p . '%</span>';
};

$bidPill = function (array $b): string {
    $ended = !empty($b['auction_end']) && strtotime($b['auction_end']) <= time();
    $live  = ($b['car_status'] ?? '') === 'active' && !$ended;
    if (!$live) return '<span class="inline-flex items-center rounded-md bg-white/10 px-1.5 py-0.5 text-[11px] font-semibold text-zinc-400">Ended</span>';
    if ((float) $b['bid_amount'] >= (float) ($b['car_high'] ?? 0))
        return '<span class="inline-flex items-center rounded-md bg-emerald-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-300">Winning</span>';
    return '<span class="inline-flex items-center rounded-md bg-rose-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-rose-300">Outbid</span>';
};

// 30-day trends computed from the bids array (b.* includes created_at)
$cut30 = strtotime('-30 days'); $cut60 = strtotime('-60 days');
$win = $out = 0;
$curBids = $prevBids = $curAmt = $prevAmt = 0;
foreach ($bids as $b) {
    $p = $bidPill($b);
    if (str_contains($p, 'Winning')) $win++;
    elseif (str_contains($p, 'Outbid')) $out++;
    $ts = strtotime($b['created_at']);
    if ($ts >= $cut30) { $curBids++; $curAmt += (float)$b['bid_amount']; }
    elseif ($ts >= $cut60) { $prevBids++; $prevAmt += (float)$b['bid_amount']; }
}
$totalAmt = array_sum(array_map(fn($b) => (float)$b['bid_amount'], $bids));
$th = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$td = 'px-4 py-3.5 text-sm';
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">My Bids</h1>
        <p class="text-sm text-zinc-500 mt-0.5">Track every bid you've placed</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">+ Place a bid</a>
    </div>

    <!-- Stat cards (admin-style trend cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-1">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Bids placed</span>
          <?= $trendBadge($pct($curBids, $prevBids)) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= count($bids) ?></div>
        <div class="mt-2 text-xs text-zinc-500">+<?= $curBids ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-2">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Total bid value</span>
          <?= $trendBadge($pct($curAmt, $prevAmt)) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white">₱<?= number_format($totalAmt, 0) ?></div>
        <div class="mt-2 text-xs text-zinc-500">₱<?= number_format($curAmt, 0) ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Winning</span>
        </div>
        <div class="mt-2 text-2xl font-bold text-emerald-300"><?= $win ?></div>
        <div class="mt-2 text-xs text-zinc-500">Highest bidder right now</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Outbid</span>
        </div>
        <div class="mt-2 text-2xl font-bold <?= $out ? 'text-rose-300' : 'text-white' ?>"><?= $out ?></div>
        <div class="mt-2 text-xs text-zinc-500">Someone bid higher — check back</div>
      </div>
    </div>

    <?php if (empty($bids)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">You haven't placed any bids yet</h3>
        <p class="mt-1 text-sm text-zinc-500">Browse available auctions and place your first bid.</p>
        <a href="<?= $base ?>/cars" class="inline-block mt-4 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse auctions</a>
      </div>
    <?php else: ?>

      <!-- Admin-style data table -->
      <div class="rounded-xl border border-white/[0.07] overflow-hidden animate-fade-in-up">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.07]">
          <h2 class="text-sm font-semibold text-white">Bid history</h2>
          <span class="text-xs text-zinc-500"><?= count($bids) ?> row<?= count($bids)===1?'':'s' ?></span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="bidsTable">
            <thead class="bg-white/[0.03]">
              <tr>
                <th class="<?= $th ?>">Listing</th>
                <th class="<?= $th ?> text-right">Your bid</th>
                <th class="<?= $th ?> text-right">Current high</th>
                <th class="<?= $th ?>">Status</th>
                <th class="<?= $th ?>">Ends</th>
                <th class="<?= $th ?> text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/[0.05]">
              <?php foreach ($bids as $b): ?>
                <tr class="hover:bg-white/[0.03] transition">
                  <td class="<?= $td ?>">
                    <div class="flex items-center gap-3">
                      <?php if (!empty($b['primary_image'])): ?>
                        <img src="<?= $base ?>/<?= e($b['primary_image']) ?>" alt="" class="w-14 h-10 object-cover rounded-md shrink-0 bg-white/5">
                      <?php else: ?>
                        <div class="w-14 h-10 bg-white/5 rounded-md shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                      <?php endif; ?>
                      <a href="<?= $base ?>/cars/view/<?= (int)$b['car_id'] ?>" class="font-medium text-zinc-100 hover:text-white transition truncate max-w-[260px]"><?= e($b['car_title']) ?></a>
                    </div>
                  </td>
                  <td class="<?= $td ?> text-right font-semibold text-zinc-100">₱<?= number_format((float)$b['bid_amount'], 2) ?></td>
                  <td class="<?= $td ?> text-right text-zinc-400">₱<?= number_format((float)($b['car_high'] ?? 0), 2) ?></td>
                  <td class="<?= $td ?>"><?= $bidPill($b) ?></td>
                  <td class="<?= $td ?> text-xs text-zinc-500 whitespace-nowrap">
                    <?php if (($b['car_status'] ?? '') === 'active'): ?>
                      <span class="countdown" data-end="<?= e($b['auction_end']) ?>"></span>
                    <?php else: ?>
                      <?= e($b['auction_end']) ?>
                    <?php endif; ?>
                  </td>
                  <td class="<?= $td ?> text-right">
                    <a href="<?= $base ?>/cars/view/<?= (int)$b['car_id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination footer (admin-style) -->
        <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 border-t border-white/[0.07]">
          <span class="text-xs text-zinc-500" id="pgInfo"></span>
          <div class="flex items-center gap-2">
            <select id="pgSize" class="dark-field rounded-lg border border-white/10 bg-white/5 px-2 py-1.5 text-xs text-zinc-300 outline-none">
              <option value="10">10 / page</option>
              <option value="25">25 / page</option>
              <option value="50">50 / page</option>
            </select>
            <button id="pgFirst" class="rounded-lg border border-white/10 px-2 py-1.5 text-zinc-400 hover:bg-white/10 transition" title="First">«</button>
            <button id="pgPrev"  class="rounded-lg border border-white/10 px-2 py-1.5 text-zinc-400 hover:bg-white/10 transition" title="Prev">‹</button>
            <span class="text-xs text-zinc-500" id="pgNum"></span>
            <button id="pgNext"  class="rounded-lg border border-white/10 px-2 py-1.5 text-zinc-400 hover:bg-white/10 transition" title="Next">›</button>
            <button id="pgLast"  class="rounded-lg border border-white/10 px-2 py-1.5 text-zinc-400 hover:bg-white/10 transition" title="Last">»</button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script nonce="<?= $nonce ?>">
(function () {
  var rows = Array.prototype.slice.call(document.querySelectorAll('#bidsTable tbody tr'));
  if (!rows.length) return;
  var sizeEl = document.getElementById('pgSize');
  var info = document.getElementById('pgInfo'), num = document.getElementById('pgNum');
  var page = 1, size = 10;

  function render() {
    var total = rows.length, pages = Math.max(1, Math.ceil(total / size));
    page = Math.min(Math.max(1, page), pages);
    var from = (page - 1) * size, to = Math.min(total, from + size);
    rows.forEach(function (r, i) { r.style.display = (i >= from && i < to) ? '' : 'none'; });
    info.textContent = 'Showing ' + (total ? from + 1 : 0) + '–' + to + ' of ' + total;
    num.textContent = page + ' / ' + pages;
    document.getElementById('pgFirst').disabled = document.getElementById('pgPrev').disabled = page <= 1;
    document.getElementById('pgNext').disabled = document.getElementById('pgLast').disabled = page >= pages;
  }
  document.getElementById('pgFirst').onclick = function () { page = 1; render(); };
  document.getElementById('pgPrev').onclick  = function () { page--; render(); };
  document.getElementById('pgNext').onclick  = function () { page++; render(); };
  document.getElementById('pgLast').onclick  = function () { page = Math.ceil(rows.length / size); render(); };
  sizeEl.onchange = function () { size = +sizeEl.value; page = 1; render(); };
  render();
})();
</script>
