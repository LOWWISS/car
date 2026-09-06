<?php
/** Car create/edit form. $car is null when creating. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$car = $car ?? null;
$images = $images ?? [];
$action = $car ? $base . '/cars/edit/' . (int)$car['id'] : $base . '/cars/create';
$bodyTypes = ['Sedan','SUV','Pickup','Hatchback','Coupe','Convertible','Van','Wagon'];
$conditions = ['New','Used','Certified'];
?>
<div class="max-w-3xl mx-auto px-4 py-8">

  <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="card p-6 space-y-5 animate-fade-in-up">
    <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">

    <div>
      <label class="form-label">Title</label>
      <input type="text" name="title" required class="form-input" value="<?= e($car['title'] ?? '') ?>">
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Make</label>
        <input type="text" name="make" required class="form-input" value="<?= e($car['make'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Model</label>
        <input type="text" name="model" required class="form-input" value="<?= e($car['model'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Year</label>
        <input type="number" name="year" required min="1900" max="<?= date('Y')+1 ?>" class="form-input" value="<?= e($car['year'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Mileage (km)</label>
        <input type="number" name="mileage" required min="0" class="form-input" value="<?= e($car['mileage'] ?? '0') ?>">
      </div>
      <div>
        <label class="form-label">Body type</label>
        <select name="body_type" class="form-input">
          <?php foreach ($bodyTypes as $b): ?>
            <option <?= ($car['body_type'] ?? '')===$b?'selected':'' ?>><?= e($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label">Condition</label>
        <select name="condition" class="form-input">
          <?php foreach ($conditions as $c): ?>
            <option <?= ($car['condition'] ?? '')===$c?'selected':'' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-input" value="<?= e($car['location'] ?? '') ?>">
    </div>

    <div>
      <label class="form-label">Description</label>
      <textarea name="description" rows="4" class="form-input"><?= e($car['description'] ?? '') ?></textarea>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="form-label">Starting price (&#8369;)</label>
        <input type="number" name="starting_price" step="0.01" required min="0" class="form-input" value="<?= e($car['starting_price'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Reserve price (&#8369;)</label>
        <input type="number" name="reserve_price" step="0.01" min="0" class="form-input" value="<?= e($car['reserve_price'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Buy Now price (&#8369;)</label>
        <input type="number" name="buy_now_price" step="0.01" min="0" class="form-input" value="<?= e($car['buy_now_price'] ?? '') ?>">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="form-label">Auction start</label>
        <input type="datetime-local" name="auction_start" required class="form-input" value="<?= e(($car['auction_start'] ?? null) ? date('Y-m-d\TH:i', strtotime($car['auction_start'])) : date('Y-m-d\TH:i')) ?>">
      </div>
      <div>
        <label class="form-label">Auction end</label>
        <input type="datetime-local" name="auction_end" required class="form-input" value="<?= e(($car['auction_end'] ?? null) ? date('Y-m-d\TH:i', strtotime($car['auction_end'])) : date('Y-m-d\TH:i', strtotime('+3 days'))) ?>">
      </div>
    </div>

    <div>
      <label class="form-label">Photos (jpg, png, webp — max 5MB each)</label>
      <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="block w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand file:text-white file:cursor-pointer">
      <p class="text-xs text-slate-400 mt-1">Server validates MIME type and extension; files are renamed and stored in a non-executable directory.</p>
    </div>

    <?php if (!empty($images)): ?>
      <div>
        <label class="form-label">Current photos</label>
        <p class="text-xs text-slate-400 mb-2">Hover over a photo and click the trash icon to remove it.</p>
        <div class="flex flex-wrap gap-3">
          <?php foreach ($images as $img): ?>
            <div class="relative group" data-image-wrapper="<?= (int)$img['id'] ?>">
              <img src="<?= $base ?>/<?= e($img['image_path']) ?>" alt="photo" class="w-24 h-24 object-cover rounded-lg border border-slate-200 transition group-hover:border-auction/50">

              <!-- Hover overlay with darken + icon hint -->
              <div class="absolute inset-0 bg-black/40 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </div>

              <!-- Delete button: linked to an external form via the form="" attribute
                   (HTML does not allow nested forms, so the real form is at the page bottom) -->
              <button type="submit" form="delete-image-form-<?= (int)$img['id'] ?>"
                      class="absolute -top-2 -right-2 bg-auction text-white rounded-full w-7 h-7 flex items-center justify-center shadow-md ring-2 ring-white hover:bg-auction-dark hover:scale-110 active:scale-95 transition-all"
                      title="Delete this image" aria-label="Delete this image">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>

              <?php if ($img['is_primary']): ?>
                <span class="absolute bottom-0 left-0 right-0 bg-brand text-white text-[10px] text-center py-0.5 rounded-b-lg font-medium">Primary</span>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-2">
      <button type="submit" class="btn-primary"><?= $car ? 'Update listing' : 'Submit listing' ?></button>
      <a href="<?= $car ? $base.'/cars/view/'.(int)$car['id'] : $base.'/dashboard' ?>" class="btn-outline">Cancel</a>
    </div>
  </form>

  <!-- Delete-image forms: placed OUTSIDE the main listing form because HTML
       does not allow <form> nesting. Each button above references its form via
       the form="" attribute. The JS intercepts these submits for AJAX delete. -->
  <?php if (!empty($images)): ?>
    <?php foreach ($images as $img): ?>
      <form id="delete-image-form-<?= (int)$img['id'] ?>" method="post" action="<?= $base ?>/cars/delete-image/<?= (int)$img['id'] ?>" data-delete-image-form class="hidden">
        <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
      </form>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
