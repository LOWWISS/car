<?php
/** Transaction detail: vehicle, parties, amount, status timeline, actions. */
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

$statusBadge = match ($t['status']) {
    'payment_pending'    => '<span class="badge badge-warning">Payment Pending</span>',
    'payment_confirmed'  => '<span class="badge badge-info">Payment Confirmed</span>',
    'ready_for_handover' => '<span class="badge badge-info">Ready for Handover</span>',
    'completed'          => '<span class="badge badge-success">Completed</span>',
    'cancelled'          => '<span class="badge badge-neutral">Cancelled</span>',
    default              => '<span class="badge badge-neutral">' . e(ucfirst($t['status'])) . '</span>',
};
$img = $t['primary_image'] ?? null;
$imgSrc = $img ? $base . '/' . $img : $base . '/assets/images/placeholder.svg';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <a href="<?= $base ?>/transactions" class="text-sm text-brand hover:underline mb-4 inline-flex items-center gap-1">&larr; Back to transactions</a>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left: vehicle + parties -->
    <div class="lg:col-span-2 space-y-6">
      <div class="card overflow-hidden animate-fade-in-up">
        <div class="bg-slate-100">
          <img src="<?= e($imgSrc) ?>" alt="<?= e($t['car_title']) ?>" class="w-full h-56 object-cover">
        </div>
        <div class="p-5">
          <h1 class="text-xl font-bold text-slate-900"><?= e($t['car_title']) ?></h1>
          <p class="text-sm text-slate-500"><?= e($t['car_make'] ?? '') ?> <?= e($t['car_model'] ?? '') ?> &middot; <?= e($t['car_year'] ?? '') ?></p>
          <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
            <div>
              <div class="text-xs uppercase text-slate-400">Final amount</div>
              <div class="text-2xl font-bold text-auction-dark">&#8369;<?= number_format((float)$t['final_amount'], 2) ?></div>
            </div>
            <div>
              <div class="text-xs uppercase text-slate-400">Status</div>
              <div class="mt-1"><?= $statusBadge ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="card p-5 animate-fade-in-up stagger-2">
        <h2 class="font-semibold text-lg mb-4">Parties</h2>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
          <div>
            <dt class="text-xs uppercase text-slate-400">Seller</dt>
            <dd class="font-medium text-slate-800"><?= e($t['seller_name']) ?></dd>
            <dd class="text-xs text-slate-500"><?= e($t['seller_email']) ?></dd>
          </div>
          <div>
            <dt class="text-xs uppercase text-slate-400">Buyer (winner)</dt>
            <dd class="font-medium text-slate-800"><?= e($t['winner_name']) ?></dd>
            <dd class="text-xs text-slate-500"><?= e($t['winner_email']) ?></dd>
          </div>
        </dl>
        <div class="mt-4 pt-4 border-t border-slate-100 text-sm">
          <a href="<?= $base ?>/cars/view/<?= (int)$t['car_id'] ?>" class="text-brand hover:underline">View vehicle listing &rarr;</a>
        </div>
      </div>
    </div>

    <!-- Right: status timeline + actions -->
    <div class="space-y-6">
      <div class="card p-5 animate-fade-in-up stagger-1">
        <h2 class="font-semibold text-lg mb-4">Transaction Timeline</h2>
        <?php if ($isCancelled): ?>
          <div class="p-3 rounded-lg bg-slate-100 text-slate-600 text-sm">
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
                    <?= $done ? 'bg-green-500 text-white' : ($active ? 'bg-brand text-white animate-pulse-glow' : 'bg-slate-200 text-slate-400') ?>">
                    <?= $done ? '&#10003;' : ($i + 1) ?>
                  </span>
                  <?php if ($i < count($stageOrder) - 1): ?>
                    <span class="w-0.5 flex-1 <?= $done ? 'bg-green-400' : 'bg-slate-200' ?> my-1"></span>
                  <?php endif; ?>
                </div>
                <div class="flex-1 pb-2">
                  <div class="font-medium text-sm <?= $active || $done ? 'text-slate-800' : 'text-slate-400' ?>"><?= e($label) ?></div>
                  <div class="text-xs text-slate-500"><?= e($desc) ?></div>
                  <?php if ($ts): ?><div class="text-[11px] text-slate-400 mt-0.5"><?= e($ts) ?></div><?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        <?php endif; ?>
      </div>

      <!-- Actions -->
      <div class="card p-5 animate-fade-in-up stagger-3 space-y-3">
        <h2 class="font-semibold text-lg">Actions</h2>

        <?php if ($isWinner && $t['status'] === 'payment_pending'): ?>
          <form method="post" action="<?= $base ?>/transactions/mark-paid/<?= (int)$t['id'] ?>">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn-primary w-full text-sm">I've Paid (notify seller)</button>
          </form>
          <p class="text-xs text-slate-500">Mark your payment as sent so the seller can confirm receipt and proceed with handover.</p>
        <?php endif; ?>

        <?php if ($canAdvance && $t['status'] === 'payment_pending'): ?>
          <form method="post" action="<?= $base ?>/transactions/confirm-payment/<?= (int)$t['id'] ?>">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn-success w-full text-sm">Confirm Payment Received</button>
          </form>
        <?php endif; ?>

        <?php if ($canAdvance && $t['status'] === 'payment_confirmed'): ?>
          <form method="post" action="<?= $base ?>/transactions/ready-handover/<?= (int)$t['id'] ?>">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn-primary w-full text-sm">Mark Ready for Handover</button>
          </form>
        <?php endif; ?>

        <?php if ($canAdvance && $t['status'] === 'ready_for_handover'): ?>
          <form method="post" action="<?= $base ?>/transactions/complete/<?= (int)$t['id'] ?>"
                onsubmit="return confirm('Record vehicle handover and complete this transaction?');">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn-success w-full text-sm">Record Handover &amp; Complete</button>
          </form>
        <?php endif; ?>

        <?php if ($t['status'] === 'completed'): ?>
          <div class="p-3 rounded-lg bg-green-50 border border-green-100 text-green-800 text-sm">
            This transaction is complete. The vehicle has been handed over and marked sold.
          </div>
        <?php endif; ?>

        <?php if ($isAdmin && !$isCancelled && $t['status'] !== 'completed'): ?>
          <form method="post" action="<?= $base ?>/transactions/cancel/<?= (int)$t['id'] ?>"
                onsubmit="return confirm('Cancel this transaction? The listing will revert to closed.');">
            <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
            <button type="submit" class="btn-danger w-full text-sm">Cancel Transaction</button>
          </form>
        <?php endif; ?>

        <?php if ($isWinner && $t['status'] !== 'payment_pending' && !$isCancelled): ?>
          <p class="text-xs text-slate-500">No action required from you at this stage. The seller will progress the transaction.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
