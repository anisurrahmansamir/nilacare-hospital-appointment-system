<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - " . htmlspecialchars($hospitalName) : htmlspecialchars($hospitalName); ?></title>
<link rel="stylesheet" href="<?php echo isset($basePath) ? $basePath : ''; ?>assets/style.css?v=20260805-3">
</head>
<body class="<?php echo isset($noContainer) && $noContainer ? 'auth-page' : ''; ?>">
<header class="navbar">
    <div class="nav-inner">
        <a class="brand" href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php" aria-label="Go to hospital homepage">
            <span class="brand-mark" aria-hidden="true">+</span>
            <span class="brand-copy">
                <span class="brand-name"><?php echo htmlspecialchars($hospitalName); ?></span>
                <span class="brand-tagline"><?php echo htmlspecialchars($hospitalTagline); ?></span>
            </span>
        </a>
        <nav class="nav-links <?php echo !isset($_SESSION['user_id']) && !isset($_SESSION['admin_id']) ? 'guest-nav' : ''; ?>" aria-label="Main navigation">
            <a href="<?php echo isset($basePath) ? $basePath : ''; ?>index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>doctors.php">Doctors</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>book_appointment.php">Book Appointment</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>dashboard.php">My Appointments</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>profile.php">My Profile</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>logout.php">Logout</a>
            <?php elseif (isset($_SESSION['admin_id'])): ?>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>admin/dashboard.php">Admin Dashboard</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>admin/profile.php">Admin Profile</a>
                <a href="<?php echo isset($basePath) ? $basePath : ''; ?>admin/logout.php">Logout</a>
            <?php else: ?>
                <a class="<?php echo ($authRole ?? '') === 'patient' ? 'active' : ''; ?>" href="<?php echo isset($basePath) ? $basePath : ''; ?>login.php">Patient Login</a>
                <a class="<?php echo ($authRole ?? '') === 'register' ? 'active' : ''; ?>" href="<?php echo isset($basePath) ? $basePath : ''; ?>register.php">Register</a>
                <a class="staff-link <?php echo ($authRole ?? '') === 'admin' ? 'active' : ''; ?>" href="<?php echo isset($basePath) ? $basePath : ''; ?>admin/login.php">Admin Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<div class="<?php echo isset($noContainer) && $noContainer ? '' : 'container'; ?>">
