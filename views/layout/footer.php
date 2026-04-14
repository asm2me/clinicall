
<?php if (Auth::check()): ?>
        </main>
    </div>
</div>

<footer class="footer-bar ample-footer">
    <div class="container-fluid px-4">
        <small class="text-muted">
            &copy; <?php echo date('Y'); ?>
            <?php echo e($cfg['app']['name']); ?> &mdash; Clinical Booking System
            <span class="ms-3 text-muted">v<?php echo CLINICALL_VERSION; ?></span>
        </small>
    </div>
</footer>
<?php else: ?>
</div><!-- /.container-fluid -->

<footer class="footer-bar mt-auto py-3 bg-light border-top">
    <div class="container-fluid px-4">
        <small class="text-muted">
            &copy; <?php echo date('Y'); ?>
            <?php echo e($cfg['app']['name']); ?> &mdash; Clinical Booking System
            <span class="ms-3 text-muted">v<?php echo CLINICALL_VERSION; ?></span>
        </small>
    </div>
</footer>
<?php endif; ?>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- ClinicAll JS -->
<script src="<?php echo e($cfg['app']['url']); ?>/assets/js/app.js"></script>

<?php if (isset($page_scripts)): ?>
<script><?php echo $page_scripts; ?></script>
<?php endif; ?>

</body>
</html>
