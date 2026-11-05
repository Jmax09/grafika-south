<?php $__env->startSection('title', 'Order Management - Staff Admin'); ?>

<?php $__env->startSection('content'); ?>


<?php echo $__env->make('components.orders-table', [
    'orders'       => $orders,
    'isSuperAdmin' => false,
    'indexRoute'   => 'admin.orders.index',
], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\grafika-south\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>