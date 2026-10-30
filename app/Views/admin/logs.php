<?php /** Admin audit logs — dark, filtered, printable. */
$base = rtrim(Config::get('APP_URL','/car'),'/');
$f = $filters;
$thCls = 'text-left text-[11px] font-semibold uppercase tracking-wider text-zinc-500 px-4 py-3';
$tdCls = 'px-4 py-3.5 text-sm';
$inputCls = 'dark-field w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-zinc-200 placeholder-zinc-500 focus:ring-2 focus:ring-brand/60 focus:border-brand/60 outline-none transition';
$labelCls = 'block text-[11px] font-semibold uppercase tracking-wider text-zinc-500 mb-1.5';

// Action -> pill color (spot problems at a glance)
$actionCls = function (string $a): string {
    $a = strtolower($a);
    if (str_contains($a, 'reject') || str_contains($a, 'delete') || str_contains($a, 'cancel') || str_contains($a, 'fail') || str_contains($a, 'disabled'))
        return 'bg-red-400/10 text-red-300';
    if (str_contains($a, 'approve') || str_contains($a, 'complete') || str_contains($a, 'success') || str_contains($a, 'win') || str_contains($a, 'sold'))
        return 'bg-emerald-400/10 text-emerald-300';
    if (str_contains($a, 'login') || str_contains($a, 'register'))
        return 'bg-brand/25 text-indigo-300';
    if (str_contains($a, 'bid') || str_contains($a, 'buy'))
        return 'bg-amber-400/10 text-amber-300';
    return 'bg-white/10 text-zinc-300';
};
?>
<div class="bg-[#0f0f12] min-h-[calc(100vh-4rem)] text-zinc-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-4 mb-5 animate-fade-in">
      <div>
        <h1 class="text-xl font-semibold text-white">Audit Logs</h1>
        <p class="text-sm text-zinc-500 mt-0.5"><?= count($logs) ?> event<?= count($logs)===1?'':'s' ?><?= array_filter($f) ? ' matching filters' : '' ?></p>
      </div>
      <div class="no-print flex flex-wrap items-center gap-2">
        <input type="date" data-report-date="from" value="<?= e($f['from']) ?>" title="Report from"
               class="dark-field rounded-lg border border-white/10 bg-white/5 px-2.5 py-2 text-sm text-zinc-200 focus:ring-2 focus:ring-brand/60 outline-none transition">
        <span class="text-zinc-600 text-sm">–</span>
        <input type="date" data-report-date="to" value="<?= e($f['to']) ?>" title="Report to"
               class="dark-field rounded-lg border border-white/10 bg-white/5 px-2.5 py-2 text-sm text-zinc-200 focus:ring-2 focus:ring-brand/60 outline-none transition">
        <select data-print-range class="dark-field rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-zinc-200 focus:ring-2 focus:ring-brand/60 outline-none transition">
          <option value="">Report range…</option>
          <option value="today">Today</option>
          <option value="7">Last 7 days</option>
          <option value="30">Last 30 days</option>
          <option value="month">This month</option>
          <option value="all">All time</option>
        </select>
        <button type="button" data-print class="inline-flex items-center gap-1.5 rounded-lg border border-white/10 bg-white/[0.03] px-3.5 py-2 text-sm text-zinc-200 hover:bg-white/[0.07] transition">
          <svg class="w-4 h-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Print report
        </button>
      </div>
    </div>

    <!-- Filter bar -->
    <form method="get" action="<?= $base ?>/admin/logs" data-autosubmit
          class="no-print rounded-xl border border-white/[0.07] bg-white/[0.02] p-4 mb-6 animate-fade-in-up">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="sm:col-span-2 lg:col-span-1">
          <label class="<?= $labelCls ?>">Search</label>
          <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="User, action, details, IP..." class="<?= $inputCls ?>">
        </div>
        <div>
          <label class="<?= $labelCls ?>">Action</label>
          <select name="action" class="<?= $inputCls ?>">
            <option value="">All actions</option>
            <?php foreach ($actions as $a): ?>
              <option value="<?= e($a) ?>" <?= $f['action']===$a?'selected':'' ?>><?= e($a) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="<?= $labelCls ?>">From</label>
          <input type="date" name="from" value="<?= e($f['from']) ?>" class="<?= $inputCls ?>">
        </div>
        <div>
          <label class="<?= $labelCls ?>">To</label>
          <input type="date" name="to" value="<?= e($f['to']) ?>" class="<?= $inputCls ?>">
        </div>
      </div>
      <div class="flex items-center gap-2 mt-4 pt-3 border-t border-white/[0.07]">
        <a href="<?= $base ?>/admin/logs" data-pjax class="rounded-lg border border-white/10 px-4 py-2 text-sm text-zinc-300 hover:bg-white/10 transition">Reset</a>
        <span class="text-xs text-zinc-600">Filters apply automatically</span>
      </div>
    </form>

    <?php if (empty($logs)): ?>
      <div class="rounded-xl border border-white/[0.07] py-16 px-6 text-center animate-fade-in-up">
        <div class="w-14 h-14 mx-auto mb-4 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center text-zinc-500">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="text-base font-semibold text-zinc-200"><?= array_filter($f) ? 'No events match your filters' : 'No audit events yet' ?></h3>
        <p class="mt-1 text-sm text-zinc-500"><?= array_filter($f) ? 'Try a different search or date range.' : 'Activity across the platform will be logged here.' ?></p>
      </div>
    <?php else: ?>
      <!-- Printable report area -->
      <div class="print-area">
        <?php
          // Report summary stats
          $counts = array_count_values(array_column($logs, 'action'));
          arsort($counts);
          $summary = array_slice($counts, 0, 6, true);
          $periodFrom = end($logs)['created_at'] ?? null;
          $periodTo   = $logs[0]['created_at'] ?? null;
        ?>
        <div class="hidden print:block mb-4" style="border-bottom:2px solid #000; padding-bottom:10px;">
          <div class="text-xl font-bold">CarAuction — Audit Log Report</div>
          <div class="text-xs mt-1">
            Generated <?= e(date('F j, Y · g:i A')) ?>
            · <?= count($logs) ?> event<?= count($logs)===1?'':'s' ?>
            <?php if ($periodFrom && $periodTo): ?>
              · Period covered: <?= e(date('M j, Y', strtotime($periodFrom))) ?> – <?= e(date('M j, Y', strtotime($periodTo))) ?>
            <?php endif; ?>
          </div>
          <?php if (array_filter($f)): ?>
            <div class="text-xs mt-0.5">
              Filters applied:
              <?= e(implode('  ·  ', array_filter([
                $f['q']      !== '' ? 'Search "'.$f['q'].'"' : '',
                $f['action'] !== '' ? 'Action "'.$f['action'].'"' : '',
                $f['from']   !== '' ? 'From '.$f['from'] : '',
                $f['to']     !== '' ? 'To '.$f['to'] : '',
              ]))) ?>
            </div>
          <?php endif; ?>
          <?php if ($summary): ?>
            <div class="text-xs mt-1.5">
              <strong>Summary:</strong>
              <?= e(implode('   ', array_map(fn($a,$c)=> $a.' ×'.$c, array_keys($summary), $summary))) ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="rounded-xl border border-white/[0.07] overflow-hidden animate-fade-in-up">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-white/[0.03]">
                <tr>
                  <th class="<?= $thCls ?>">Time</th>
                  <th class="<?= $thCls ?>">User</th>
                  <th class="<?= $thCls ?>">Action</th>
                  <th class="<?= $thCls ?>">IP</th>
                  <th class="<?= $thCls ?>">Details</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.05]">
                <?php foreach ($logs as $log): ?>
                  <tr class="hover:bg-white/[0.03] transition">
                    <td class="<?= $tdCls ?> text-zinc-500 whitespace-nowrap font-mono text-xs"><?= e($log['created_at']) ?></td>
                    <td class="<?= $tdCls ?> font-medium text-zinc-100"><?= e($log['user_name'] ?? ('#'.$log['user_id'])) ?></td>
                    <td class="<?= $tdCls ?>">
                      <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[11px] font-semibold <?= $actionCls($log['action']) ?>"><?= e($log['action']) ?></span>
                    </td>
                    <td class="<?= $tdCls ?> text-zinc-500 font-mono text-xs"><?= e($log['ip_address']) ?></td>
                    <td class="<?= $tdCls ?> text-xs text-zinc-500 max-w-xs truncate print:max-w-none print:whitespace-normal" title="<?= e($log['details']) ?>"><?= e($log['details']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Report footer (print only) -->
        <div class="hidden print:flex mt-6 text-xs justify-between items-end" style="page-break-inside:avoid;">
          <div>
            End of report — <?= count($logs) ?> event<?= count($logs)===1?'':'s' ?> · CarAuction Audit System
          </div>
          <div style="text-align:center;">
            <div style="border-top:1px solid #000; width:180px; padding-top:3px;">Reviewed by (signature)</div>
            <div class="mt-2">Date: ____________</div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
