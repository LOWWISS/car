<?php /** Admin user management — dark, consistent with the dashboard chrome. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$thCls = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$tdCls = 'px-4 py-3.5 text-sm';
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">Users</h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= count($users) ?> registered account<?= count($users)===1?'':'s' ?></p>
      </div>
    </div>

    <!-- Table card -->
    <div class="rounded-xl border border-white/[0.07] overflow-hidden animate-fade-in-up">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-white/[0.03]">
            <tr>
              <th class="<?= $thCls ?>">User</th>
              <th class="<?= $thCls ?>">Role</th>
              <th class="<?= $thCls ?>">Verified</th>
              <th class="<?= $thCls ?> text-right">Listings</th>
              <th class="<?= $thCls ?> text-right">Bids</th>
              <th class="<?= $thCls ?>">Status</th>
              <th class="<?= $thCls ?> text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.05]">
            <?php foreach ($users as $u): ?>
              <tr class="hover:bg-white/[0.03] transition">
                <td class="<?= $tdCls ?>">
                  <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-lg bg-gradient-to-br from-brand to-indigo-700 text-white flex items-center justify-center text-sm font-semibold shrink-0"><?= e(strtoupper(substr($u['name'], 0, 1))) ?></span>
                    <div class="min-w-0">
                      <div class="font-medium text-zinc-100 truncate"><?= e($u['name']) ?></div>
                      <div class="text-xs text-zinc-500 truncate"><?= e($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="<?= $tdCls ?>">
                  <?php if ($u['role'] === 'admin'): ?>
                    <span class="inline-flex items-center rounded-md bg-brand/25 px-1.5 py-0.5 text-[11px] font-semibold text-indigo-300" title="Admin roles are locked and cannot be changed">admin</span>
                  <?php else: ?>
                    <form method="post" action="<?= $base ?>/admin/users/role/<?= (int)$u['id'] ?>" class="inline">
                      <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
                      <select name="role" onchange="this.form.submit()"
                              class="dark-field rounded-lg border border-white/10 bg-white/5 px-2 py-1 text-xs text-zinc-200 focus:ring-2 focus:ring-brand/60 focus:border-brand/60 outline-none transition">
                        <?php foreach (['buyer','seller'] as $r): ?>
                          <option value="<?= e($r) ?>" <?= $u['role']===$r?'selected':'' ?>><?= e($r) ?></option>
                        <?php endforeach; ?>
                      </select>
                    </form>
                  <?php endif; ?>
                </td>
                <td class="<?= $tdCls ?>">
                  <?php if ($u['email_verified']): ?>
                    <span class="inline-flex items-center rounded-md bg-emerald-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-300">Verified</span>
                  <?php else: ?>
                    <span class="inline-flex items-center rounded-md bg-amber-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-amber-300">Pending</span>
                  <?php endif; ?>
                </td>
                <td class="<?= $tdCls ?> text-right font-medium text-zinc-200"><?= (int)$u['listings'] ?></td>
                <td class="<?= $tdCls ?> text-right font-medium text-zinc-200"><?= (int)$u['bids'] ?></td>
                <td class="<?= $tdCls ?>">
                  <?php if ($u['is_active']): ?>
                    <span class="inline-flex items-center rounded-md bg-emerald-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-emerald-300">Active</span>
                  <?php else: ?>
                    <span class="inline-flex items-center rounded-md bg-red-400/10 px-1.5 py-0.5 text-[11px] font-semibold text-red-300">Disabled</span>
                  <?php endif; ?>
                </td>
                <td class="<?= $tdCls ?> text-right">
                  <form method="post" action="<?= $base ?>/admin/users/toggle/<?= (int)$u['id'] ?>"
                        data-confirm="<?= $u['is_active'] ? 'Disable this account? They will no longer be able to sign in.' : 'Enable this account?' ?>"
                        data-confirm-text="<?= $u['is_active'] ? 'Disable' : 'Enable' ?>">
                    <input type="hidden" name="_csrf_token" value="<?= e(Csrf::token()) ?>">
                    <button class="rounded-lg border px-3 py-1.5 text-xs font-medium transition <?= $u['is_active']
                        ? 'border-red-400/30 text-red-300 hover:bg-red-500/10'
                        : 'border-emerald-400/30 text-emerald-300 hover:bg-emerald-500/10' ?>">
                      <?= $u['is_active'] ? 'Disable' : 'Enable' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
