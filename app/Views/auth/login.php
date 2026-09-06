<?php
/** Login page. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
// Inline rate-limit / lockout indicator (populated by AuthController::login on failed attempt)
$remaining      = Session::getFlash('login_remaining', null);
$maxAttempts    = Session::getFlash('login_max', (int) Config::get('LOGIN_MAX_ATTEMPTS', 5));
$lockedMins     = Session::getFlash('login_locked_mins', null);
?>
<div class="max-w-md mx-auto px-4 py-12">
  <div class="card p-8 animate-scale-in">
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-brand-light rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4.143-4.143a1 1 0 011.293-1.293l2.85 2.85 5.85-5.85a1 1 0 011.293 1.293L11 16z"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l2-5h14l2 5M5 13h14v5H5z M7 18v2 M17 18v2"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Welcome back</h1>
      <p class="text-sm text-slate-500 mt-1">Log in to bid and manage listings.</p>
    </div>

    <form method="post" action="<?= $base ?>/login" class="space-y-4">
      <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
      <div>
        <label for="email" class="form-label">Email</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <input id="email" type="email" name="email" required autofocus class="form-input pl-9" value="<?= e($_POST['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
      </div>
      <div>
        <label for="password" class="form-label">Password</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <input id="password" type="password" name="password" required class="form-input pl-9 pr-9" placeholder="Enter your password">
          <button type="button" onclick="togglePassword('password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
      </div>

      <?php if ($lockedMins !== null): ?>
        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-2.5 text-sm text-red-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
          <span>Account locked. Try again in <strong><?= (int)$lockedMins ?></strong> min.</span>
        </div>
      <?php elseif ($remaining !== null && $remaining > 0): ?>
        <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-2.5 text-sm text-amber-700 flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span><strong><?= (int)$remaining ?></strong> attempt<?= ((int)$remaining === 1 ? '' : 's') ?> left before lockout.</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-1.5" role="progressbar" aria-label="Remaining login attempts">
          <div class="bg-amber-500 h-1.5 rounded-full transition-all" style="width: <?= max(8, (int)round(($remaining / max(1, (int)$maxAttempts)) * 100)) ?>%"></div>
        </div>
      <?php endif; ?>

      <button type="submit" class="btn-primary w-full">Log in</button>
    </form>

    <div class="text-sm text-center mt-4 space-y-1">
      <p><a href="<?= $base ?>/forgot-password" class="text-brand hover:underline">Forgot password?</a></p>
      <p>No account? <a href="<?= $base ?>/register" class="text-brand hover:underline font-medium">Sign up</a></p>
    </div>
  </div>
</div>
