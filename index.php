<?php
require 'config.php';
$pageTitle = "Home";
include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">Welcome to <?php echo htmlspecialchars($hospitalName); ?></span>
        <h1>Quality care begins with an easier appointment.</h1>
        <p>Choose a doctor, select a date that matches the doctor's schedule, and manage your visit online.</p>
        <div class="hero-actions">
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn">Create Patient Account</a>
            <a href="login.php" class="btn btn-light">Patient Login</a>
        <?php else: ?>
            <a href="book_appointment.php" class="btn">Book an Appointment</a>
            <a href="dashboard.php" class="btn btn-light">View My Appointments</a>
        <?php endif; ?>
        </div>
        <div class="trust-row">
            <span>✓ Schedule-based booking</span>
            <span>✓ Secure patient accounts</span>
            <span>✓ Easy appointment management</span>
        </div>
    </div>
    <aside class="hero-card">
        <div class="hospital-cross">+</div>
        <h2>Patient Services</h2>
        <p>General outpatient appointment portal</p>
        <ul>
            <li>View doctors and specialties</li>
            <li>Book available dates and times</li>
            <li>Edit or cancel active bookings</li>
            <li>Check approval status online</li>
        </ul>
        <span class="location-pill">📍 <?php echo htmlspecialchars($hospitalLocation); ?></span>
    </aside>
</section>

<section class="section-heading">
    <div><span class="eyebrow">Medical team</span><h2>Meet Our Doctors</h2></div>
    <?php if (isset($_SESSION['user_id'])): ?><a href="doctors.php" class="text-link">View all doctors →</a><?php endif; ?>
</section>
<div class="card-grid">
<?php
$result = $conn->query("SELECT * FROM doctors WHERE status='active'");
while ($doc = $result->fetch_assoc()) {
    echo "<article class='card doctor-card'>";
    $doctorInitial = strtoupper(substr(trim(preg_replace('/^Dr\\.?\\s*/i', '', $doc['name'])), 0, 1));
    echo "<div class='doctor-avatar'>" . htmlspecialchars($doctorInitial ?: 'D') . "</div>";
    echo "<h3>" . htmlspecialchars($doc['name']) . "</h3>";
    echo "<p class='specialty'>" . htmlspecialchars($doc['specialization']) . "</p>";
    echo "<div class='schedule-line'><span>📅 " . htmlspecialchars($doc['available_day']) . "</span><span>🕒 " . htmlspecialchars($doc['available_time']) . "</span></div>";
    if (isset($_SESSION['user_id'])) {
        echo "<a class='btn btn-sm' href='book_appointment.php?doctor_id=" . $doc['id'] . "'>Book Appointment</a>";
    }
    echo "</article>";
}
?>
</div>

<section class="care-strip">
    <div><strong>1</strong><span>Choose a doctor</span></div>
    <div><strong>2</strong><span>Select an available slot</span></div>
    <div><strong>3</strong><span>Receive booking status</span></div>
</section>

<?php include 'includes/footer.php'; ?>
