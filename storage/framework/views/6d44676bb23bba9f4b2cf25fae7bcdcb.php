<?php $__env->startSection('title', 'Customer Login | Grafika South'); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('auth.portal', [
        'mode'         => 'login',
        'showSwitcher' => !request()->routeIs('admin.login'),
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/auth/login.blade.php ENDPATH**/ ?>