<?php
/** Car catalog — dark filter bar + chips + grid, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$f = $filters;
$bodyTypes = ['Sedan','SUV','Pickup','Hatchback','Coupe','Convertible','Van','Wagon'];
$sorts = ['newest'=>'Newest','oldest'=>'Oldest','price_low'=>'Price ↑','price_high'=>'Price ↓','ending'=>'Ending soon','year_desc'=>'Year ↓'];
$inputCls = 'dark-field w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:ring-2 focus:ring-brand/60 focus:border-brand/60 outline-none transition';
$labelCls = 'block text-[11px] font-semibold uppercase tracking-wider text-zinc-500 mb-1.5';

// Active-filter chips (each links back with that param removed)
$chipLabels = ['q'=>'Search','make'=>'Make','body_type'=>'Body','min_price'=>'Min ₱','max_price'=>'Max ₱','location'=>'Location','sort'=>'Sort'];
$chips = [];
foreach ($chipLabels as $key => $label) {
    if (!empty($f[$key])) {
        $rest = $f; unset($rest[$key]);
        $chips[] = [$label . ': ' . $f[$key], '/cars?' . http_build_query(array_filter($rest, fn($v)=> $v !== '' && $v !== null))];
    }
}
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-5 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">Browse Cars</h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= (int)$total ?> listing<?= $total===1?'':'s' ?> found</p>
      </div>
      <?php if ($chips): ?>
        <div class="flex flex-wrap items-center gap-2">
          <?php foreach ($chips as [$text, $href]): ?>
            <a href="<?= $base ?><?= e($href) ?>" class="group inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-zinc-300 hover:border-red-400/40 hover:text-red-300 transition">
              <?= e($text) ?>
              <svg class="w-3 h-3 opacity-60 group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
          <?php endforeach; ?>
          <a href="<?= $base ?>/cars" class="text-xs text-zinc-500 hover:text-white transition underline underline-offset-2">Clear all</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Filter bar -->
    <form method="get" action="<?= $base ?>/cars" data-autosubmit class="rounded-xl border border-white/[0.07] bg-white/[0.02] p-4 mb-6 animate-fade-in-up">
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="col-span-2 sm:col-span-1 lg:col-span-1">
          <label class="<?= $labelCls ?>">Search</label>
          <div class="relative">
            <svg class="w-4 h-4 text-zinc-500 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="Title, make, model..." class="<?= $inputCls ?> pl-9">
          </div>
        </div>
        <div>
          <label class="<?= $labelCls ?>">Make</label>
          <select name="make" class="<?= $inputCls ?>">
            <option value="">Any</option>
            <?php foreach ($makes as $m): ?>
              <option value="<?= e($m) ?>" <?= $f['make']===$m?'selected':'' ?>><?= e($m) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="<?= $labelCls ?>">Body type</label>
          <select name="body_type" class="<?= $inputCls ?>">
            <option value="">Any</option>
            <?php foreach ($bodyTypes as $b): ?>
              <option value="<?= e($b) ?>" <?= $f['body_type']===$b?'selected':'' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="<?= $labelCls ?>">Price range</label>
          <div class="flex items-center gap-1.5">
            <input type="number" name="min_price" value="<?= e($f['min_price']) ?>" class="<?= $inputCls ?> min-w-0" min="0" placeholder="Min">
            <span class="text-zinc-600">–</span>
            <input type="number" name="max_price" value="<?= e($f['max_price']) ?>" class="<?= $inputCls ?> min-w-0" placeholder="Max">
          </div>
        </div>
        <div>
          <label class="<?= $labelCls ?>">Location</label>
          <input type="text" name="location" value="<?= e($f['location']) ?>" class="<?= $inputCls ?>" placeholder="City or region">
        </div>
        <div>
          <label class="<?= $labelCls ?>">Sort by</label>
          <select name="sort" class="<?= $inputCls ?>">
            <?php foreach ($sorts as $k=>$label): ?>
              <option value="<?= e($k) ?>" <?= $f['sort']===$k?'selected':'' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="flex items-center gap-2 mt-4 pt-3 border-t border-white/[0.07]">
        <a href="<?= $base ?>/cars" class="rounded-lg border border-white/10 px-4 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">Reset</a>
        <span class="text-xs text-zinc-600">Filters apply automatically</span>
      </div>
    </form>

    <!-- Results -->
    <?php if (empty($cars)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200">No cars match your filters</h3>
        <p class="mt-1 text-sm text-zinc-500">Try adjusting your search or clearing filters.</p>
        <a href="<?= $base ?>/cars" class="inline-block mt-4 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-dark transition">Clear all filters</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($cars as $i => $car): ?>
          <?php $stagger = 'stagger-' . (min($i, 7) + 1); $cardDark = true; include __DIR__ . '/../partials/car_card.php'; ?>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav class="flex justify-center mt-8 gap-1.5 animate-fade-in">
          <?php
            $build = function($p) use ($f) {
              $q = array_filter($f, fn($v)=> $v !== '' && $v !== null);
              $q['page'] = $p;
              return http_build_query($q);
            };
            $pgCls = 'min-w-[2.25rem] text-center rounded-lg border px-2.5 py-1.5 text-sm transition ';
          ?>
          <?php for ($p=1; $p<=$totalPages; $p++): ?>
            <a href="<?= $base ?>/cars?<?= e($build($p)) ?>"
               class="<?= $pgCls ?><?= $p===$page ? 'bg-brand border-brand text-white font-semibold' : 'border-white/10 text-zinc-300 hover:bg-white/10' ?>">
               <?= (int)$p ?>
            </a>
          <?php endfor; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
