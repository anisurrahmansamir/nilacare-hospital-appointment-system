<?php
require 'config.php';
$pageTitle = "My Appointments";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

// Handle cancellation
if (isset($_GET['cancel'])) {
    $appt_id = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE appointments SET status='cancelled' WHERE id = ? AND patient_id = ? AND status IN ('pending','approved')");
    $stmt->bind_param("ii", $appt_id, $patient_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

include 'includes/header.php';

$stmt = $conn->prepare("
    SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, d.name AS doctor_name, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="page-header">
    <div>
        <span class="eyebrow"><?php echo htmlspecialchars($hospitalName); ?></span>
        <h1>My Appointments</h1>
        <p class="muted">View, edit, or cancel your active hospital bookings.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-secondary">Hospital Home</a>
        <a href="book_appointment.php" class="btn">+ Book Appointment</a>
    </div>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Appointment updated successfully and sent for approval.</div><?php endif; ?>
<?php if (isset($_GET['edit_unavailable'])): ?><div class="alert alert-error">Completed or cancelled appointments cannot be edited.</div><?php endif; ?>

<div class="table-wrap">
<table>
<tr>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Reason</th>
    <th>Status</th>
    <th>Action</th>
</tr>
<?php if ($result->num_rows === 0): ?>
<tr><td colspan="6">You have no appointments yet.</td></tr>
<?php endif; ?>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['doctor_name']); ?><br><span class="muted"><?php echo htmlspecialchars($row['specialization']); ?></span></td>
    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
    <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
    <td><?php echo htmlspecialchars($row['reason']); ?></td>
    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
    <td>
        <?php if ($row['status'] === 'pending' || $row['status'] === 'approved'): ?>
            <div class="actions-wrap">
                <a class="action-link edit" href="edit_appointment.php?id=<?php echo $row['id']; ?>">Edit</a>
                <a class="action-link delete" href="dashboard.php?cancel=<?php echo $row['id']; ?>" onclick="return confirm('Cancel this appointment?');">Cancel</a>
            </div>
        <?php else: ?>
            &mdash;
        <?php endif; ?>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php include 'includes/footer.php'; ?>
