<?php
/** Transactions list — dark analytics layout mirroring the admin dashboard. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$nonce = SecurityHeaders::cspNonce();
$role = $role ?? 'buyer';

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

$txPill = function (string $s): array {
    return match ($s) {
        'completed'          => ['bg-emerald-400/10 text-emerald-300', 'Completed'],
        'payment_confirmed'  => ['bg-blue-400/10 text-blue-300',       'Paid'],
        'ready_for_handover' => ['bg-indigo-400/10 text-indigo-300',   'Handover'],
        'payment_pending'    => ['bg-amber-400/10 text-amber-300',     'Pay due'],
        'cancelled'          => ['bg-red-400/10 text-red-300',         'Cancelled'],
        default              => ['bg-white/10 text-zinc-400',          ucfirst($s)],
    };
};

// Trends: count + value in last 30d vs prior 30d (from created_at)
$cut30 = strtotime('-30 days'); $cut60 = strtotime('-60 days');
$curTx = $prevTx = 0; $curAmt = $prevAmt = 0.0; $pending = $done = 0;
$total = array_sum(array_map(fn($t) => (float)$t['final_amount'], $items));
foreach ($items as $t) {
    $ts = strtotime($t['created_at']);
    if ($ts >= $cut30) { $curTx++; $curAmt += (float)$t['final_amount']; }
    elseif ($ts >= $cut60) { $prevTx++; $prevAmt += (float)$t['final_amount']; }
    if ($t['status'] === 'payment_pending') $pending++;
    if ($t['status'] === 'completed') $done++;
}
$th = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$td = 'px-4 py-3.5 text-sm';
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">Transactions</h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= count($items) ?> transaction<?= count($items)===1?'':'s' ?><?= $pending ? " · <span class='text-amber-300'>$pending awaiting payment</span>" : '' ?></p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse live auctions</a>
    </div>

    <!-- Stat cards (admin-style trend cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-1">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Total value</span>
          <?= $trendBadge($pct($curAmt, $prevAmt)) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white">₱<?= number_format($total, 0) ?></div>
        <div class="mt-2 text-xs text-zinc-500">₱<?= number_format($curAmt, 0) ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-2">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Transactions</span>
          <?= $trendBadge($pct($curTx, $prevTx)) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= count($items) ?></div>
        <div class="mt-2 text-xs text-zinc-500">+<?= $curTx ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Awaiting payment</span>
        </div>
        <div class="mt-2 text-2xl font-bold <?= $pending ? 'text-amber-300' : 'text-white' ?>"><?= $pending ?></div>
        <div class="mt-2 text-xs text-zinc-500">Action needed on these</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Completed</span>
        </div>
        <div class="mt-2 text-2xl font-bold text-emerald-300"><?= $done ?></div>
        <div class="mt-2 text-xs text-zinc-500">Fully settled</div>
      </div>
    </div>

    <?php if (empty($items)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-emerald-400/10 border border-emerald-400/20 flex items-center justify-center text-emerald-400">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No transactions yet</h3>
        <p class="mt-1 text-sm text-zinc-500">Transactions are created automatically when you win an auction.</p>
        <a href="<?= $base ?>/cars" class="inline-block mt-4 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse auctions</a>
      </div>
    <?php else: ?>

      <!-- Admin-style data table -->
      <div class="rounded-xl border border-white/[0.07] overflow-hidden animate-fade-in-up">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/[0.07]">
          <h2 class="text-sm font-semibold text-white">Transaction history</h2>
          <span class="text-xs text-zinc-500"><?= count($items) ?> row<?= count($items)===1?'':'s' ?></span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left" id="txTable">
            <thead class="bg-white/[0.03]">
              <tr>
                <th class="<?= $th ?>">Vehicle</th>
                <th class="<?= $th ?>"><?= $role === 'admin' ? 'Seller / Buyer' : ($role === 'seller' ? 'Buyer' : 'Seller') ?></th>
                <th class="<?= $th ?> text-right">Final amount</th>
                <th class="<?= $th ?>">Status</th>
                <th class="<?= $th ?>">Created</th>
                <th class="<?= $th ?> text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/[0.05]">
              <?php foreach ($items as $t): [$cls, $txt] = $txPill($t['status']); ?>
                <tr class="hover:bg-white/[0.03] transition">
                  <td class="<?= $td ?>">
                    <div class="flex items-center gap-3">
                      <?php if (!empty($t['primary_image'])): ?>
                        <img src="<?= $base ?>/<?= e($t['primary_image']) ?>" alt="" class="w-14 h-10 object-cover rounded-md shrink-0 bg-white/5">
                      <?php else: ?>
                        <div class="w-14 h-10 bg-white/5 rounded-md shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                      <?php endif; ?>
                      <a href="<?= $base ?>/transactions/view/<?= (int)$t['id'] ?>" class="font-medium text-zinc-100 hover:text-white transition truncate max-w-[220px]"><?= e($t['car_title']) ?></a>
                    </div>
                  </td>
                  <td class="<?= $td ?> text-zinc-400">
                    <?php if ($role === 'admin'): ?>
                      <?= e($t['seller_name']) ?> <span class="text-zinc-600">&rarr;</span> <?= e($t['winner_name']) ?>
                    <?php elseif ($role === 'seller'): ?>
                      <?= e($t['winner_name']) ?>
                    <?php else: ?>
                      <?= e($t['seller_name']) ?>
                    <?php endif; ?>
                  </td>
                  <td class="<?= $td ?> text-right font-semibold text-zinc-100">₱<?= number_format((float)$t['final_amount'], 2) ?></td>
                  <td class="<?= $td ?>"><span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold <?= $cls ?>"><?= $txt ?></span></td>
                  <td class="<?= $td ?> text-xs text-zinc-500 whitespace-nowrap"><?= e($t['created_at']) ?></td>
                  <td class="<?= $td ?> text-right">
                    <?php if ($t['status'] === 'payment_pending'): ?>
                      <a href="<?= $base ?>/transactions/view/<?= (int)$t['id'] ?>" class="rounded-md bg-brand/80 px-2.5 py-1 text-xs font-semibold text-white hover:bg-brand transition">Pay now</a>
                    <?php else: ?>
                      <a href="<?= $base ?>/transactions/view/<?= (int)$t['id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View</a>
                    <?php endif; ?>
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
  var rows = Array.prototype.slice.call(document.querySelectorAll('#txTable tbody tr'));
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
