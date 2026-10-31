<?php
/** Transaction detail — dark, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$t = $tx;
$stages = [
    'payment_pending'    => ['Payment Pending',    'The winner must complete payment.'],
    'payment_confirmed'  => ['Payment Confirmed',  'Payment has been received and verified.'],
    'ready_for_handover' => ['Ready for Handover', 'Vehicle is ready to be handed over to the buyer.'],
    'completed'          => ['Completed',          'Vehicle handed over. Transaction complete.'],
];
$stageOrder = ['payment_pending', 'payment_confirmed', 'ready_for_handover', 'completed'];
$currentIdx = array_search($t['status'], $stageOrder, true);
$isCancelled = $t['status'] === 'cancelled';

$statusPill = match ($t['status']) {
    'payment_pending'    => 'bg-amber-400/10 text-amber-300',
    'payment_confirmed'  => 'bg-blue-400/10 text-blue-300',
    'ready_for_handover' => 'bg-indigo-400/10 text-indigo-300',
    'completed'          => 'bg-emerald-400/10 text-emerald-300',
    'cancelled'          => 'bg-red-400/10 text-red-300',
    default              => 'bg-white/10 text-zinc-400',
};
$statusLabel = match ($t['status']) {
    'payment_pending'    => 'Payment Pending',
    'payment_confirmed'  => 'Payment Confirmed',
    'ready_for_handover' => 'Ready for Handover',
    'completed'          => 'Completed',
    'cancelled'          => 'Cancelled',
    default              => ucfirst($t['status']),
};
$img = $t['primary_image'] ?? null;
$imgSrc = $img ? $base . '/' . $img : $base . '/assets/images/placeholder.svg';
$card = 'rounded-xl border border-white/[0.07] bg-white/[0.02]';
$btnPri = 'w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition';
$btnOk  = 'w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-500 transition';
$btnBad = 'w-full rounded-lg border border-red-400/30 px-4 py-2.5 text-sm font-semibold text-red-300 hover:bg-red-500/10 transition';
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <a href="<?= $base ?>/transactions" class="inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-white transition mb-5 animate-fade-in">&larr; Back to transactions</a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: vehicle + parties -->
      <div class="lg:col-span-2 space-y-6">
        <div class="<?= $card ?> overflow-hidden animate-fade-in-up">
          <div class="bg-white/5">
            <img src="<?= e($imgSrc) ?>" alt="<?= e($t['car_title']) ?>" class="w-full h-56 object-cover">
          </div>
          <div class="p-5">
            <h1 class="text-xl font-semibold text-white"><?= e($t['car_title']) ?></h1>
            <p class="text-sm text-zinc-500"><?= e($t['car_make'] ?? '') ?> <?= e($t['car_model'] ?? '') ?> &middot; <?= e($t['car_year'] ?? '') ?></p>
            <div class="mt-4 grid grid-cols-2 gap-4">
              <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Final amount</div>
                <div class="text-2xl font-bold text-white">₱<?= number_format((float)$t['final_amount'], 2) ?></div>
              </div>
              <div>
                <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Status</div>
                <div class="mt-1"><span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold <?= $statusPill ?>"><?= $statusLabel ?></span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="<?= $card ?> p-5 animate-fade-in-up stagger-2">
          <h2 class="text-base font-semibold text-white mb-4">Parties</h2>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
              <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Seller</dt>
              <dd class="font-medium text-zinc-100 mt-0.5"><?= e($t['seller_name']) ?></dd>
              <dd class="text-xs text-zinc-500"><?= e($t['seller_email']) ?></dd>
            </div>
            <div>
              <dt class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Buyer (winner)</dt>
              <dd class="font-medium text-zinc-100 mt-0.5"><?= e($t['winner_name']) ?></dd>
              <dd class="text-xs text-zinc-500"><?= e($t['winner_email']) ?></dd>
            </div>
          </dl>
          <div class="mt-4 pt-4 border-t border-white/[0.07] text-sm">
            <a href="<?= $base ?>/cars/view/<?= (int)$t['car_id'] ?>" class="text-zinc-400 hover:text-white transition">View vehicle listing &rarr;</a>
          </div>
        </div>
      </div>

      <!-- Right: status timeline + actions -->
      <div class="space-y-6">
        <div class="<?= $card ?> p-5 animate-fade-in-up stagger-1">
          <h2 class="text-base font-semibold text-white mb-4">Transaction Timeline</h2>
          <?php if ($isCancelled): ?>
            <div class="p-3 rounded-lg bg-red-400/10 border border-red-400/20 text-red-300 text-sm">
              This transaction was cancelled<?= !empty($t['cancelled_at']) ? ' on ' . e($t['cancelled_at']) : '' ?>. The listing has been reverted to closed.
            </div>
          <?php else: ?>
            <ol class="space-y-4">
              <?php foreach ($stageOrder as $i => $stage):
                $done = $i < $currentIdx || $t['status'] === 'completed';
                $active = $i === $currentIdx && $t['status'] !== 'completed';
                [$label, $desc] = $stages[$stage];
                $ts = match ($stage) {
                    'payment_pending'   => $t['created_at'],
                    'payment_confirmed' => $t['paid_at'],
                    'completed'         => $t['completed_at'],
                    default             => null,
                };
              ?>
                <li class="flex gap-3">
                  <div class="flex flex-col items-center">
                    <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                      <?= $done ? 'bg-emerald-500 text-white' : ($active ? 'bg-brand text-white animate-pulse-glow' : 'bg-white/10 text-zinc-500') ?>">
                      <?= $done ? '&#10003;' : ($i + 1) ?>
                    </span>
                    <?php if ($i < count($stageOrder) - 1): ?>
                      <span class="w-0.5 flex-1 <?= $done ? 'bg-emerald-400/60' : 'bg-white/10' ?> my-1"></span>
                    <?php endif; ?>
                  </div>
                  <div class="flex-1 pb-2">
                    <div class="font-medium text-sm <?= $active || $done ? 'text-zinc-100' : 'text-zinc-500' ?>"><?= e($label) ?></div>
                    <div class="text-xs text-zinc-500"><?= e($desc) ?></div>
                    <?php if ($ts): ?><div class="text-[11px] text-zinc-600 mt-0.5"><?= e($ts) ?></div><?php endif; ?>
                  </div>
                </li>
              <?php endforeach; ?>
            </ol>
          <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="<?= $card ?> p-5 animate-fade-in-up stagger-3 space-y-3">
          <h2 class="text-base font-semibold text-white">Actions</h2>

          <?php if ($isWinner && $t['status'] === 'payment_pending'): ?>
            <form method="post" action="<?= $base ?>/transactions/mark-paid/<?= (int)$t['id'] ?>">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="<?= $btnPri ?>">I've Paid (notify seller)</button>
            </form>
            <p class="text-xs text-zinc-500">Mark your payment as sent so the seller can confirm receipt and proceed with handover.</p>
          <?php endif; ?>

          <?php if ($canAdvance && $t['status'] === 'payment_pending'): ?>
            <form method="post" action="<?= $base ?>/transactions/confirm-payment/<?= (int)$t['id'] ?>">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="<?= $btnOk ?>">Confirm Payment Received</button>
            </form>
          <?php endif; ?>

          <?php if ($canAdvance && $t['status'] === 'payment_confirmed'): ?>
            <form method="post" action="<?= $base ?>/transactions/ready-handover/<?= (int)$t['id'] ?>">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="<?= $btnPri ?>">Mark Ready for Handover</button>
            </form>
          <?php endif; ?>

          <?php if ($canAdvance && $t['status'] === 'ready_for_handover'): ?>
            <form method="post" action="<?= $base ?>/transactions/complete/<?= (int)$t['id'] ?>"
                  data-confirm="Record vehicle handover and complete this transaction?" data-confirm-text="Complete">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="<?= $btnOk ?>">Record Handover &amp; Complete</button>
            </form>
          <?php endif; ?>

          <?php if ($t['status'] === 'completed'): ?>
            <div class="p-3 rounded-lg bg-emerald-400/10 border border-emerald-400/20 text-emerald-300 text-sm">
              This transaction is complete. The vehicle has been handed over and marked sold.
            </div>
          <?php endif; ?>

          <?php if ($isAdmin && !$isCancelled && $t['status'] !== 'completed'): ?>
            <form method="post" action="<?= $base ?>/transactions/cancel/<?= (int)$t['id'] ?>"
                  data-confirm="Cancel this transaction? The listing will revert to closed." data-confirm-text="Cancel">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="<?= $btnBad ?>">Cancel Transaction</button>
            </form>
          <?php endif; ?>

          <?php if ($isWinner && $t['status'] !== 'payment_pending' && !$isCancelled): ?>
            <p class="text-xs text-zinc-500">No action required from you at this stage. The seller will progress the transaction.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
