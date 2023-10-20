<!-- Copyright (c) Microsoft Corporation.
     Licensed under the MIT License. -->

     <!DOCTYPE html>
<html>
  <head>
    <title>PHP Graph </title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
      integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh"
      crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
    <link rel="stylesheet" href="<?php echo e(asset('/css/app.css')); ?>">


    <!-- Add these lines to include Bootstrap and jQuery -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  </head>

  <body>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
      <div class="container">
        <a href="/" class="navbar-brand">PHP Graph </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a href="/" class="nav-link <?php echo e($_SERVER['REQUEST_URI'] == '/' ? ' active' : ''); ?>">Home</a>
            </li>
            <?php if(isset($userName)): ?>
              <li class="nav-item" data-turbolinks="false">
                <a href="/calendar" class="nav-link<?php echo e($_SERVER['REQUEST_URI'] == '/calendar' ? ' active' : ''); ?>">Calendar</a>
              </li>
            <?php endif; ?>
          </ul>
          <ul class="navbar-nav justify-content-end">
         <!--   <li class="nav-item">
              <a class="nav-link" href="https://docs.microsoft.com/graph/overview" target="_blank">
                <i class="fas fa-external-link-alt mr-1"></i>Docs
              </a>
            </li>-->
            <?php if(isset($userName)): ?>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                  aria-haspopup="true" aria-expanded="false">
                  <?php if(isset($user_avatar)): ?>
                    <img src="<?php echo e($user_avatar); ?>" class="rounded-circle align-self-center mr-2" style="width: 32px;">
                  <?php else: ?>
                    <i class="far fa-user-circle fa-lg rounded-circle align-self-center mr-2" style="width: 32px;"></i>
                  <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                  <h5 class="dropdown-item-text mb-0"><?php echo e($userName); ?></h5>
                  <p class="dropdown-item-text text-muted mb-0"><?php echo e($userEmail); ?></p>
                  <div class="dropdown-divider"></div>
                  <a href="/signout" class="dropdown-item">Sign Out</a>
                </div>
              </li>
            <?php else: ?>
              <li class="nav-item">
                <a href="/signin" class="nav-link">Sign In</a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>
    <main role="main" class="container">
      <?php if(session('error')): ?>
        <div class="alert alert-danger" role="alert">
          <p class="mb-3"><?php echo e(session('error')); ?></p>
          <?php if(session('errorDetail')): ?>
            <pre class="alert-pre border bg-light p-2"><code><?php echo e(session('errorDetail')); ?></code></pre>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <?php echo $__env->yieldContent('content'); ?>
    </main>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"
      integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n"
      crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
      integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
      crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
      integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
      crossorigin="anonymous"></script>
  </body>
</html><?php /**PATH D:\_WorkSpace\HH-outlook-main\resources\views/layout.blade.php ENDPATH**/ ?>