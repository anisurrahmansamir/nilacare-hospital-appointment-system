<?php
require '../config.php';
$pageTitle = "Manage Patients";
$activePage = "patients";
$success = "";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Delete patient (their appointments are removed automatically via ON DELETE CASCADE)
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: patients.php?deleted=1");
    exit;
}

if (isset($_GET['deleted'])) {
    $success = "Patient record deleted successfully.";
}
if (isset($_GET['added'])) {
    $success = "Patient added successfully.";
}
if (isset($_GET['updated'])) {
    $success = "Patient details updated successfully.";
}

include 'includes/header.php';

// Get patient list with their appointment count
$result = $conn->query("
    SELECT u.id, u.name, u.email, u.phone, u.created_at,
           COUNT(a.id) AS appointment_count
    FROM users u
    LEFT JOIN appointments a ON a.patient_id = u.id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
?>

<h1>Manage Patients</h1>
<p class="muted">View, add, edit, or remove patient accounts.</p>

<?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

<a href="add_patient.php" class="btn">+ Add New Patient</a>

<div class="panel">
<div class="table-wrap">
<table>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Phone</th>
    <th>Registered</th>
    <th>Appointments</th>
    <th>Actions</th>
</tr>
<?php if ($result->num_rows === 0): ?>
<tr><td colspan="6">No patients registered yet.</td></tr>
<?php endif; ?>
<?php while ($p = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($p['name']); ?></td>
    <td><?php echo htmlspecialchars($p['email']); ?></td>
    <td><?php echo htmlspecialchars($p['phone']); ?></td>
    <td><?php echo date('d M Y', strtotime($p['created_at'])); ?></td>
    <td><a href="appointments.php?patient_id=<?php echo $p['id']; ?>"><?php echo $p['appointment_count']; ?> appointment(s)</a></td>
    <td>
        <a class="action-link edit" href="edit_patient.php?id=<?php echo $p['id']; ?>">Edit</a>
        <a class="action-link delete" href="patients.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete this patient? This will also delete all their appointments.');">Delete</a>
    </td>
</tr>
<?php endwhile; ?>
</table>
</div>
</div>

<?php include 'includes/footer.php'; ?>
