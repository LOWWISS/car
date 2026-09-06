<?php /** Forgot password. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-md mx-auto px-4 py-12">
  <div class="card p-8 animate-scale-in">
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-4.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Reset password</h1>
      <p class="text-sm text-slate-500 mt-1">Enter your email to receive a reset link.</p>
    </div>
    <form method="post" action="<?= $base ?>/forgot-password" class="space-y-4">
      <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
      <div>
        <label for="email" class="form-label">Email</label>
        <div class="relative">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
          <input id="email" type="email" name="email" required autofocus class="form-input pl-9" placeholder="you@example.com">
        </div>
      </div>
      <button type="submit" class="btn-primary w-full">Send reset link</button>
    </form>
    <p class="text-sm text-center mt-4"><a href="<?= $base ?>/login" class="text-brand hover:underline">&larr; Back to login</a></p>
  </div>
</div>
