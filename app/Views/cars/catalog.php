<?php
/** Car catalog with filters + pagination. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$f = $filters;
$bodyTypes = ['Sedan','SUV','Pickup','Hatchback','Coupe','Convertible','Van','Wagon'];
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

  <!-- Search bar -->
  <form method="get" action="<?= $base ?>/cars" class="mb-6 animate-fade-in-up">
    <div class="flex gap-2">
      <div class="relative flex-1">
        <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="Search by title, make, or model..."
               class="w-full rounded-lg border border-slate-300 pl-10 pr-4 py-3 text-sm focus:ring-2 focus:ring-brand focus:border-brand outline-none transition">
      </div>
      <button type="submit" class="btn-primary px-6" aria-label="Search">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        Search
      </button>
      <?php if (!empty($f['q'])): ?>
        <a href="<?= $base ?>/cars" class="btn-outline">Clear</a>
      <?php endif; ?>
    </div>
  </form>

  <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Filters -->
    <aside class="lg:col-span-1">
      <form method="get" action="<?= $base ?>/cars" class="card p-4 space-y-4 sticky top-20 animate-fade-in-up stagger-1">
        <div class="flex items-center gap-2 pb-2 border-b border-slate-100">
          <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
          <h3 class="font-semibold text-sm text-slate-700">Filters</h3>
        </div>
        <div>
          <label class="form-label">Make</label>
          <select name="make" class="form-input">
            <option value="">Any</option>
            <?php foreach ($makes as $m): ?>
              <option value="<?= e($m) ?>" <?= $f['make']===$m?'selected':'' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="form-label">Body type</label>
          <select name="body_type" class="form-input">
            <option value="">Any</option>
            <?php foreach ($bodyTypes as $b): ?>
              <option value="<?= e($b) ?>" <?= $f['body_type']===$b?'selected':'' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="form-label">Min price</label>
            <input type="number" name="min_price" value="<?= e($f['min_price']) ?>" class="form-input" min="0" placeholder="0">
          </div>
          <div>
            <label class="form-label">Max price</label>
            <input type="number" name="max_price" value="<?= e($f['max_price']) ?>" class="form-input" min="0" placeholder="Any">
          </div>
        </div>
        <div>
          <label class="form-label">Location</label>
          <input type="text" name="location" value="<?= e($f['location']) ?>" class="form-input" placeholder="City or region">
        </div>
        <div>
          <label class="form-label">Sort by</label>
          <select name="sort" class="form-input">
            <?php
              $sorts = ['newest'=>'Newest','oldest'=>'Oldest','price_low'=>'Price: Low to High','price_high'=>'Price: High to Low','ending'=>'Ending Soon','year_desc'=>'Year: Newest'];
              foreach ($sorts as $k=>$label): ?>
              <option value="<?= e($k) ?>" <?= $f['sort']===$k?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="flex gap-2 pt-2">
          <button type="submit" class="btn-primary flex-1">Apply</button>
          <a href="<?= $base ?>/cars" class="btn-outline">Reset</a>
        </div>
      </form>
    </aside>

    <!-- Results -->
    <section class="lg:col-span-3">
      <p class="text-sm text-slate-500 mb-4 animate-fade-in"><?= (int)$total ?> listing<?= $total===1?'':'s' ?> found</p>

      <?php if (empty($cars)): ?>
        <div class="card animate-fade-in-up">
          <div class="empty-state">
            <div class="empty-state-icon">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="empty-state-title">No cars match your filters</h3>
            <p class="empty-state-text">Try adjusting your search or clearing filters.</p>
            <a href="<?= $base ?>/cars" class="btn-primary">Clear all filters</a>
          </div>
        </div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
          <?php foreach ($cars as $i => $car): ?>
            <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
          <nav class="flex justify-center mt-8 gap-1 animate-fade-in">
            <?php
              $query = $f; unset($query['q']);
              $build = function($p) use ($f) {
                $q = array_filter($f, fn($v)=> $v !== '' && $v !== null);
                $q['page'] = $p;
                return http_build_query($q);
              };
            ?>
            <?php for ($p=1; $p<=$totalPages; $p++): ?>
              <a href="<?= $base ?>/cars?<?= e($build($p)) ?>"
                 class="pagination-link <?= $p===$page?'active':'' ?>">
                 <?= (int)$p ?>
              </a>
            <?php endfor; ?>
          </nav>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
</div>
