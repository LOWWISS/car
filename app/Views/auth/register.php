<?php /** Register page. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-md mx-auto px-4 py-12">
  <div class="card p-8 animate-scale-in">
    <div class="text-center mb-6">
      <div class="w-14 h-14 bg-brand-light rounded-2xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
      </div>
      <h1 class="text-2xl font-bold text-slate-900">Create your account</h1>
      <p class="text-sm text-slate-500 mt-1">Bid as a buyer, or list cars as a seller.</p>
    </div>

    <form method="post" action="<?= $base ?>/register" class="space-y-4 mt-6">
      <input type="hidden" name="_csrf_token" value="<?= e($csrf) ?>">
      <div>
        <label for="name" class="form-label">Full name</label>
        <input id="name" type="text" name="name" required autofocus class="form-input" value="<?= e($_POST['name'] ?? '') ?>">
      </div>
      <div>
        <label for="email" class="form-label">Email</label>
        <input id="email" type="email" name="email" required class="form-input" value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div>
        <label for="phone" class="form-label">Phone (optional)</label>
        <input id="phone" type="text" name="phone" class="form-input" value="<?= e($_POST['phone'] ?? '') ?>">
      </div>
      <div>
        <label class="form-label">Account type</label>
        <div class="grid grid-cols-2 gap-2">
          <label class="flex items-center gap-2 p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50">
            <input type="radio" name="role" value="buyer" checked class="text-brand focus:ring-brand"> <span class="text-sm">Buyer / Bidder</span>
          </label>
          <label class="flex items-center gap-2 p-3 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50">
            <input type="radio" name="role" value="seller" class="text-brand focus:ring-brand"> <span class="text-sm">Seller</span>
          </label>
        </div>
      </div>
      <div>
        <label for="password" class="form-label">Password (min 8 chars)</label>
        <input id="password" type="password" name="password" required minlength="8" class="form-input">
      </div>
      <div>
        <label for="password_confirm" class="form-label">Confirm password</label>
        <input id="password_confirm" type="password" name="password_confirm" required class="form-input">
      </div>
      <button type="submit" class="btn-primary w-full">Create account</button>
    </form>

    <p class="text-sm text-center mt-4">Already have an account? <a href="<?= $base ?>/login" class="text-brand hover:underline">Log in</a></p>
  </div>
</div>
