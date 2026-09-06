<?php
/** Home page: hero + stats + live bidding + features. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
?>

<!-- Hero Section -->
<section class="relative bg-slate-900 overflow-hidden">
  <div class="absolute inset-0">
    <img src="<?= $base ?>/assets/images/hero-car.jpg" alt="Hero Car" class="w-full h-full object-cover opacity-40" onerror="this.style.display='none'">
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900 via-slate-900/80 to-transparent"></div>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      <!-- Left: heading + CTAs -->
      <div class="max-w-2xl animate-fade-in-up">
        <span class="inline-block bg-blue-600/20 text-blue-300 text-sm font-semibold px-3 py-1 rounded-full mb-4 border border-blue-500/30">Featured: Lamborghini</span>
        <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight">Find Your Dream Car Today</h1>
        <p class="mt-6 text-lg text-slate-300">Join thousands of bidders and sellers in the most trusted car auction platform. Place real-time bids, watch listings, and win your next vehicle.</p>
        <div class="mt-8 flex flex-wrap gap-4">
          <a href="<?= $base ?>/cars" class="bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg">Browse Auctions</a>
          <?php if (Auth::guest()): ?>
            <a href="<?= $base ?>/register" class="border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 transition">Start Selling</a>
          <?php else: ?>
            <a href="<?= $base ?>/cars/create" class="border-2 border-white text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 transition">List a Car</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: interactive Lamborghini viewer -->
      <div class="animate-fade-in-up stagger-2">
        <div class="bg-slate-800/60 backdrop-blur-sm rounded-2xl shadow-2xl overflow-hidden">
          <!-- Viewer stage -->
          <div id="lamboViewer" class="relative aspect-[4/3] bg-slate-900 overflow-hidden cursor-zoom-in select-none">
            <img id="lamboImg" src="<?= $base ?>/assets/images/lambo/exterior.jpg.png" alt="Lamborghini Exterior" class="w-full h-full object-contain transition-transform duration-300 ease-out" draggable="false">
            <!-- Zoom hint -->
            <div id="lamboHint" class="absolute bottom-3 left-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full pointer-events-none transition-opacity duration-300">
              Click to zoom &middot; drag to pan
            </div>
            <!-- Zoom badge -->
            <div id="lamboZoomBadge" class="absolute top-3 left-3 bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded opacity-0 transition-opacity duration-200">2x</div>
          </div>
          <!-- Tab buttons -->
          <div class="flex">
            <button data-lambo-tab="exterior" data-lambo-src="<?= $base ?>/assets/images/lambo/exterior.jpg.png" class="lambo-tab flex-1 py-3 text-sm font-semibold text-white bg-blue-600 transition-colors">Exterior</button>
            <button data-lambo-tab="interior" data-lambo-src="<?= $base ?>/assets/images/lambo/interior.jpg.png" class="lambo-tab flex-1 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-700/50 transition-colors">Interior</button>
            <button data-lambo-tab="back" data-lambo-src="<?= $base ?>/assets/images/lambo/back.jpg.png" class="lambo-tab flex-1 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-700/50 transition-colors border-l border-slate-700/50">Back</button>
            <button data-lambo-tab="engine" data-lambo-src="<?= $base ?>/assets/images/lambo/engine.jpg.png" class="lambo-tab flex-1 py-3 text-sm font-semibold text-slate-300 hover:bg-slate-700/50 transition-colors border-l border-slate-700/50">Engine</button>
          </div>
        </div>
        <p class="text-center text-slate-400 text-xs mt-3">Click the image to zoom in, then drag to look around.</p>
      </div>
    </div>
  </div>
</section>

<!-- Lamborghini viewer script -->
<script nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
(function () {
  const viewer = document.getElementById('lamboViewer');
  const img = document.getElementById('lamboImg');
  const hint = document.getElementById('lamboHint');
  const zoomBadge = document.getElementById('lamboZoomBadge');
  const tabs = document.querySelectorAll('.lambo-tab');
  if (!viewer || !img) return;

  let zoomed = false;
  let panX = 0, panY = 0;
  let dragging = false;
  let dragStartX = 0, dragStartY = 0;
  let panStartX = 0, panStartY = 0;

  function applyTransform() {
    const scale = zoomed ? 2 : 1;
    img.style.transform = 'translate(' + panX + 'px, ' + panY + 'px) scale(' + scale + ')';
  }

  function resetPan() {
    panX = 0; panY = 0;
    applyTransform();
  }

  function setZoomed(state) {
    zoomed = state;
    viewer.style.cursor = zoomed ? 'grab' : 'zoom-in';
    zoomBadge.style.opacity = zoomed ? '1' : '0';
    if (!zoomed) resetPan();
    applyTransform();
  }

  // Click to toggle zoom
  viewer.addEventListener('click', function (e) {
    if (dragging) return;
    // Zoom toward click point
    if (!zoomed) {
      const rect = viewer.getBoundingClientRect();
      const offsetX = e.clientX - rect.left - rect.width / 2;
      const offsetY = e.clientY - rect.top - rect.height / 2;
      panX = -offsetX;
      panY = -offsetY;
    }
    setZoomed(!zoomed);
  });

  // Drag to pan (only when zoomed)
  viewer.addEventListener('mousedown', function (e) {
    if (!zoomed) return;
    dragging = true;
    viewer.style.cursor = 'grabbing';
    dragStartX = e.clientX;
    dragStartY = e.clientY;
    panStartX = panX;
    panStartY = panY;
    e.preventDefault();
  });

  document.addEventListener('mousemove', function (e) {
    if (!dragging) return;
    panX = panStartX + (e.clientX - dragStartX);
    panY = panStartY + (e.clientY - dragStartY);
    applyTransform();
  });

  document.addEventListener('mouseup', function () {
    if (!dragging) return;
    dragging = false;
    viewer.style.cursor = zoomed ? 'grab' : 'zoom-in';
    // Small delay so click handler can skip
    setTimeout(function () { dragging = false; }, 50);
  });

  // Touch support
  viewer.addEventListener('touchstart', function (e) {
    if (!zoomed || e.touches.length !== 1) return;
    dragging = true;
    dragStartX = e.touches[0].clientX;
    dragStartY = e.touches[0].clientY;
    panStartX = panX;
    panStartY = panY;
  }, { passive: true });

  viewer.addEventListener('touchmove', function (e) {
    if (!dragging || e.touches.length !== 1) return;
    panX = panStartX + (e.touches[0].clientX - dragStartX);
    panY = panStartY + (e.touches[0].clientY - dragStartY);
    applyTransform();
  }, { passive: true });

  viewer.addEventListener('touchend', function () {
    dragging = false;
  });

  // Tab switching
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function (e) {
      e.stopPropagation();
      const src = tab.getAttribute('data-lambo-src');
      if (!src) return;
      // Swap image with fade
      img.style.opacity = '0';
      setTimeout(function () {
        img.src = src;
        setZoomed(false);
        img.style.opacity = '1';
      }, 200);
      // Update active styles
      tabs.forEach(function (t) {
        t.classList.remove('bg-blue-600', 'text-white');
        t.classList.add('text-slate-300', 'hover:bg-slate-700/50');
      });
      tab.classList.add('bg-blue-600', 'text-white');
      tab.classList.remove('text-slate-300', 'hover:bg-slate-700/50');
    });
  });

  // Fade hint after 4s
  setTimeout(function () { hint.style.opacity = '0'; }, 4000);
})();
</script>

<!-- Statistics Section -->
<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="text-center p-6 bg-slate-50 rounded-xl animate-fade-in-up stagger-1 card-hover">
        <div class="text-4xl font-bold text-blue-600">10K+</div>
        <div class="mt-2 text-slate-600">Active Listings</div>
      </div>
      <div class="text-center p-6 bg-slate-50 rounded-xl animate-fade-in-up stagger-2 card-hover">
        <div class="text-4xl font-bold text-blue-600">50K+</div>
        <div class="mt-2 text-slate-600">Registered Users</div>
      </div>
      <div class="text-center p-6 bg-slate-50 rounded-xl animate-fade-in-up stagger-3 card-hover">
        <div class="text-4xl font-bold text-blue-600">95%</div>
        <div class="mt-2 text-slate-600">Satisfaction Rate</div>
      </div>
    </div>
  </div>
</section>

<!-- Live Bidding Section -->
<section class="bg-slate-50 py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-8 animate-fade-in">
      <div>
        <h2 class="text-3xl font-bold text-slate-900">Live Bidding Auctions</h2>
        <p class="mt-2 text-slate-600">Don't miss out on these active auctions ending soon</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="text-blue-600 font-semibold hover:underline">View All &rarr;</a>
    </div>
    <?php if (!empty($endingSoon)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($endingSoon as $i => $car): ?>
          <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 bg-white rounded-xl animate-fade-in">
        <p class="text-slate-500">No live auctions at the moment. Check back soon!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Features Section -->
<section class="bg-white py-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 animate-fade-in">
      <h2 class="text-3xl font-bold text-slate-900">Why Choose Us</h2>
      <p class="mt-2 text-slate-600">Experience the best car auction platform with these features</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="text-center p-8 bg-slate-50 rounded-xl card-hover animate-fade-in-up stagger-1">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Secure Transactions</h3>
        <p class="text-slate-600">All transactions are protected with advanced encryption and security measures.</p>
      </div>
      <div class="text-center p-8 bg-slate-50 rounded-xl card-hover animate-fade-in-up stagger-2">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Real-time Bidding</h3>
        <p class="text-slate-600">Place bids in real-time with live updates and countdown timers.</p>
      </div>
      <div class="text-center p-8 bg-slate-50 rounded-xl card-hover animate-fade-in-up stagger-3">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">24/7 Support</h3>
        <p class="text-slate-600">Our support team is available around the clock to assist you.</p>
      </div>
    </div>
  </div>
</section>
