<?php
/** Home page: hero + stats + how it works + live bidding + featured + features + testimonials + CTA. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
$featuredCars = array_slice($featured ?? [], 0, 4);
?>

<!-- Hero Section -->
<section id="landingHero" class="relative bg-slate-950 overflow-hidden">
  <!-- Backdrop: spotlight glows + fading grid (pure CSS, no image dependency) -->
  <div aria-hidden="true" class="absolute inset-0 pointer-events-none">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_55%_50%_at_72%_22%,rgba(59,130,246,0.20),transparent)]"></div>
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_45%_40%_at_15%_85%,rgba(99,102,241,0.14),transparent)]"></div>
    <div class="absolute inset-0 opacity-[0.13]"
         style="background-image:linear-gradient(rgba(148,163,184,.3) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.3) 1px,transparent 1px);background-size:56px 56px;-webkit-mask-image:radial-gradient(ellipse 85% 75% at 50% 35%,black,transparent);mask-image:radial-gradient(ellipse 85% 75% at 50% 35%,black,transparent);"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-14 pb-28 md:pt-20 md:pb-36">
    <div class="grid lg:grid-cols-2 gap-12 lg:gap-10 xl:gap-16 items-center">

      <!-- Copy + CTAs -->
      <div class="max-w-xl animate-fade-in-up">
        <span class="inline-flex items-center gap-2.5 rounded-full border border-white/10 bg-white/5 pl-3 pr-4 py-1.5 text-xs font-semibold text-blue-300 backdrop-blur">
          <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-400"></span>
          </span>
          Live auctions happening now
        </span>

        <h1 class="mt-6 text-4xl sm:text-5xl xl:text-[3.6rem] font-bold text-white leading-[1.08] tracking-tight">
          The car you want.<br>
          <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-cyan-300">The price you set.</span>
        </h1>

        <p class="mt-6 text-base sm:text-lg text-slate-300/90 leading-relaxed">
          Browse verified listings, place real-time bids, and win your next vehicle on the auction platform built for car lovers.
        </p>

        <div class="mt-8 flex flex-wrap items-center gap-4">
          <a href="<?= $base ?>/cars" class="group inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-7 py-3 rounded-xl hover:bg-blue-500 transition shadow-lg shadow-blue-600/30 hover:shadow-xl hover:shadow-blue-500/40 hover:-translate-y-0.5">
            Browse Auctions
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
            </svg>
          </a>
          <?php if (Auth::guest()): ?>
            <a href="<?= $base ?>/register" class="inline-flex items-center border border-white/20 text-white font-semibold px-7 py-3 rounded-xl hover:bg-white/10 hover:border-white/40 hover:-translate-y-0.5 transition">Start Selling</a>
          <?php else: ?>
            <a href="<?= $base ?>/cars/create" class="inline-flex items-center border border-white/20 text-white font-semibold px-7 py-3 rounded-xl hover:bg-white/10 hover:border-white/40 hover:-translate-y-0.5 transition">List a Car</a>
          <?php endif; ?>
          <a href="#how-it-works" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-400 hover:text-white transition">
            See how it works
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7-7-7m14-8l-7 7-7-7"/>
            </svg>
          </a>
        </div>

        <!-- Trust indicators -->
        <div class="mt-10 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-slate-400">
          <span class="inline-flex items-center gap-2">
            <span class="flex text-amber-400" aria-hidden="true">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.52.44c.5.04.7.66.32.99l-4.2 3.6a.56.56 0 00-.18.55l1.28 5.39a.56.56 0 01-.84.61l-4.72-2.88a.56.56 0 00-.59 0l-4.73 2.88a.56.56 0 01-.84-.6l1.29-5.4a.56.56 0 00-.19-.55l-4.2-3.6a.56.56 0 01.32-.99l5.52-.44a.56.56 0 00.48-.35L11.48 3.5z"/></svg>
            </span>
            4.9/5 from 2,400+ bidders
          </span>
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Verified listings
          </span>
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            Secure payments
          </span>
        </div>
      </div>

      <!-- Interactive 3D car viewer -->
      <div class="relative animate-fade-in-up stagger-2">
        <div aria-hidden="true" class="absolute -inset-8 rounded-full bg-blue-600/15 blur-3xl pointer-events-none"></div>
        <div class="relative rounded-2xl p-px bg-gradient-to-b from-white/20 via-white/5 to-transparent shadow-2xl shadow-black/50">
          <div class="bg-slate-900/80 backdrop-blur-sm rounded-[15px] overflow-hidden">
            <!-- 3D viewer stage -->
            <div id="carViewer" class="relative aspect-[4/3] bg-gradient-to-b from-slate-900 via-slate-950 to-slate-900 overflow-hidden cursor-grab active:cursor-grabbing select-none">
              <!-- Static fallback (shown if WebGL/model load fails) -->
              <img id="carFallback" src="<?= $base ?>/assets/images/lambo/exterior.jpg.png" alt="Featured sports car" class="absolute inset-0 w-full h-full object-contain hidden" draggable="false">
              <!-- Loading spinner -->
              <div id="carLoading" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <svg class="w-10 h-10 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
              </div>
              <!-- Model badge -->
              <div class="absolute top-3 left-3 flex items-center gap-2 bg-black/50 backdrop-blur px-3 py-1.5 rounded-full pointer-events-none">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                <span class="text-[11px] font-medium text-white/90 tracking-wide">Ferrari 599 GTB</span>
              </div>
              <!-- 360 badge -->
              <div class="absolute top-3 right-3 flex items-center gap-1.5 bg-black/50 backdrop-blur px-3 py-1.5 rounded-full pointer-events-none">
                <svg class="w-3.5 h-3.5 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5.6 9A8 8 0 0119 7.5M18.4 15A8 8 0 015 16.5"/>
                </svg>
                <span class="text-[11px] font-medium text-white/90 tracking-wide">360&deg;</span>
              </div>
              <!-- Controls hint -->
              <div id="carHint" class="absolute bottom-3 left-1/2 -translate-x-1/2 whitespace-nowrap bg-black/60 text-white text-xs px-3 py-1.5 rounded-full pointer-events-none transition-opacity duration-300">
                Drag to rotate &middot; scroll to zoom
              </div>
            </div>
            <!-- Paint picker -->
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-t border-white/5">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 shrink-0">Paint</span>
              <div class="flex flex-wrap gap-2">
                <button type="button" data-color="#d40000" aria-label="Rosso red" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 ring-2 ring-blue-400 transition hover:scale-110" style="background-color:#d40000"></button>
                <button type="button" data-color="#ffcf00" aria-label="Giallo yellow" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 transition hover:scale-110" style="background-color:#ffcf00"></button>
                <button type="button" data-color="#0a6cff" aria-label="Blu" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 transition hover:scale-110" style="background-color:#0a6cff"></button>
                <button type="button" data-color="#15181d" aria-label="Nero black" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 transition hover:scale-110" style="background-color:#15181d"></button>
                <button type="button" data-color="#c9cdd4" aria-label="Grigio silver" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 transition hover:scale-110" style="background-color:#c9cdd4"></button>
                <button type="button" data-color="#0e7a3d" aria-label="Verde green" class="paint-swatch w-7 h-7 rounded-full border-2 border-white/25 transition hover:scale-110" style="background-color:#0e7a3d"></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Scroll cue -->
  <a href="#stats" class="hidden md:flex absolute bottom-24 left-1/2 -translate-x-1/2 items-center justify-center w-10 h-10 rounded-full text-slate-500 hover:text-white hover:bg-white/10 transition animate-bounce" aria-label="Scroll down">
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
    </svg>
  </a>
</section>

<!-- Stats strip: floating card overlapping the hero edge -->
<section id="stats" class="relative z-10 -mt-14 md:-mt-20 scroll-mt-24">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-px rounded-2xl bg-slate-200/70 border border-slate-200 shadow-xl shadow-slate-900/10 overflow-hidden" data-reveal>
      <div class="bg-white p-6 md:p-8 flex items-center gap-4">
        <span class="w-11 h-11 shrink-0 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
          </svg>
        </span>
        <div>
          <div class="text-2xl md:text-3xl font-bold text-slate-900 leading-none">10K+</div>
          <div class="mt-1.5 text-xs md:text-sm text-slate-500 font-medium">Active listings</div>
        </div>
      </div>
      <div class="bg-white p-6 md:p-8 flex items-center gap-4">
        <span class="w-11 h-11 shrink-0 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 11-8 0 4 4 0 018 0zm6-3a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </span>
        <div>
          <div class="text-2xl md:text-3xl font-bold text-slate-900 leading-none">50K+</div>
          <div class="mt-1.5 text-xs md:text-sm text-slate-500 font-medium">Registered users</div>
        </div>
      </div>
      <div class="bg-white p-6 md:p-8 flex items-center gap-4">
        <span class="w-11 h-11 shrink-0 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </span>
        <div>
          <div class="text-2xl md:text-3xl font-bold text-slate-900 leading-none">8K+</div>
          <div class="mt-1.5 text-xs md:text-sm text-slate-500 font-medium">Auctions won</div>
        </div>
      </div>
      <div class="bg-white p-6 md:p-8 flex items-center gap-4">
        <span class="w-11 h-11 shrink-0 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l-3.976-2.888a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </span>
        <div>
          <div class="text-2xl md:text-3xl font-bold text-slate-900 leading-none">95%</div>
          <div class="mt-1.5 text-xs md:text-sm text-slate-500 font-medium">Satisfaction rate</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How it works -->
<section id="how-it-works" class="bg-white py-20 md:py-28 scroll-mt-16">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14" data-reveal>
      <span class="inline-block text-xs font-semibold tracking-widest uppercase text-blue-600 bg-blue-50 px-3 py-1 rounded-full">How it works</span>
      <h2 class="mt-4 text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Three steps to your next car</h2>
      <p class="mt-3 text-slate-600 max-w-xl mx-auto">From sign-up to driveway — the whole process takes minutes to start.</p>
    </div>

    <div class="relative grid md:grid-cols-3 gap-6">
      <div aria-hidden="true" class="hidden lg:block absolute top-16 left-[18%] right-[18%] border-t-2 border-dashed border-slate-200"></div>

      <div class="relative p-8 rounded-2xl border border-slate-200 bg-white card-hover" data-reveal>
        <div class="flex items-center justify-between">
          <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </span>
          <span class="text-5xl font-bold text-slate-100 select-none leading-none">01</span>
        </div>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Create your account</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Sign up free in under a minute. Verify your account to unlock bidding and listing.</p>
      </div>

      <div class="relative p-8 rounded-2xl border border-slate-200 bg-white card-hover" data-reveal data-reveal-delay="90">
        <div class="flex items-center justify-between">
          <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 007.5 7.5h4.5a7.5 7.5 0 007.5-7.5v-4a1.5 1.5 0 00-3 0m-6-1.5v-7a1.5 1.5 0 013 0v7m0 0v-3a1.5 1.5 0 013 0v3"/>
            </svg>
          </span>
          <span class="text-5xl font-bold text-slate-100 select-none leading-none">02</span>
        </div>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Place your bid</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Watch live countdowns, get instant outbid alerts, and raise your bid with one click.</p>
      </div>

      <div class="relative p-8 rounded-2xl border border-slate-200 bg-white card-hover" data-reveal data-reveal-delay="180">
        <div class="flex items-center justify-between">
          <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
          </span>
          <span class="text-5xl font-bold text-slate-100 select-none leading-none">03</span>
        </div>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Win &amp; drive away</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Top bid when the timer hits zero wins. Complete a secure payment and it's yours.</p>
      </div>
    </div>
  </div>
</section>

<!-- Live Bidding Section -->
<section id="live-auctions" class="bg-slate-50 py-20 md:py-24 scroll-mt-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
      <div>
        <div class="flex items-center gap-3">
          <span class="relative flex h-2.5 w-2.5 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
          </span>
          <h2 id="ending-soon" class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Ending soon</h2>
        </div>
        <p class="mt-2 text-slate-600">Active auctions closing shortly — place your bid before time runs out.</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="group inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 transition">
        View all live auctions
        <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
        </svg>
      </a>
    </div>
    <?php if (!empty($endingSoon)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-reveal data-reveal-delay="100">
        <?php foreach ($endingSoon as $i => $car): ?>
          <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="text-center py-12 bg-white rounded-2xl border border-slate-200 animate-fade-in">
        <p class="text-slate-500">No live auctions at the moment. Check back soon!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Featured showroom -->
<?php if (!empty($featuredCars)): ?>
<section id="showroom" class="relative bg-slate-950 py-20 md:py-24 overflow-hidden scroll-mt-24">
  <div aria-hidden="true" class="pointer-events-none absolute inset-0">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_50%_45%_at_50%_0%,rgba(59,130,246,0.14),transparent)]"></div>
  </div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-10" data-reveal>
      <div>
        <span class="inline-block text-xs font-semibold tracking-widest uppercase text-blue-300 bg-blue-500/10 border border-blue-400/20 px-3 py-1 rounded-full">Showroom</span>
        <h2 class="mt-4 text-3xl md:text-4xl font-bold text-white tracking-tight">Featured cars</h2>
        <p class="mt-2 text-slate-400">Hand-picked listings from verified sellers.</p>
      </div>
      <a href="<?= $base ?>/cars/featured" class="group inline-flex items-center gap-2 text-blue-300 font-semibold hover:text-white transition">
        View featured
        <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
        </svg>
      </a>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6" data-reveal data-reveal-delay="100">
      <?php foreach ($featuredCars as $i => $car): ?>
        <?php $stagger = 'stagger-' . (min($i, 7) + 1); include __DIR__ . '/../partials/car_card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- Features bento -->
<section class="bg-white py-20 md:py-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14" data-reveal>
      <span class="inline-block text-xs font-semibold tracking-widest uppercase text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Why Car Auction</span>
      <h2 class="mt-4 text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Everything you need to bid with confidence</h2>
      <p class="mt-3 text-slate-600 max-w-xl mx-auto">A platform designed around trust, speed, and transparency.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <!-- Real-time bidding (wide) -->
      <div class="md:col-span-2 group relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-8 card-hover" data-reveal>
        <div aria-hidden="true" class="absolute -right-16 -top-16 w-56 h-56 rounded-full bg-blue-100/60 blur-3xl group-hover:bg-blue-100 transition"></div>
        <div class="relative">
          <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </span>
          <h3 class="mt-5 text-xl font-semibold text-slate-900">Real-time bidding engine</h3>
          <p class="mt-2 text-sm text-slate-600 leading-relaxed max-w-md">Prices, countdowns, and bid counts update live — no page refresh needed. You'll always know exactly where you stand.</p>
          <div class="mt-6 space-y-2 max-w-sm">
            <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
              <span class="relative flex h-2 w-2 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
              </span>
              <span class="font-semibold text-slate-800">&#8369;1,250,000</span>
              <span class="text-slate-400 text-xs ml-auto">Bid placed &middot; just now</span>
            </div>
            <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm opacity-75">
              <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
              <span class="font-semibold text-slate-700">&#8369;1,220,000</span>
              <span class="text-slate-400 text-xs ml-auto">Bid placed &middot; 14s ago</span>
            </div>
            <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm opacity-50">
              <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
              <span class="font-semibold text-slate-700">&#8369;1,180,000</span>
              <span class="text-slate-400 text-xs ml-auto">Bid placed &middot; 42s ago</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Secure payments -->
      <div class="group p-8 rounded-2xl border border-slate-200 bg-slate-50 card-hover" data-reveal data-reveal-delay="90">
        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </span>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Secure payments</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Every transaction is protected with encryption and a clear payment trail.</p>
      </div>

      <!-- Verified listings -->
      <div class="group p-8 rounded-2xl border border-slate-200 bg-slate-50 card-hover" data-reveal>
        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
          </svg>
        </span>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Verified listings</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Listings are reviewed before going live, so what you see is what you bid on.</p>
      </div>

      <!-- Watchlist alerts -->
      <div class="group p-8 rounded-2xl border border-slate-200 bg-slate-50 card-hover" data-reveal data-reveal-delay="90">
        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1"/>
          </svg>
        </span>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">Instant alerts</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Add cars to your watchlist and get notified the moment you're outbid.</p>
      </div>

      <!-- Support -->
      <div class="group p-8 rounded-2xl border border-slate-200 bg-slate-50 card-hover" data-reveal data-reveal-delay="180">
        <span class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </span>
        <h3 class="mt-5 text-lg font-semibold text-slate-900">24/7 support</h3>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">Real humans, around the clock — before, during, and after your auction.</p>
      </div>
    </div>
  </div>
</section>

<!-- Testimonials -->
<section class="bg-slate-50 py-20 md:py-24">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-14" data-reveal>
      <span class="inline-block text-xs font-semibold tracking-widest uppercase text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Testimonials</span>
      <h2 class="mt-4 text-3xl md:text-4xl font-bold text-slate-900 tracking-tight">Loved by buyers and sellers</h2>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
      <figure class="bg-white rounded-2xl border border-slate-200 p-7 card-hover" data-reveal>
        <div class="flex text-amber-400" aria-hidden="true">
          <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.52.44c.5.04.7.66.32.99l-4.2 3.6a.56.56 0 00-.18.55l1.28 5.39a.56.56 0 01-.84.61l-4.72-2.88a.56.56 0 00-.59 0l-4.73 2.88a.56.56 0 01-.84-.6l1.29-5.4a.56.56 0 00-.19-.55l-4.2-3.6a.56.56 0 01.32-.99l5.52-.44a.56.56 0 00.48-.35L11.48 3.5z"/></svg>
          <?php endfor; ?>
        </div>
        <blockquote class="mt-4 text-sm text-slate-600 leading-relaxed">"Won my Civic below market price on my second bid. The live countdown had my heart racing — in the best way."</blockquote>
        <figcaption class="mt-5 flex items-center gap-3">
          <span class="w-9 h-9 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-semibold">M</span>
          <div>
            <div class="text-sm font-semibold text-slate-800">Miguel R.</div>
            <div class="text-xs text-slate-400">Buyer &middot; Quezon City</div>
          </div>
        </figcaption>
      </figure>

      <figure class="bg-white rounded-2xl border border-slate-200 p-7 card-hover" data-reveal data-reveal-delay="90">
        <div class="flex text-amber-400" aria-hidden="true">
          <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.52.44c.5.04.7.66.32.99l-4.2 3.6a.56.56 0 00-.18.55l1.28 5.39a.56.56 0 01-.84.61l-4.72-2.88a.56.56 0 00-.59 0l-4.73 2.88a.56.56 0 01-.84-.6l1.29-5.4a.56.56 0 00-.19-.55l-4.2-3.6a.56.56 0 01.32-.99l5.52-.44a.56.56 0 00.48-.35L11.48 3.5z"/></svg>
          <?php endfor; ?>
        </div>
        <blockquote class="mt-4 text-sm text-slate-600 leading-relaxed">"Sold my Montero in three days. Listing took ten minutes and the bids came in faster than any classifieds site I've used."</blockquote>
        <figcaption class="mt-5 flex items-center gap-3">
          <span class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-sm font-semibold">A</span>
          <div>
            <div class="text-sm font-semibold text-slate-800">Anna L.</div>
            <div class="text-xs text-slate-400">Seller &middot; Cebu</div>
          </div>
        </figcaption>
      </figure>

      <figure class="bg-white rounded-2xl border border-slate-200 p-7 card-hover" data-reveal data-reveal-delay="180">
        <div class="flex text-amber-400" aria-hidden="true">
          <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11a.56.56 0 00.47.35l5.52.44c.5.04.7.66.32.99l-4.2 3.6a.56.56 0 00-.18.55l1.28 5.39a.56.56 0 01-.84.61l-4.72-2.88a.56.56 0 00-.59 0l-4.73 2.88a.56.56 0 01-.84-.6l1.29-5.4a.56.56 0 00-.19-.55l-4.2-3.6a.56.56 0 01.32-.99l5.52-.44a.56.56 0 00.48-.35L11.48 3.5z"/></svg>
          <?php endfor; ?>
        </div>
        <blockquote class="mt-4 text-sm text-slate-600 leading-relaxed">"The watchlist alerts saved me — I got pinged the second I was outbid and won with seconds left. Payment was painless."</blockquote>
        <figcaption class="mt-5 flex items-center gap-3">
          <span class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm font-semibold">J</span>
          <div>
            <div class="text-sm font-semibold text-slate-800">Jayson T.</div>
            <div class="text-xs text-slate-400">Buyer &middot; Davao</div>
          </div>
        </figcaption>
      </figure>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="bg-white pb-20 md:pb-28">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-600 to-indigo-800 px-6 py-16 md:py-20 text-center shadow-2xl shadow-indigo-900/30" data-reveal>
      <div aria-hidden="true" class="pointer-events-none absolute inset-0">
        <div class="absolute inset-0 opacity-[0.08]"
             style="background-image:linear-gradient(rgba(255,255,255,.4) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.4) 1px,transparent 1px);background-size:48px 48px;"></div>
      </div>
      <div class="relative">
        <h2 class="text-3xl md:text-4xl font-bold text-white tracking-tight">Your next car is one bid away</h2>
        <p class="mt-4 text-base md:text-lg text-blue-100 max-w-2xl mx-auto">Create a free account in minutes and join live auctions for thousands of verified vehicles.</p>
        <div class="mt-9 flex flex-wrap gap-4 justify-center">
          <?php if (Auth::guest()): ?>
            <a href="<?= $base ?>/register" class="group inline-flex items-center gap-2 bg-white text-indigo-700 font-semibold px-8 py-3 rounded-xl hover:bg-blue-50 transition shadow-lg hover:-translate-y-0.5">
              Create Free Account
              <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
              </svg>
            </a>
            <a href="<?= $base ?>/cars" class="inline-flex items-center border-2 border-white/60 text-white font-semibold px-8 py-3 rounded-xl hover:bg-white/10 hover:border-white hover:-translate-y-0.5 transition">Browse Auctions</a>
          <?php else: ?>
            <a href="<?= $base ?>/cars" class="group inline-flex items-center gap-2 bg-white text-indigo-700 font-semibold px-8 py-3 rounded-xl hover:bg-blue-50 transition shadow-lg hover:-translate-y-0.5">
              Browse Auctions
              <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
              </svg>
            </a>
            <a href="<?= $base ?>/cars/create" class="inline-flex items-center border-2 border-white/60 text-white font-semibold px-8 py-3 rounded-xl hover:bg-white/10 hover:border-white hover:-translate-y-0.5 transition">List a Car</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Landing footer -->
<footer class="bg-slate-950 border-t border-white/10">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid gap-10 md:grid-cols-3">
      <div>
        <a href="<?= $base ?>/" class="flex items-center gap-2 text-slate-100 font-bold text-lg">
          <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
          </svg>
          Car<span class="text-blue-400">Auction</span>
        </a>
        <p class="mt-3 text-sm text-slate-400 leading-relaxed max-w-xs">The trusted marketplace for buying and selling cars at auction — verified listings, live bidding, secure payments.</p>
      </div>
      <nav class="grid grid-cols-2 gap-8 md:col-span-2 md:justify-items-end">
        <div>
          <div class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-3">Marketplace</div>
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="<?= $base ?>/cars" class="hover:text-white transition">Browse cars</a></li>
            <li><a href="<?= $base ?>/cars/bidding" class="hover:text-white transition">Live bidding</a></li>
            <li><a href="<?= $base ?>/cars/featured" class="hover:text-white transition">Featured cars</a></li>
          </ul>
        </div>
        <div>
          <div class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-3">Account</div>
          <ul class="space-y-2 text-sm text-slate-400">
            <?php if (Auth::guest()): ?>
              <li><a href="<?= $base ?>/login" class="hover:text-white transition">Log in</a></li>
              <li><a href="<?= $base ?>/register" class="hover:text-white transition">Create account</a></li>
            <?php else: ?>
              <li><a href="<?= $base ?>/dashboard" class="hover:text-white transition">Dashboard</a></li>
              <li><a href="<?= $base ?>/cars/create" class="hover:text-white transition">List a car</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </nav>
    </div>
  </div>
  <div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-2">
      <p class="text-xs text-slate-500">&copy; <?= date('Y') ?> <?= e(Config::get('APP_NAME', 'CarAuction')) ?>. All rights reserved.</p>
      <p class="text-xs text-slate-600">Bid smart. Drive happy.</p>
    </div>
  </div>
</footer>

<!-- 3D car viewer: import map + module script (Three.js is vendored locally for CSP) -->
<script type="importmap" nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
{
  "imports": {
    "three": "<?= $base ?>/assets/js/vendor/three/three.module.min.js"
  }
}
</script>
<script type="module" nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
import * as THREE from 'three';
import { GLTFLoader } from '<?= $base ?>/assets/js/vendor/three/loaders/GLTFLoader.js';
import { OrbitControls } from '<?= $base ?>/assets/js/vendor/three/controls/OrbitControls.js';
import { RoomEnvironment } from '<?= $base ?>/assets/js/vendor/three/environments/RoomEnvironment.js';

const container = document.getElementById('carViewer');
const loading = document.getElementById('carLoading');
const fallback = document.getElementById('carFallback');
const hint = document.getElementById('carHint');
const swatches = document.querySelectorAll('.paint-swatch');

function showFallback() {
  if (loading) loading.style.display = 'none';
  if (fallback) fallback.classList.remove('hidden');
}

if (container) {
  try {
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.15;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.domElement.className = 'absolute inset-0 w-full h-full block';
    container.appendChild(renderer.domElement);

    const scene = new THREE.Scene();
    scene.fog = new THREE.Fog(0x0f172a, 14, 30);
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    scene.environmentIntensity = 0.8;

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 100);
    camera.position.set(4.6, 2.0, 5.8);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.target.set(0, 0.55, 0);
    controls.enableDamping = true;
    controls.dampingFactor = 0.08;
    controls.enablePan = false;
    controls.minDistance = 3.5;
    controls.maxDistance = 11;
    controls.maxPolarAngle = Math.PI * 0.55;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    controls.autoRotate = !reducedMotion;
    controls.autoRotateSpeed = 1.4;

    // Studio lighting: warm key light casts the shadow, cool rim separates
    // the car from the dark backdrop
    const keyLight = new THREE.DirectionalLight(0xfff4e6, 2.4);
    keyLight.position.set(5, 8, 4);
    keyLight.castShadow = true;
    keyLight.shadow.mapSize.set(2048, 2048);
    keyLight.shadow.camera.near = 1;
    keyLight.shadow.camera.far = 24;
    keyLight.shadow.camera.left = -7;
    keyLight.shadow.camera.right = 7;
    keyLight.shadow.camera.top = 7;
    keyLight.shadow.camera.bottom = -7;
    keyLight.shadow.bias = -0.0002;
    keyLight.shadow.normalBias = 0.02;
    keyLight.shadow.camera.updateProjectionMatrix();
    scene.add(keyLight);

    const rimLight = new THREE.DirectionalLight(0x9db8ff, 1.1);
    rimLight.position.set(-6, 4, -7);
    scene.add(rimLight);

    // Showroom floor: wide dark disc that catches the shadow, fading into fog
    const ground = new THREE.Mesh(
      new THREE.CircleGeometry(9, 64),
      new THREE.MeshStandardMaterial({ color: 0x0d1424, roughness: 0.85, metalness: 0.1 })
    );
    ground.rotation.x = -Math.PI / 2;
    ground.receiveShadow = true;
    scene.add(ground);

    // Soft radial light pool under the car (additive glow disc)
    const glowCanvas = document.createElement('canvas');
    glowCanvas.width = glowCanvas.height = 256;
    const gctx = glowCanvas.getContext('2d');
    const grad = gctx.createRadialGradient(128, 128, 8, 128, 128, 128);
    grad.addColorStop(0, 'rgba(96,165,250,0.22)');
    grad.addColorStop(0.5, 'rgba(59,130,246,0.08)');
    grad.addColorStop(1, 'rgba(59,130,246,0)');
    gctx.fillStyle = grad;
    gctx.fillRect(0, 0, 256, 256);
    const glow = new THREE.Mesh(
      new THREE.CircleGeometry(6, 48),
      new THREE.MeshBasicMaterial({
        map: new THREE.CanvasTexture(glowCanvas),
        transparent: true,
        depthWrite: false,
        blending: THREE.AdditiveBlending
      })
    );
    glow.rotation.x = -Math.PI / 2;
    glow.position.y = 0.01;
    scene.add(glow);

    // Body paint materials for the color picker
    const paintMats = [];

    new GLTFLoader().load(
      '<?= $base ?>/assets/models/ferrari.glb',
      function (gltf) {
        const model = gltf.scene;

        // This GLB ships with no textures and a uniform roughness/metalness on
        // every material, so we retune each surface type to plausible PBR values.
        const MAT_TUNE = {
          body_color:        { paint: true, metalness: 0.85, roughness: 0.28 },
          ferrari_yellow:    { paint: true, metalness: 0.6,  roughness: 0.32 },
          _0098_dodgerblue:  { paint: true, metalness: 0.6,  roughness: 0.32 },
          metal_chrome:      { metalness: 1.0, roughness: 0.08, envMapIntensity: 1.6 },
          metal_gray:        { metalness: 0.9, roughness: 0.35, envMapIntensity: 1.3 },
          glass_gray:        { glass: true, opacity: 0.35 },
          taillight_glass:   { glass: true, opacity: 0.55 },
          projector_glass:   { glass: true, opacity: 0.7 },
          turn_signal_led:   { metalness: 0.1, roughness: 0.3, emissive: true, envMapIntensity: 1.4 },
          tires:             { metalness: 0,    roughness: 0.95 },
          carpet:            { metalness: 0,    roughness: 1.0 },
          carbon_fiber:      { metalness: 0.6,  roughness: 0.3,  envMapIntensity: 1.3 },
          leather:           { metalness: 0,    roughness: 0.7 },
          leather_red:       { metalness: 0,    roughness: 0.7 },
          interior_dark:     { metalness: 0.05, roughness: 0.7 },
          interior_light:    { metalness: 0.05, roughness: 0.7 },
          plastic_gray:      { metalness: 0.1,  roughness: 0.6 }
        };

        model.traverse(function (o) {
          if (!o.isMesh) return;
          o.castShadow = true;
          o.receiveShadow = true;
          const mats = Array.isArray(o.material) ? o.material : [o.material];
          const upgraded = mats.map(function (m) {
            if (!m) return m;
            const tune = MAT_TUNE[(m.name || '').toLowerCase()] || {};

            if (tune.paint || tune.glass) {
              // Upgrade to physical material for clearcoat / realistic glass.
              // Build with explicit standard props — Material.copy() would pull
              // `undefined` into physical-only props and feed NaN to the shader.
              if (!m.isMeshPhysicalMaterial) {
                const phys = new THREE.MeshPhysicalMaterial({
                  color: m.color ? m.color.clone() : 0xffffff,
                  metalness: m.metalness ?? 0,
                  roughness: m.roughness ?? 1,
                  opacity: m.opacity ?? 1,
                  transparent: !!m.transparent,
                  side: m.side ?? THREE.FrontSide
                });
                phys.name = m.name;
                m = phys;
              }
            }
            if (tune.paint) {
              m.clearcoat = 1;
              m.clearcoatRoughness = 0.05;
              m.envMapIntensity = 1.4;
            }
            if (tune.glass) {
              m.metalness = 0;
              m.roughness = 0.05;
              m.envMapIntensity = 1.8;
              m.transparent = true;
              m.opacity = tune.opacity ?? 0.4;
              m.depthWrite = false;
            }
            if (tune.metalness !== undefined) m.metalness = tune.metalness;
            if (tune.roughness !== undefined) m.roughness = tune.roughness;
            if (tune.envMapIntensity !== undefined) m.envMapIntensity = tune.envMapIntensity;
            else if ('envMapIntensity' in m && m.envMapIntensity === 1) m.envMapIntensity = 1.2;
            if (tune.emissive) {
              m.emissive.copy(m.color);
              m.emissiveIntensity = 0.9;
            }
            if (m.name === 'Body_Color') paintMats.push(m);
            m.needsUpdate = true;
            return m;
          });
          o.material = Array.isArray(o.material) ? upgraded : upgraded[0];
        });
        // Normalize: scale so the car is ~4.4 units wide, centered, wheels on ground
        const size = new THREE.Box3().setFromObject(model).getSize(new THREE.Vector3());
        model.scale.setScalar(4.4 / Math.max(size.x, size.z));
        const box = new THREE.Box3().setFromObject(model);
        const c = box.getCenter(new THREE.Vector3());
        model.position.x -= c.x;
        model.position.z -= c.z;
        model.position.y -= box.min.y;
        model.rotation.y = Math.PI; // face the front toward the camera
        scene.add(model);
        if (loading) loading.style.display = 'none';
      },
      undefined,
      showFallback
    );

    // Pause rendering when the viewer scrolls out of view
    let inView = true;
    new IntersectionObserver(function (entries) {
      inView = entries[0].isIntersecting;
    }).observe(container);

    // Stop auto-rotate while the user drags, resume after 4s idle
    let lastInteract = 0;
    controls.addEventListener('start', function () { controls.autoRotate = false; });
    controls.addEventListener('end', function () { lastInteract = performance.now(); });

    renderer.setAnimationLoop(function () {
      if (!inView) return;
      if (!controls.autoRotate && !reducedMotion && performance.now() - lastInteract > 4000) {
        controls.autoRotate = true;
      }
      controls.update();
      renderer.render(scene, camera);
    });

    // Keep canvas matched to the stage size
    function resize() {
      const w = container.clientWidth, h = container.clientHeight;
      if (!w || !h) return;
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h, false);
    }
    new ResizeObserver(resize).observe(container);
    resize();

    // Paint swatches -> recolor the Body_Color material
    swatches.forEach(function (btn) {
      btn.addEventListener('click', function () {
        const color = btn.getAttribute('data-color');
        paintMats.forEach(function (m) { m.color.set(color); });
        swatches.forEach(function (b) { b.classList.remove('ring-2', 'ring-blue-400'); });
        btn.classList.add('ring-2', 'ring-blue-400');
      });
    });

    renderer.domElement.addEventListener('webglcontextlost', showFallback);
    setTimeout(function () { if (hint) hint.style.opacity = '0'; }, 5000);
  } catch (err) {
    showFallback();
  }
}
</script>

<!-- Scroll reveal: sections marked data-reveal fade in as they enter view.
     Elements stay visible when JS/IntersectionObserver is unavailable.
     NOTE: this must run AFTER all [data-reveal] markup so the observer
     actually finds the elements (classic scripts run at parse position). -->
<script nonce="<?= e(SecurityHeaders::cspNonce()) ?>">
(function () {
  var els = document.querySelectorAll('[data-reveal]');
  if (!els.length || !('IntersectionObserver' in window)
      || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var el = entry.target;
      el.classList.add('is-revealed');
      io.unobserve(el);
      var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
      setTimeout(function () {
        el.classList.remove('reveal', 'is-revealed');
        el.style.transitionDelay = '';
      }, 800 + delay);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
  els.forEach(function (el) {
    el.classList.add('reveal');
    var delay = parseInt(el.getAttribute('data-reveal-delay') || '0', 10);
    if (delay > 0) el.style.transitionDelay = delay + 'ms';
    io.observe(el);
  });
})();
</script>
