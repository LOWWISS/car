<?php /** Admin user management. */ $base = rtrim(Config::get('APP_URL','/car'),'/'); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="card overflow-hidden animate-fade-in-up">
    <table class="data-table">
      <thead>
        <tr>
          <th>User</th><th>Role</th>
          <th>Verified</th><th class="text-right">Listings</th>
          <th class="text-right">Bids</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-full bg-brand text-white flex items-center justify-center text-sm font-semibold shrink-0"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span>
                <div>
                  <div class="font-medium text-slate-800"><?= e($u['name']) ?></div>
                  <div class="text-xs text-slate-500"><?= e($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td>
              <?php if ($u['role'] === 'admin'): ?>
                <span class="badge badge-info" title="Admin roles are locked and cannot be changed">admin</span>
              <?php else: ?>
                <form method="post" action="<?= $base ?>/admin/users/role/<?= (int)$u['id'] ?>" class="inline">
                  <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
                  <select name="role" onchange="this.form.submit()" class="text-xs border border-slate-300 rounded px-2 py-1.5 focus:ring-2 focus:ring-brand outline-none">
                    <?php foreach (['buyer','seller'] as $r): ?>
                      <option value="<?= e($r) ?>" <?= $u['role']===$r?'selected':'' ?>><?= e($r) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              <?php endif; ?>
            </td>
            <td><?= $u['email_verified'] ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-warning">Pending</span>' ?></td>
            <td class="text-right font-medium"><?= (int)$u['listings'] ?></td>
            <td class="text-right font-medium"><?= (int)$u['bids'] ?></td>
            <td><?= $u['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Disabled</span>' ?></td>
            <td>
              <form method="post" action="<?= $base ?>/admin/users/toggle/<?= (int)$u['id'] ?>">
                <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
                <button class="<?= $u['is_active'] ? 'btn-danger' : 'btn-success' ?> text-xs"><?= $u['is_active'] ? 'Disable' : 'Enable' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
