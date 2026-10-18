<?php
/** Home page: hero + stats + live bidding + features. */
$base = rtrim(Config::get('APP_URL', '/car'), '/');
?>

<!-- Hero Section -->
<section id="landingHero" class="relative bg-slate-900 overflow-hidden">
  <div class="absolute inset-0">
    <img src="<?= $base ?>/assets/images/hero-car.jpg" alt="Hero Car" class="w-full h-full object-cover opacity-40" onerror="this.style.display='none'">
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/70 via-slate-900/80 to-slate-900"></div>
  </div>
  <!-- Ambient glow accents -->
  <div aria-hidden="true" class="pointer-events-none absolute -top-32 left-1/2 -translate-x-1/2 w-[36rem] h-[36rem] rounded-full bg-blue-600/15 blur-3xl"></div>
  <div aria-hidden="true" class="pointer-events-none absolute -bottom-40 -right-32 w-[30rem] h-[30rem] rounded-full bg-indigo-500/10 blur-3xl"></div>

  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
    <div class="flex flex-col items-center gap-12">
      <!-- Heading + CTAs -->
      <div class="max-w-3xl text-center animate-fade-in-up">
        <span class="inline-flex items-center gap-2.5 bg-blue-600/20 text-blue-300 text-sm font-semibold pl-3 pr-4 py-1.5 rounded-full mb-5 border border-blue-500/30">
          <span class="relative flex h-2 w-2 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-400"></span>
          </span>
          Featured: Ferrari 599 GTB
        </span>
        <h1 class="text-4xl md:text-6xl font-bold text-white leading-tight tracking-tight">
          Find Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-300 to-blue-400">Dream Car</span> Today
        </h1>
        <p class="mt-6 text-lg text-slate-300 max-w-2xl mx-auto">Join thousands of bidders and sellers in the most trusted car auction platform. Place real-time bids, watch listings, and win your next vehicle.</p>
        <div class="mt-8 flex flex-wrap gap-4 justify-center">
          <a href="<?= $base ?>/cars" class="group inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-600/25 hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5">
            Browse Auctions
            <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
            </svg>
          </a>
          <?php if (Auth::guest()): ?>
            <a href="<?= $base ?>/register" class="border-2 border-white/80 text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 hover:-translate-y-0.5 transition">Start Selling</a>
          <?php else: ?>
            <a href="<?= $base ?>/cars/create" class="border-2 border-white/80 text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 hover:-translate-y-0.5 transition">List a Car</a>
          <?php endif; ?>
        </div>
        <!-- Trust indicators -->
        <div class="mt-10 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm text-slate-400">
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Verified listings
          </span>
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Secure payments
          </span>
          <span class="inline-flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Real-time bidding
          </span>
        </div>
      </div>

      <!-- Interactive 3D car viewer -->
      <div class="w-full max-w-3xl animate-fade-in-up stagger-2">
        <div class="rounded-2xl p-px bg-gradient-to-b from-blue-500/50 via-slate-600/30 to-transparent shadow-2xl shadow-blue-950/40">
          <div class="bg-slate-800/70 backdrop-blur-sm rounded-[15px] overflow-hidden">
          <!-- 3D viewer stage -->
          <div id="carViewer" class="relative aspect-[4/3] bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 overflow-hidden cursor-grab active:cursor-grabbing select-none">
            <!-- Static fallback (shown if WebGL/model load fails) -->
            <img id="carFallback" src="<?= $base ?>/assets/images/lambo/exterior.jpg.png" alt="Featured sports car" class="absolute inset-0 w-full h-full object-contain hidden" draggable="false">
            <!-- Loading spinner -->
            <div id="carLoading" class="absolute inset-0 flex items-center justify-center pointer-events-none">
              <svg class="w-10 h-10 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
              </svg>
            </div>
            <!-- Controls hint -->
            <div id="carHint" class="absolute bottom-3 left-3 bg-black/60 text-white text-xs px-3 py-1.5 rounded-full pointer-events-none transition-opacity duration-300">
              Drag to rotate &middot; scroll to zoom
            </div>
          </div>
          <!-- Paint picker -->
          <div class="flex items-center gap-3 px-4 py-3">
            <span class="text-sm font-semibold text-slate-300 shrink-0">Paint</span>
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
        <p class="text-center text-slate-400 text-xs mt-3">Interactive 3D model &mdash; drag to spin, scroll to zoom, pick a paint.</p>
      </div>
    </div>
  </div>

  <!-- Scroll cue -->
  <a href="#stats" class="hidden md:flex absolute bottom-5 left-1/2 -translate-x-1/2 items-center justify-center w-10 h-10 rounded-full text-slate-400 hover:text-white hover:bg-white/10 transition animate-bounce" aria-label="Scroll down">
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
    </svg>
  </a>
</section>

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
     Elements stay visible when JS/IntersectionObserver is unavailable. -->
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

<!-- Statistics Section -->
<section id="stats" class="bg-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal>
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
          </svg>
        </div>
        <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">10K+</div>
        <div class="mt-2 text-slate-600 font-medium">Active Listings</div>
      </div>
      <div class="text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal data-reveal-delay="90">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 11-8 0 4 4 0 018 0zm6-3a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
        </div>
        <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">50K+</div>
        <div class="mt-2 text-slate-600 font-medium">Registered Users</div>
      </div>
      <div class="text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal data-reveal-delay="180">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
          <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </div>
        <div class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-500">95%</div>
        <div class="mt-2 text-slate-600 font-medium">Satisfaction Rate</div>
      </div>
    </div>
  </div>
</section>

<!-- Live Bidding Section -->
<section class="bg-slate-50 py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8" data-reveal>
      <div>
        <div class="flex items-center gap-3">
          <span class="relative flex h-2.5 w-2.5 shrink-0">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
          </span>
          <h2 class="text-3xl font-bold text-slate-900">Live Bidding Auctions</h2>
        </div>
        <p class="mt-2 text-slate-600">Don't miss out on these active auctions ending soon</p>
      </div>
      <a href="<?= $base ?>/cars/bidding" class="group inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 transition">
        View All
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
      <div class="text-center py-12 bg-white rounded-xl animate-fade-in">
        <p class="text-slate-500">No live auctions at the moment. Check back soon!</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Features Section -->
<section class="bg-white py-16 md:py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12" data-reveal>
      <span class="inline-block text-xs font-semibold tracking-widest uppercase text-blue-600 bg-blue-50 px-3 py-1 rounded-full">Why choose us</span>
      <h2 class="mt-4 text-3xl font-bold text-slate-900">Built for Buyers and Sellers</h2>
      <p class="mt-2 text-slate-600 max-w-xl mx-auto">Experience the best car auction platform with these features</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="group text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal>
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Secure Transactions</h3>
        <p class="text-slate-600">All transactions are protected with advanced encryption and security measures.</p>
      </div>
      <div class="group text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal data-reveal-delay="90">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">Real-time Bidding</h3>
        <p class="text-slate-600">Place bids in real-time with live updates and countdown timers.</p>
      </div>
      <div class="group text-center p-8 bg-slate-50 rounded-2xl border border-slate-100 card-hover" data-reveal data-reveal-delay="180">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg shadow-blue-500/25 transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-3">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-900 mb-2">24/7 Support</h3>
        <p class="text-slate-600">Our support team is available around the clock to assist you.</p>
      </div>
    </div>
  </div>
</section>

<!-- Call to action -->
<section class="relative bg-slate-900 py-20 overflow-hidden">
  <div aria-hidden="true" class="pointer-events-none absolute -top-24 left-1/4 w-96 h-96 rounded-full bg-blue-600/15 blur-3xl"></div>
  <div aria-hidden="true" class="pointer-events-none absolute -bottom-32 right-1/4 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl"></div>
  <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center" data-reveal>
    <h2 class="text-3xl md:text-4xl font-bold text-white">Ready to Find Your Next Car?</h2>
    <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">Create a free account in minutes and join live auctions for thousands of verified vehicles.</p>
    <div class="mt-8 flex flex-wrap gap-4 justify-center">
      <?php if (Auth::guest()): ?>
        <a href="<?= $base ?>/register" class="group inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-600/25 hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5">
          Create Free Account
          <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
          </svg>
        </a>
        <a href="<?= $base ?>/cars" class="border-2 border-white/80 text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 hover:-translate-y-0.5 transition">Browse Auctions</a>
      <?php else: ?>
        <a href="<?= $base ?>/cars" class="group inline-flex items-center gap-2 bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-600/25 hover:shadow-xl hover:shadow-blue-600/30 hover:-translate-y-0.5">
          Browse Auctions
          <svg class="w-4 h-4 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12"/>
          </svg>
        </a>
        <a href="<?= $base ?>/cars/create" class="border-2 border-white/80 text-white font-semibold px-8 py-3 rounded-lg hover:bg-white hover:text-slate-900 hover:-translate-y-0.5 transition">List a Car</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Landing footer -->
<footer class="bg-slate-950 border-t border-white/10 py-8">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
    <a href="<?= $base ?>/" class="flex items-center gap-2 text-slate-200 font-bold">
      <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/>
      </svg>
      Car<span class="text-blue-400">Auction</span>
    </a>
    <nav class="flex items-center gap-6 text-sm text-slate-400">
      <a href="<?= $base ?>/cars" class="hover:text-white transition">Browse</a>
      <a href="<?= $base ?>/cars/bidding" class="hover:text-white transition">Bidding</a>
      <a href="<?= $base ?>/cars/featured" class="hover:text-white transition">Featured</a>
      <?php if (Auth::guest()): ?>
        <a href="<?= $base ?>/login" class="hover:text-white transition">Log in</a>
      <?php endif; ?>
    </nav>
    <p class="text-xs text-slate-500">&copy; <?= date('Y') ?> <?= e(Config::get('APP_NAME', 'CarAuction')) ?>. All rights reserved.</p>
  </div>
</footer>
