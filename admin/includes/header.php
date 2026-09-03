<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - " . htmlspecialchars($hospitalName) : "Admin Panel - " . htmlspecialchars($hospitalName); ?></title>
<link rel="stylesheet" href="../assets/style.css?v=20260805-4">
</head>
<body class="admin-page">
<div class="admin-wrapper">
    <aside class="sidebar">
        <div class="sidebar-brand"><span class="brand-mark">+</span><span><?php echo htmlspecialchars($hospitalName); ?><small>Administration Portal</small></span></div>
        <nav class="sidebar-nav" aria-label="Administration navigation">
            <span class="sidebar-section-label">Overview</span>
            <a href="dashboard.php" class="<?php echo (isset($activePage) && $activePage === 'dashboard') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">▦</span><span>Dashboard</span></a>
            <span class="sidebar-section-label">Management</span>
            <a href="patients.php" class="<?php echo (isset($activePage) && $activePage === 'patients') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">♙</span><span>Patients</span></a>
            <a href="doctors.php" class="<?php echo (isset($activePage) && $activePage === 'doctors') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">✚</span><span>Doctors</span></a>
            <a href="appointments.php" class="<?php echo (isset($activePage) && $activePage === 'appointments') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">□</span><span>Appointments</span></a>
            <a href="book_appointment.php" class="<?php echo (isset($activePage) && $activePage === 'book') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">＋</span><span>Book for Patient</span></a>
            <span class="sidebar-section-label">Account</span>
            <a href="profile.php" class="<?php echo (isset($activePage) && $activePage === 'profile') ? 'active' : ''; ?>"><span class="sidebar-icon" aria-hidden="true">⚙</span><span>Admin Profile</span></a>
            <a href="../index.php"><span class="sidebar-icon" aria-hidden="true">⌂</span><span>Hospital Home</span></a>
            <a href="logout.php" class="logout-link"><span class="sidebar-icon" aria-hidden="true">↪</span><span>Logout</span></a>
        </nav>
        <div class="sidebar-user">
            <span><?php echo htmlspecialchars(strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1))); ?></span>
            <div><strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></strong><small>Administrator</small></div>
        </div>
    </aside>
    <div class="admin-content">
