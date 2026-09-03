<?php
require '../config.php';
$pageTitle = "Admin Dashboard";
$activePage = "dashboard";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$totalDoctors = $conn->query("SELECT COUNT(*) AS c FROM doctors")->fetch_assoc()['c'];
$totalPatients = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalAppointments = $conn->query("SELECT COUNT(*) AS c FROM appointments")->fetch_assoc()['c'];
$pendingAppointments = $conn->query("SELECT COUNT(*) AS c FROM appointments WHERE status='pending'")->fetch_assoc()['c'];

include 'includes/header.php';
?>

<div class="admin-page-heading">
    <div>
        <span class="eyebrow">Administration overview</span>
        <h1>Admin Dashboard</h1>
        <p class="muted">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>. Manage <?php echo htmlspecialchars($hospitalName); ?> from one place.</p>
    </div>
    <a href="../index.php" class="btn btn-secondary">Hospital Home</a>
</div>

<div class="card-grid dashboard-stats">
    <div class="stat-card stat-teal"><div class="stat-card-top"><span>Patients</span><span class="stat-symbol">P</span></div><h3><?php echo $totalPatients; ?></h3><p>Registered patient accounts</p></div>
    <div class="stat-card stat-blue"><div class="stat-card-top"><span>Doctors</span><span class="stat-symbol">D</span></div><h3><?php echo $totalDoctors; ?></h3><p>Doctors in the hospital system</p></div>
    <div class="stat-card stat-purple"><div class="stat-card-top"><span>Appointments</span><span class="stat-symbol">A</span></div><h3><?php echo $totalAppointments; ?></h3><p>Total appointment records</p></div>
    <div class="stat-card stat-orange"><div class="stat-card-top"><span>Needs Review</span><span class="stat-symbol">!</span></div><h3><?php echo $pendingAppointments; ?></h3><p>Appointments pending approval</p></div>
</div>

<div class="panel dashboard-panel">
    <div class="panel-heading">
        <div><span class="eyebrow">Common tasks</span><h2>Quick Actions</h2></div>
        <p>Choose an area to manage hospital records.</p>
    </div>
    <div class="quick-action-grid">
        <a href="patients.php" class="quick-action-card"><span class="quick-icon">P</span><span><strong>Manage Patients</strong><small>View and update patient accounts</small></span><b>→</b></a>
        <a href="doctors.php" class="quick-action-card"><span class="quick-icon">D</span><span><strong>Manage Doctors</strong><small>Edit doctors and availability</small></span><b>→</b></a>
        <a href="appointments.php" class="quick-action-card"><span class="quick-icon">A</span><span><strong>Appointments</strong><small>Review and edit all bookings</small></span><b>→</b></a>
        <a href="book_appointment.php" class="quick-action-card"><span class="quick-icon">+</span><span><strong>Book for Patient</strong><small>Create an approved staff booking</small></span><b>→</b></a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
