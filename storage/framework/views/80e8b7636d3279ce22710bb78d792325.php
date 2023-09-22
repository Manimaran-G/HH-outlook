

<?php $__env->startSection('content'); ?>
<div class="jumbotron">
    <p class="lead"> Microsoft Graph API to access a user's data from PHP</p>
    <?php if(isset($userName)): ?>
        <h4>Welcome <?php echo e($userName); ?>!</h4>
        <p>Use the navigation bar at the top of the page to get started.</p>
    <?php else: ?>
        <a href="/hello" class="btn btn-primary btn-large">Click here to sign in</a>
    <?php endif; ?>
</div>

<?php if(isset($events)): ?>
<div class="container">
    <h2>Events</h2>
    <ul>
        <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
            <strong>Title:</strong> <?php echo e($event['title']); ?><br>
            <strong>Start:</strong> <?php echo e($event['start']); ?><br>
            <strong>End:</strong> <?php echo e($event['end']); ?><br>
        </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Downloads\HH-outlook-main\HH-outlook-main\resources\views/hello.blade.php ENDPATH**/ ?>