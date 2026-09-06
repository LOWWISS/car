<?php /** 500 page. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-md mx-auto text-center px-4 py-24">
  <div class="text-7xl font-bold text-slate-400">500</div>
  <h1 class="text-2xl font-bold text-slate-900 mt-4">Something went wrong</h1>
  <p class="text-slate-500 mt-2"><?= e($message ?? 'An unexpected error occurred. Please try again later.') ?></p>
  <a href="<?= $base ?>/" class="btn-primary inline-block mt-6">Back home</a>
</div>
