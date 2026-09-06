<?php
/** Won Auctions page. Links each won auction to its transaction/payment flow. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');

$txBadge = function (?string $status): string {
    if (!$status) return '<span class="badge badge-neutral">No transaction</span>';
    return match ($status) {
        'payment_pending'    => '<span class="badge badge-warning">Payment Due</span>',
        'payment_confirmed'  => '<span class="badge badge-info">Payment Confirmed</span>',
        'ready_for_handover' => '<span class="badge badge-info">Ready for Handover</span>',
        'completed'          => '<span class="badge badge-success">Completed</span>',
        'cancelled'          => '<span class="badge badge-neutral">Cancelled</span>',
        default              => '<span class="badge badge-neutral">' . e(ucfirst($status)) . '</span>',
    };
};
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <?php if (empty($won)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h3 class="empty-state-title">No won auctions yet</h3>
        <p class="empty-state-text">Keep bidding to win your first auction!</p>
        <a href="<?= $base ?>/cars" class="btn-primary">Browse auctions</a>
      </div>
    </div>
  <?php else: ?>
    <div class="card overflow-hidden animate-fade-in-up">
      <table class="data-table">
        <thead>
          <tr>
            <th>Vehicle</th>
            <th class="text-right">Winning bid</th>
            <th>Payment status</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($won as $car): ?>
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <?php if (!empty($car['primary_image'])): ?>
                    <img src="<?= $base ?>/<?= e($car['primary_image']) ?>" alt="" class="w-14 h-10 object-cover rounded shrink-0">
                  <?php else: ?>
                    <div class="w-14 h-10 bg-slate-200 rounded shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                  <?php endif; ?>
                  <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="text-brand font-medium hover:underline"><?= e($car['title']) ?></a>
                </div>
              </td>
              <td class="text-right font-semibold text-slate-800">&#8369;<?= number_format((float)$car['bid_amount'], 2) ?></td>
              <td><?= $txBadge($car['transaction_status'] ?? null) ?></td>
              <td class="text-right">
                <?php if (!empty($car['transaction_id'])): ?>
                  <a href="<?= $base ?>/transactions/view/<?= (int)$car['transaction_id'] ?>" class="text-sm text-brand hover:underline">View transaction</a>
                <?php else: ?>
                  <a href="<?= $base ?>/cars/view/<?= (int)$car['id'] ?>" class="text-sm text-brand hover:underline">View listing</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
