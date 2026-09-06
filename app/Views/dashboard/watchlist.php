<?php /** Watchlist page. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <?php if (empty($items)): ?>
    <div class="card animate-fade-in-up">
      <div class="empty-state">
        <div class="empty-state-icon">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        </div>
        <h3 class="empty-state-title">Your watchlist is empty</h3>
        <p class="empty-state-text">Tap the star on any listing to track it here.</p>
        <a href="<?= $base ?>/cars" class="btn-primary">Browse cars</a>
      </div>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($items as $i => $car): ?>
        <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
