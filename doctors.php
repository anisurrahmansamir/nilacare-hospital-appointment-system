<?php
require 'config.php';
$pageTitle = "Doctors";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow">Clinical team</span>
        <h1>Our Doctors</h1>
        <p class="muted">Choose a doctor and book only within the schedule shown.</p>
    </div>
    <a href="index.php" class="btn btn-secondary">← Hospital Home</a>
</div>

<div class="card-grid">
<?php
$result = $conn->query("SELECT * FROM doctors WHERE status='active'");
while ($doc = $result->fetch_assoc()) {
    $doctorInitial = strtoupper(substr(trim(preg_replace('/^Dr\\.?\\s*/i', '', $doc['name'])), 0, 1));
    echo "<div class='card doctor-card'>";
    echo "<div class='doctor-avatar'>" . htmlspecialchars($doctorInitial ?: 'D') . "</div>";
    echo "<h3>" . htmlspecialchars($doc['name']) . "</h3>";
    echo "<p class='specialty'>" . htmlspecialchars($doc['specialization']) . "</p>";
    echo "<div class='schedule-line'><span>Available days: " . htmlspecialchars($doc['available_day']) . "</span><span>Available time: " . htmlspecialchars($doc['available_time']) . "</span></div>";
    echo "<a class='btn' href='book_appointment.php?doctor_id=" . $doc['id'] . "'>Book Appointment</a>";
    echo "</div>";
}
?>
</div>

<?php include 'includes/footer.php'; ?>
