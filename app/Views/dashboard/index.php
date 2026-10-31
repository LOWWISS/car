<?php
/** Bidder dashboard — dark analytics layout mirroring the admin dashboard. */
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
$img = function (?string $p) use ($base): string {
    return $p ? $base . '/' . $p : $base . '/assets/images/placeholder.svg';
};
$money = fn(float $v): string => '₱' . number_format($v, 2);

// Status pill for a bid row
$bidPill = function (array $b) use ($base): string {
    $ended = !empty($b['auction_end']) && strtotime($b['auction_end']) <= time();
    $live  = ($b['car_status'] ?? '') === 'active' && !$ended;
    if (!$live) return '<span class="inline-flex items-center rounded-md bg-white/10 px-1.5 py-0.5 text-[11px] font-semibold text-zinc-400">Ended</span>';
    if ((float) $b['bid_amount'] >= (float) ($b['car_high'] ?? 0))
        return '<span class="inline-flex items-center rounded-md bg-emerald-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-300">Winning</span>';
    return '<span class="inline-flex items-center rounded-md bg-rose-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-rose-300">Outbid</span>';
};
// Transaction pill + optional pay link for won rows
$txPill = function (?string $s): array {
    return match ($s) {
        'completed'          => ['bg-emerald-400/10 text-emerald-300', 'Completed', false],
        'payment_confirmed'  => ['bg-blue-400/10 text-blue-300',       'Paid',      false],
        'handover_ready'     => ['bg-indigo-400/10 text-indigo-300',   'Handover',  false],
        'payment_pending'    => ['bg-amber-400/10 text-amber-300',     'Pay due',   true],
        'cancelled'          => ['bg-red-400/10 text-red-300',         'Cancelled', false],
        default              => ['bg-white/10 text-zinc-400',          'Pending',   true],
    };
};
$th = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$td = 'px-4 py-3 text-sm';
?>

<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">My Dashboard</h1>
        <p class="text-sm text-zinc-500 mt-0.5">Your bidding activity at a glance</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse live auctions</a>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-1">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Bids placed</span>
          <?= $trendBadge($pct($trends['bids']['cur'], $trends['bids']['prev'])) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= (int)$stats['active_bids'] ?></div>
        <div class="mt-2 text-xs text-zinc-500">+<?= (int)$trends['bids']['cur'] ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-2">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Total spent</span>
          <?= $trendBadge($pct($trends['spend']['cur'], $trends['spend']['prev'])) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= $money($totalSpent) ?></div>
        <div class="mt-2 text-xs text-zinc-500"><?= $money($trends['spend']['cur']) ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Auctions won</span>
          <?= $trendBadge($pct($trends['won']['cur'], $trends['won']['prev'])) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= (int)$wonCount ?></div>
        <div class="mt-2 text-xs text-zinc-500">+<?= (int)$trends['won']['cur'] ?> in the last 30 days</div>
      </div>
      <div class="rounded-xl border border-white/[0.07] p-5 animate-fade-in-up stagger-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-zinc-400">Watchlist</span>
          <?= $trendBadge($pct($trends['watch']['cur'], $trends['watch']['prev'])) ?>
        </div>
        <div class="mt-2 text-2xl font-bold text-white"><?= (int)$stats['watchlist'] ?></div>
        <div class="mt-2 text-xs text-zinc-500">+<?= (int)$trends['watch']['cur'] ?> added in the last 30 days</div>
      </div>
    </div>

    <!-- Bidding activity chart -->
    <div class="rounded-xl border border-white/[0.07] p-5 mb-6 animate-fade-in-up">
      <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
          <h2 class="text-base font-semibold text-white">My bidding activity</h2>
          <p class="text-xs text-zinc-500 mt-0.5 flex items-center gap-4">
            <span id="chartRangeLabel">Daily totals for the last 90 days</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-indigo-400"></span> Bids</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400/70"></span> Spend (₱k)</span>
          </p>
        </div>
        <div class="flex gap-1 rounded-lg bg-white/[0.04] p-1" id="chartRanges">
          <button data-days="90" class="range-btn rounded-md px-3 py-1 text-xs font-medium bg-white/10 text-white">Last 3 months</button>
          <button data-days="30" class="range-btn rounded-md px-3 py-1 text-xs font-medium text-zinc-400 hover:text-white">Last 30 days</button>
          <button data-days="7"  class="range-btn rounded-md px-3 py-1 text-xs font-medium text-zinc-400 hover:text-white">Last 7 days</button>
        </div>
      </div>
      <div class="relative">
        <svg id="bidChart" viewBox="0 0 900 260" class="w-full h-64" preserveAspectRatio="none"></svg>
        <div id="chartTip" class="hidden absolute rounded-lg border border-white/10 bg-[#1c1c22] px-3 py-2 text-xs shadow-xl pointer-events-none z-10"></div>
      </div>
    </div>

    <!-- Tabbed tables -->
    <div class="rounded-xl border border-white/[0.07] animate-fade-in-up">
      <div class="flex items-center gap-1 border-b border-white/[0.07] px-3 pt-3">
        <button data-tab="bids" class="tab-btn rounded-t-lg px-4 py-2 text-sm font-medium bg-white/[0.06] text-white border-b-2 border-brand">My bids <span class="ml-1 text-xs text-zinc-500"><?= count($bids) ?></span></button>
        <button data-tab="won" class="tab-btn rounded-t-lg px-4 py-2 text-sm font-medium text-zinc-400 hover:text-white">Won auctions <span class="ml-1 text-xs text-zinc-500"><?= count($won) ?></span></button>
        <button data-tab="watch" class="tab-btn rounded-t-lg px-4 py-2 text-sm font-medium text-zinc-400 hover:text-white">Watchlist <span class="ml-1 text-xs text-zinc-500"><?= count($watch) ?></span></button>
      </div>

      <!-- My bids -->
      <div class="tab-panel" data-panel="bids">
        <?php if (empty($bids)): ?>
          <p class="px-4 py-10 text-center text-sm text-zinc-500">No bids yet — join a live auction to get started.</p>
        <?php else: ?>
          <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
            <table class="w-full">
              <thead class="bg-white/[0.03] sticky top-0"><tr>
                <th class="<?= $th ?>">Car</th><th class="<?= $th ?> text-right">My bid</th>
                <th class="<?= $th ?> text-right">Current high</th><th class="<?= $th ?>">Status</th>
                <th class="<?= $th ?>">Ends</th><th class="<?= $th ?> text-right"></th>
              </tr></thead>
              <tbody class="divide-y divide-white/[0.05]">
                <?php foreach ($bids as $b): ?>
                  <tr class="hover:bg-white/[0.03] transition">
                    <td class="<?= $td ?>">
                      <div class="flex items-center gap-3">
                        <img src="<?= e($img($b['primary_image'])) ?>" class="w-12 h-9 rounded-md object-cover bg-white/5" alt="">
                        <div class="min-w-0"><div class="font-medium text-zinc-100 truncate max-w-[220px]"><?= e($b['car_title']) ?></div></div>
                      </div>
                    </td>
                    <td class="<?= $td ?> text-right font-semibold text-zinc-100"><?= $money((float)$b['bid_amount']) ?></td>
                    <td class="<?= $td ?> text-right text-zinc-400"><?= $money((float)($b['car_high'] ?? 0)) ?></td>
                    <td class="<?= $td ?>"><?= $bidPill($b) ?></td>
                    <td class="<?= $td ?> text-xs text-zinc-500"><span class="countdown" data-end="<?= e($b['auction_end']) ?>"></span></td>
                    <td class="<?= $td ?> text-right"><a href="<?= $base ?>/cars/view/<?= (int)$b['car_id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="px-4 py-2.5 border-t border-white/[0.07] text-right">
            <a href="<?= $base ?>/dashboard/my-bids" class="text-xs text-zinc-500 hover:text-white transition">View all my bids →</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Won auctions -->
      <div class="tab-panel hidden" data-panel="won">
        <?php if (empty($won)): ?>
          <p class="px-4 py-10 text-center text-sm text-zinc-500">No wins yet — keep bidding!</p>
        <?php else: ?>
          <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
            <table class="w-full">
              <thead class="bg-white/[0.03] sticky top-0"><tr>
                <th class="<?= $th ?>">Car</th><th class="<?= $th ?> text-right">Won at</th>
                <th class="<?= $th ?> text-right">Winning bid</th><th class="<?= $th ?>">Transaction</th><th class="<?= $th ?> text-right"></th>
              </tr></thead>
              <tbody class="divide-y divide-white/[0.05]">
                <?php foreach ($won as $w): [$wCls, $wTxt, $needsPay] = $txPill($w['transaction_status'] ?? null); ?>
                  <tr class="hover:bg-white/[0.03] transition">
                    <td class="<?= $td ?>">
                      <div class="flex items-center gap-3">
                        <img src="<?= e($img($w['primary_image'])) ?>" class="w-12 h-9 rounded-md object-cover bg-white/5" alt="">
                        <div class="min-w-0"><div class="font-medium text-zinc-100 truncate max-w-[220px]"><?= e($w['title']) ?></div></div>
                      </div>
                    </td>
                    <td class="<?= $td ?> text-right text-xs text-zinc-500"><?= e(date('M j, Y', strtotime($w['won_at']))) ?></td>
                    <td class="<?= $td ?> text-right font-semibold text-zinc-100"><?= $money((float)$w['bid_amount']) ?></td>
                    <td class="<?= $td ?>"><span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold <?= $wCls ?>"><?= $wTxt ?></span></td>
                    <td class="<?= $td ?> text-right">
                      <?php if ($needsPay && !empty($w['transaction_id'])): ?>
                        <a href="<?= $base ?>/transactions/view/<?= (int)$w['transaction_id'] ?>" class="rounded-md bg-brand/80 px-2.5 py-1 text-xs font-semibold text-white hover:bg-brand transition">Pay now</a>
                      <?php else: ?>
                        <a href="<?= $base ?>/cars/view/<?= (int)$w['id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View</a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="px-4 py-2.5 border-t border-white/[0.07] text-right">
            <a href="<?= $base ?>/dashboard/won-auctions" class="text-xs text-zinc-500 hover:text-white transition">View all won auctions →</a>
          </div>
        <?php endif; ?>
      </div>

      <!-- Watchlist -->
      <div class="tab-panel hidden" data-panel="watch">
        <?php if (empty($watch)): ?>
          <p class="px-4 py-10 text-center text-sm text-zinc-500">Nothing watched yet — tap ☆ on any listing.</p>
        <?php else: ?>
          <div class="overflow-x-auto max-h-[420px] overflow-y-auto">
            <table class="w-full">
              <thead class="bg-white/[0.03] sticky top-0"><tr>
                <th class="<?= $th ?>">Car</th><th class="<?= $th ?> text-right">Current bid</th>
                <th class="<?= $th ?> text-right">Bids</th><th class="<?= $th ?>">Ends</th><th class="<?= $th ?> text-right"></th>
              </tr></thead>
              <tbody class="divide-y divide-white/[0.05]">
                <?php foreach ($watch as $c): ?>
                  <tr class="hover:bg-white/[0.03] transition">
                    <td class="<?= $td ?>">
                      <div class="flex items-center gap-3">
                        <img src="<?= e($img($c['primary_image'])) ?>" class="w-12 h-9 rounded-md object-cover bg-white/5" alt="">
                        <div class="min-w-0"><div class="font-medium text-zinc-100 truncate max-w-[220px]"><?= e($c['title']) ?></div></div>
                      </div>
                    </td>
                    <td class="<?= $td ?> text-right font-semibold text-zinc-100"><?= $money((float)($c['current_bid'] ?? 0)) ?></td>
                    <td class="<?= $td ?> text-right text-zinc-400"><?= (int)($c['bid_count'] ?? 0) ?></td>
                    <td class="<?= $td ?> text-xs text-zinc-500"><span class="countdown" data-end="<?= e($c['auction_end']) ?>"></span></td>
                    <td class="<?= $td ?> text-right"><a href="<?= $base ?>/cars/view/<?= (int)$c['id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="px-4 py-2.5 border-t border-white/[0.07] text-right">
            <a href="<?= $base ?>/watchlist" class="text-xs text-zinc-500 hover:text-white transition">View all watchlist →</a>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script nonce="<?= $nonce ?>">
(function () {
  var series = <?= json_encode($chart) ?>;
  var svg = document.getElementById('bidChart');
  var tip = document.getElementById('chartTip');
  var W = 900, H = 260, PAD = 34, days = 90;

  function render() {
    var data = series.slice(-days);
    var max = 1;
    data.forEach(function (p) { max = Math.max(max, p.bids, p.spend); });
    var x = function (i) { return PAD + (i / Math.max(1, data.length - 1)) * (W - PAD * 2); };
    var y = function (v) { return H - PAD - (v / max) * (H - PAD * 2); };
    var s = '';

    // gridlines
    for (var g = 0; g <= 4; g++) {
      var gy = PAD + (g / 4) * (H - PAD * 2);
      s += '<line x1="' + PAD + '" y1="' + gy + '" x2="' + (W - PAD) + '" y2="' + gy + '" stroke="rgba(255,255,255,.06)"/>';
      s += '<text x="' + (PAD - 6) + '" y="' + (gy + 3) + '" text-anchor="end" font-size="10" fill="#52525b">' + Math.round(max * (1 - g / 4)) + '</text>';
    }

    function pathFor(key, color, fill) {
      if (!data.length) return '';
      var d = 'M' + x(0) + ' ' + y(data[0][key]);
      for (var i = 1; i < data.length; i++) d += ' L' + x(i) + ' ' + y(data[i][key]);
      var p = '<path d="' + d + '" fill="none" stroke="' + color + '" stroke-width="2.5" stroke-linejoin="round"/>';
      p += '<path d="' + d + ' L' + x(data.length - 1) + ' ' + (H - PAD) + ' L' + x(0) + ' ' + (H - PAD) + ' Z" fill="' + fill + '"/>';
      return p;
    }

    s += pathFor('bids', '#818cf8', 'rgba(99,102,241,.15)');
    s += pathFor('spend', '#34d399', 'rgba(52,211,153,.12)');

    // x labels (~5 ticks)
    var step = Math.max(1, Math.floor(data.length / 5));
    for (var i = 0; i < data.length; i += step) {
      var dt = new Date(data[i].d + 'T00:00:00');
      s += '<text x="' + x(i) + '" y="' + (H - 10) + '" text-anchor="middle" font-size="10" fill="#52525b">' +
           dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) + '</text>';
    }

    // hover zones
    data.forEach(function (p, i) {
      s += '<rect x="' + (x(i) - (W - PAD * 2) / data.length / 2) + '" y="0" width="' + ((W - PAD * 2) / data.length) + '" height="' + H + '" fill="transparent" data-i="' + i + '" class="hz"/>';
    });
    svg.innerHTML = s;

    document.getElementById('chartRangeLabel').textContent = 'Daily totals for the last ' + days + ' days';

    svg.querySelectorAll('.hz').forEach(function (r) {
      r.addEventListener('mousemove', function (e) {
        var p = data[+r.getAttribute('data-i')];
        var dt = new Date(p.d + 'T00:00:00');
        tip.innerHTML = '<div class="font-semibold text-zinc-100">' + dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) + '</div>' +
          '<div class="text-indigo-300">Bids: ' + p.bids + '</div>' +
          '<div class="text-emerald-300">Spend: ₱' + (p.spend * 1000).toLocaleString() + '</div>';
        var rect = svg.getBoundingClientRect();
        tip.style.left = Math.min(rect.width - 130, (e.clientX - rect.left) + 14) + 'px';
        tip.style.top = Math.max(0, (e.clientY - rect.top) - 60) + 'px';
        tip.classList.remove('hidden');
      });
      r.addEventListener('mouseleave', function () { tip.classList.add('hidden'); });
    });
  }

  document.querySelectorAll('#chartRanges .range-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      days = +b.getAttribute('data-days');
      document.querySelectorAll('#chartRanges .range-btn').forEach(function (o) {
        o.className = 'range-btn rounded-md px-3 py-1 text-xs font-medium ' +
          (o === b ? 'bg-white/10 text-white' : 'text-zinc-400 hover:text-white');
      });
      render();
    });
  });

  // Tabs
  document.querySelectorAll('.tab-btn').forEach(function (b) {
    b.addEventListener('click', function () {
      var t = b.getAttribute('data-tab');
      document.querySelectorAll('.tab-btn').forEach(function (o) {
        o.className = 'tab-btn rounded-t-lg px-4 py-2 text-sm font-medium ' +
          (o === b ? 'bg-white/[0.06] text-white border-b-2 border-brand' : 'text-zinc-400 hover:text-white');
      });
      document.querySelectorAll('.tab-panel').forEach(function (p) {
        p.classList.toggle('hidden', p.getAttribute('data-panel') !== t);
      });
    });
  });

  render();
})();
</script>
