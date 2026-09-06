<?php
/** My Bids page. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <?php if (empty($bids)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
        </div>
        <h3 class="empty-state-title">You haven't placed any bids yet</h3>
        <p class="empty-state-text">Browse available auctions and place your first bid.</p>
        <a href="<?= $base ?>/cars" class="btn-primary">Browse auctions</a>
      </div>
    </div>
  <?php else: ?>
    <div class="card overflow-hidden animate-fade-in-up">
      <table class="data-table">
        <thead>
          <tr>
            <th>Listing</th>
            <th class="text-right">Your bid</th>
            <th>Status</th>
            <th>Ends</th>
            <th class="text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bids as $b): ?>
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <?php if (!empty($b['primary_image'])): ?>
                    <img src="<?= $base ?>/<?= e($b['primary_image']) ?>" alt="" class="w-14 h-10 object-cover rounded shrink-0">
                  <?php else: ?>
                    <div class="w-14 h-10 bg-slate-200 rounded shrink-0 flex items-center justify-center"><svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                  <?php endif; ?>
                  <a href="<?= $base ?>/cars/view/<?= (int)$b['car_id'] ?>" class="text-brand font-medium hover:underline"><?= e($b['car_title']) ?></a>
                </div>
              </td>
              <td class="text-right font-semibold text-slate-800">&#8369;<?= number_format((float)$b['bid_amount'], 2) ?></td>
              <td>
                <?php
                $statusClass = match($b['car_status']) {
                    'active' => 'badge-success',
                    'pending' => 'badge-warning',
                    'sold' => 'badge-info',
                    'closed' => 'badge-neutral',
                    default => 'badge-neutral',
                };
                ?>
                <span class="badge <?= $statusClass ?>"><?= e(ucfirst($b['car_status'])) ?></span>
              </td>
              <td class="text-slate-500"><?= e($b['auction_end']) ?></td>
              <td class="text-right">
                <a href="<?= $base ?>/cars/view/<?= (int)$b['car_id'] ?>" class="text-sm text-brand hover:underline">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
