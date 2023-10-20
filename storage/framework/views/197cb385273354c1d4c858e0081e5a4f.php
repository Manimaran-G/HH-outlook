<!-- Copyright (c) Microsoft Corporation.
     Licensed under the MIT License. -->

     

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
  <?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\_WorkSpace\HH-outlook-main\resources\views/welcome.blade.php ENDPATH**/ ?>