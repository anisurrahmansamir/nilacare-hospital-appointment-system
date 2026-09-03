<?php
require '../config.php';
$pageTitle = "Manage Appointments";
$activePage = "appointments";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Update appointment status
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $validActions = ['approved', 'cancelled', 'completed'];
    if (in_array($action, $validActions)) {
        $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
    }
    $redirect = "appointments.php";
    if (isset($_GET['patient_id'])) {
        $redirect .= "?patient_id=" . (int)$_GET['patient_id'];
    }
    header("Location: $redirect");
    exit;
}

// Optional filter by patient (linked from the Patients page)
$filterPatientId = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;
$filterPatientName = "";

if ($filterPatientId > 0) {
    $stmt = $conn->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status,
               u.name AS patient_name, u.phone, d.name AS doctor_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->bind_param("i", $filterPatientId);
    $stmt->execute();
    $result = $stmt->get_result();

    $nameStmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $nameStmt->bind_param("i", $filterPatientId);
    $nameStmt->execute();
    $nameResult = $nameStmt->get_result();
    if ($row = $nameResult->fetch_assoc()) {
        $filterPatientName = $row['name'];
    }
} else {
    $result = $conn->query("
        SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status,
               u.name AS patient_name, u.phone, d.name AS doctor_name
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
}

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow"><?php echo htmlspecialchars($hospitalName); ?></span>
        <h1>Manage Appointments</h1>
    </div>
    <div>
        <a href="../index.php" class="btn btn-secondary">Hospital Home</a>
        <a href="book_appointment.php" class="btn">+ Book for Patient</a>
    </div>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success">Appointment details updated successfully.</div><?php endif; ?>

<?php if ($filterPatientId > 0): ?>
    <p class="muted">Showing appointments for: <b><?php echo htmlspecialchars($filterPatientName); ?></b>
    &nbsp;|&nbsp; <a href="appointments.php">Clear filter</a></p>
<?php else: ?>
    <p class="muted">All patient appointments across all doctors.</p>
<?php endif; ?>

<div class="panel">
<div class="table-wrap">
<table>
<tr>
    <th>Patient</th>
    <th>Doctor</th>
    <th>Date</th>
    <th>Time</th>
    <th>Reason</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
<?php if ($result->num_rows === 0): ?>
<tr><td colspan="7">No appointments found.</td></tr>
<?php endif; ?>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['patient_name']); ?><br><span class="muted"><?php echo htmlspecialchars($row['phone']); ?></span></td>
    <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
    <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
    <td><?php echo htmlspecialchars($row['reason']); ?></td>
    <td><span class="status status-<?php echo $row['status']; ?>"><?php echo $row['status']; ?></span></td>
    <td>
        <div class="actions-wrap">
        <a class="action-link edit" href="edit_appointment.php?id=<?php echo $row['id']; ?>">Edit Details</a>
        <?php $qs = "id=" . $row['id'] . ($filterPatientId > 0 ? "&patient_id=$filterPatientId" : ""); ?>
        <?php if ($row['status'] === 'pending'): ?>
            <a class="action-link edit" href="appointments.php?action=approved&<?php echo $qs; ?>">Approve</a>
            <a class="action-link delete" href="appointments.php?action=cancelled&<?php echo $qs; ?>">Cancel</a>
        <?php elseif ($row['status'] === 'approved'): ?>
            <a class="action-link edit" href="appointments.php?action=completed&<?php echo $qs; ?>">Mark Completed</a>
            <a class="action-link delete" href="appointments.php?action=cancelled&<?php echo $qs; ?>">Cancel</a>
        <?php else: ?>
            <span class="muted">No status action</span>
        <?php endif; ?>
        </div>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

<?php include 'includes/footer.php'; ?>
