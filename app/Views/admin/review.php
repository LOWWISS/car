<?php
/**
 * Admin listing review: shows the full car content (gallery, specs, description)
 * BEFORE the admin decides to approve or reject. Replaces the old one-click
 * approve from the dashboard so the admin can verify there is no problem first.
 */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$car = $car; $images = $images;
$primary = $images[0] ?? null;
$primarySrc = $primary ? $base . '/' . $primary['image_path'] : $base . '/assets/images/placeholder.svg';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <a href="<?= $base ?>/admin" class="text-sm text-brand hover:underline mb-4 inline-flex items-center gap-1">&larr; Back to admin dashboard</a>

  <!-- Pending banner -->
  <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-center gap-3 animate-fade-in-up">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <div class="flex-1">
      <div class="font-semibold">This listing is awaiting your review.</div>
      <div class="text-sm">Inspect the content below carefully. If everything looks correct, approve it to make it live. Otherwise, reject it and the seller will be asked to resubmit.</div>
    </div>
    <span class="badge badge-warning shrink-0">PENDING</span>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Gallery + specs (2 cols) -->
    <div class="lg:col-span-2 space-y-6">
      <div class="card overflow-hidden animate-fade-in-up">
        <div class="relative bg-slate-100">
          <img id="mainImage" src="<?= e($primarySrc) ?>" alt="<?= e($car['title']) ?>" class="w-full h-96 object-cover">
        </div>
        <?php if (count($images) > 1): ?>
          <div class="flex gap-2 p-3 overflow-x-auto">
            <?php foreach ($images as $img): ?>
              <img src="<?= $base ?>/<?= e($img['image_path']) ?>" alt="thumbnail"
                   class="w-20 h-20 object-cover rounded-lg cursor-pointer border-2 border-transparent hover:border-brand transition"
                   onclick="document.getElementById('mainImage').src=this.src">
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if (empty($images)): ?>
          <div class="p-3 text-sm text-slate-400 text-center">No images were uploaded for this listing.</div>
        <?php endif; ?>
      </div>

      <div class="card p-6 animate-fade-in-up stagger-2">
        <h2 class="font-semibold text-lg mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Specifications
        </h2>
        <dl class="grid grid-cols-2 sm:grid-cols-3 gap-y-4 text-sm">
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Make</dt><dd class="font-medium text-slate-800"><?= e($car['make']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Model</dt><dd class="font-medium text-slate-800"><?= e($car['model']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Year</dt><dd class="font-medium text-slate-800"><?= e($car['year']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Mileage</dt><dd class="font-medium text-slate-800"><?= number_format((int)$car['mileage']) ?> km</dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Body type</dt><dd class="font-medium text-slate-800"><?= e($car['body_type']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Condition</dt><dd class="font-medium text-slate-800"><?= e($car['condition']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Location</dt><dd class="font-medium text-slate-800"><?= e($car['location']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Auction start</dt><dd class="font-medium text-slate-800"><?= e($car['auction_start']) ?></dd></div>
          <div><dt class="text-slate-400 text-xs uppercase mb-0.5">Auction end</dt><dd class="font-medium text-slate-800"><?= e($car['auction_end']) ?></dd></div>
        </dl>
        <?php if (!empty($car['description'])): ?>
          <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="font-semibold mb-2">Description</h3>
            <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed"><?= e($car['description']) ?></p>
          </div>
        <?php else: ?>
          <div class="mt-4 pt-4 border-t border-slate-100">
            <h3 class="font-semibold mb-2">Description</h3>
            <p class="text-sm text-slate-400 italic">No description provided.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Decision panel (1 col) -->
    <div class="space-y-6">
      <div class="card p-6 animate-fade-in-up stagger-1">
        <h1 class="text-xl font-bold text-slate-900"><?= e($car['title']) ?></h1>
        <p class="text-sm text-slate-500 mt-1 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
          Submitted by <?= e($car['seller_name']) ?>
        </p>

        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div class="p-3 rounded-lg bg-slate-50">
            <div class="text-xs uppercase text-slate-500">Starting price</div>
            <div class="font-bold text-slate-900 text-lg">&#8369;<?= number_format((float)$car['starting_price'], 2) ?></div>
          </div>
          <div class="p-3 rounded-lg bg-slate-50">
            <div class="text-xs uppercase text-slate-500">Reserve price</div>
            <div class="font-bold text-slate-900 text-lg"><?= $car['reserve_price'] !== null ? '&#8369;' . number_format((float)$car['reserve_price'], 2) : '&mdash;' ?></div>
          </div>
          <div class="p-3 rounded-lg bg-slate-50">
            <div class="text-xs uppercase text-slate-500">Buy Now price</div>
            <div class="font-bold text-slate-900 text-lg"><?= $car['buy_now_price'] !== null ? '&#8369;' . number_format((float)$car['buy_now_price'], 2) : '&mdash;' ?></div>
          </div>
          <div class="p-3 rounded-lg bg-slate-50">
            <div class="text-xs uppercase text-slate-500">Submitted</div>
            <div class="font-medium text-slate-700 text-sm"><?= e($car['created_at']) ?></div>
          </div>
        </div>

        <!-- Decision actions -->
        <div class="mt-6 pt-4 border-t border-slate-100">
          <h3 class="font-semibold text-slate-800 mb-3">Decision</h3>
          <div class="space-y-3">
            <form method="post" action="<?= $base ?>/admin/approve/<?= (int)$car['id'] ?>"
                  onsubmit="return confirm('Approve this listing and make it live in the catalog?');">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="btn-success w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                Approve &amp; Publish
              </button>
            </form>
            <form method="post" action="<?= $base ?>/admin/reject/<?= (int)$car['id'] ?>"
                  onsubmit="return confirm('Reject this listing? The seller will be notified to review and resubmit.');">
              <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
              <button type="submit" class="btn-danger w-full flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Reject
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="card p-5 animate-fade-in-up stagger-2 text-sm text-slate-600">
        <h3 class="font-semibold text-slate-800 mb-2 flex items-center gap-2">
          <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Review checklist
        </h3>
        <ul class="space-y-1.5 list-disc pl-5">
          <li>Images are real photos of the actual car (not stock/placeholder).</li>
          <li>Title, make, model and year match the photos.</li>
          <li>Description is accurate and free of prohibited content.</li>
          <li>Pricing and auction times are reasonable.</li>
          <li>No contact info or off-platform payment links in the description.</li>
        </ul>
      </div>
    </div>
  </div>
</div>
