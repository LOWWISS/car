<?php /** Set new password. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-md mx-auto px-4 py-12">
  <div class="card p-8 animate-scale-in">
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Set a new password</h1>
    </div>
    <form method="post" action="<?= $base ?>/reset-password" class="space-y-4">
      <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div>
        <label for="password" class="form-label">New password (min 8 chars)</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          <input id="password" type="password" name="password" required minlength="8" class="form-input pl-9" placeholder="Enter new password">
        </div>
      </div>
      <div>
        <label for="password_confirm" class="form-label">Confirm new password</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          <input id="password_confirm" type="password" name="password_confirm" required class="form-input pl-9" placeholder="Confirm new password">
        </div>
      </div>
      <button type="submit" class="btn-primary w-full">Update password</button>
    </form>
  </div>
</div>
