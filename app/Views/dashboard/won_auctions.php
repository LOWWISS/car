<?php
/** Won Auctions — dark, consistent with the bidder dashboard. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');

$txPill = function (?string $s): array {
    return match ($s) {
        'completed'          => ['bg-emerald-400/10 text-emerald-300', 'Completed', false],
        'payment_confirmed'  => ['bg-blue-400/10 text-blue-300',       'Paid',      false],
        'ready_for_handover' => ['bg-indigo-400/10 text-indigo-300',   'Handover',  false],
        'payment_pending'    => ['bg-amber-400/10 text-amber-300',     'Pay due',   true],
        'cancelled'          => ['bg-red-400/10 text-red-300',         'Cancelled', false],
        default              => ['bg-white/10 text-zinc-400',          'Pending',   true],
    };
};

$totalValue = array_sum(array_map(fn($c) => (float)$c['bid_amount'], $won));
$pendingPay = 0;
foreach ($won as $c) {
    [$cls, $txt, $needsPay] = $txPill($c['transaction_status'] ?? null);
    if ($needsPay) $pendingPay++;
}
$th = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$td = 'px-4 py-3.5 text-sm';
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">Won Auctions</h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= count($won) ?> auction<?= count($won)===1?'':'s' ?> won<?= $pendingPay ? " · <span class='text-amber-300'>$pendingPay awaiting payment</span>" : '' ?></p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse live auctions</a>
    </div>

    <?php if (empty($won)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-400/10 border border-amber-400/20 flex items-center justify-center text-amber-400">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No won auctions yet</h3>
        <p class="mt-1 text-sm text-zinc-500">Keep bidding to win your first auction!</p>
        <a href="<?= $base ?>/cars" class="inline-block mt-4 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">Browse auctions</a>
      </div>
    <?php else: ?>

      <!-- Summary chips -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl border border-white/[0.07] p-4 animate-fade-in-up stagger-1">
          <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Auctions won</div>
          <div class="mt-1 text-xl font-bold text-white"><?= count($won) ?></div>
        </div>
        <div class="rounded-xl border border-white/[0.07] p-4 animate-fade-in-up stagger-2">
          <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Total winning value</div>
          <div class="mt-1 text-xl font-bold text-white">₱<?= number_format($totalValue, 2) ?></div>
        </div>
        <div class="rounded-xl border border-white/[0.07] p-4 animate-fade-in-up stagger-3">
          <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Awaiting payment</div>
          <div class="mt-1 text-xl font-bold <?= $pendingPay ? 'text-amber-300' : 'text-white' ?>"><?= $pendingPay ?></div>
        </div>
      </div>

      <!-- Table -->
      <div class="rounded-xl border border-white/[0.07] overflow-hidden animate-fade-in-up">
        <div class="overflow-x-auto">
          <table class="w-full text-left">
            <thead class="bg-white/[0.03]">
              <tr>
                <th class="<?= $th ?>">Vehicle</th>
                <th class="<?= $th ?> text-right">Winning bid</th>
                <th class="<?= $th ?>">Payment status</th>
                <th class="<?= $th ?> text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/[0.05]">
              <?php foreach ($won as $car): [$cls, $txt, $needsPay] = $txPill($car['transaction_status'] ?? null); ?>
                <tr class="hover:bg-white/[0.03] transition">
                  <td class="<?= $td ?>">
                    <div class="flex items-center gap-3">
                      <?php if (!empty($car['primary_image'])): ?>
                        <img src="<?= $base ?>/<?= e($car['primary_image']) ?>" alt="" class="w-14 h-10 object-cover rounded-md shrink-0 bg-white/5">
                      <?php else: ?>
                        <div class="w-14 h-10 bg-white/5 rounded-md shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                      <?php endif; ?>
                      <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="font-medium text-zinc-100 hover:text-white transition truncate max-w-[260px]"><?= e($car['title']) ?></a>
                    </div>
                  </td>
                  <td class="<?= $td ?> text-right font-semibold text-zinc-100">₱<?= number_format((float)$car['bid_amount'], 2) ?></td>
                  <td class="<?= $td ?>"><span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold <?= $cls ?>"><?= $txt ?></span></td>
                  <td class="<?= $td ?> text-right">
                    <?php if ($needsPay && !empty($car['transaction_id'])): ?>
                      <a href="<?= $base ?>/transactions/view/<?= (int)$car['transaction_id'] ?>" class="rounded-md bg-brand/80 px-2.5 py-1 text-xs font-semibold text-white hover:bg-brand transition">Pay now</a>
                    <?php elseif (!empty($car['transaction_id'])): ?>
                      <a href="<?= $base ?>/transactions/view/<?= (int)$car['transaction_id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View transaction</a>
                    <?php else: ?>
                      <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="text-xs font-medium text-zinc-400 hover:text-white transition">View listing</a>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
