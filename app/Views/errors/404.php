<?php /** 404 page. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-md mx-auto text-center px-4 py-24">
  <div class="text-7xl font-bold text-brand">404</div>
  <h1 class="text-2xl font-bold text-slate-900 mt-4">Page not found</h1>
  <p class="text-slate-500 mt-2"><?= e($message ?? 'The page you requested does not exist.') ?></p>
  <a href="<?= $base ?>/" class="btn-primary inline-block mt-6">Back home</a>
</div>
