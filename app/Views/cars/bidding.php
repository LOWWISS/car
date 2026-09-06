<?php
/** Live bidding page: active auctions sorted by ending soon. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <?php if (empty($cars)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h3 class="empty-state-title">No active auctions right now</h3>
        <p class="empty-state-text">Check back later or browse all cars.</p>
        <a href="<?= $base ?>/cars" class="btn-primary">Browse all cars</a>
      </div>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
      <?php foreach ($cars as $i => $car): ?>
        <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
