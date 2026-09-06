<?php
/** Featured cars page: latest active listings. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <?php if (empty($cars)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/></svg>
        </div>
        <h3 class="empty-state-title">No featured cars yet</h3>
        <p class="empty-state-text">Check back soon for new listings!</p>
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
