</main>
</div><!-- /pt-16 wrapper (offset for fixed header) -->

<?php if (Auth::check()): ?>
</div><!-- /right column: header + content -->
</div><!-- /sidebar + right column wrapper -->
<?php endif; ?>

<script>window.APP_BASE = <?= json_encode(rtrim(Config::get('APP_URL','/car'),'/')) ?>;</script>
<script src="<?= rtrim(Config::get('APP_URL','/car'),'/') ?>/assets/js/app.js?v=12"></script>
</body>
</html>
