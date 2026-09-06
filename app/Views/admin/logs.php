<?php /** Admin audit logs. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-5xl mx-auto px-4 py-8">
  <div class="card overflow-hidden animate-fade-in-up">
    <table class="data-table">
      <thead>
        <tr><th>Time</th><th>User</th><th>Action</th><th>IP</th><th>Details</th></tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td class="text-slate-500 whitespace-nowrap"><?= e($log['created_at']) ?></td>
            <td class="font-medium"><?= e($log['user_name'] ?? ('#'.$log['user_id'])) ?></td>
            <td><span class="badge badge-info"><?= e($log['action']) ?></span></td>
            <td class="text-slate-500 font-mono text-xs"><?= e($log['ip_address']) ?></td>
            <td class="text-xs text-slate-500 max-w-xs truncate"><?= e($log['details']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
