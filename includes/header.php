<?php $isLoggedIn = isset($_SESSION['user_id']); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <!-- Google Fonts Roboto -->
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap"
    />
    <!-- MDB -->
    <link rel="stylesheet" href="css/mdb.min.css" />
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">FitGear Equipment</a>
    <button
      data-mdb-collapse-init
      class="navbar-toggler"
      type="button"
      data-mdb-target="#navbarNav"
      aria-controls="navbarNav"
      aria-expanded="false"
      aria-label="Toggle navigation"
    >
      <i class="fas fa-bars"></i>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php#equipment">Browse Equipment</a>
        </li>
        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <a class="nav-link" href="equipment.php">Manage Equipment</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="categories.php">Categories</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="comments_moderation.php">Moderate Comments</a>
          </li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($isLoggedIn): ?>
          <li class="nav-item">
            <span class="nav-link">Welcome back!</span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


