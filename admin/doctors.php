<?php
require '../config.php';
require '../includes/appointment_helpers.php';
$pageTitle = "Manage Doctors";
$activePage = "doctors";
$error = "";
$success = "";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $name = trim($_POST['name'] ?? '');
    $spec = trim($_POST['specialization'] ?? '');
    $day = trim($_POST['available_day'] ?? '');
    $time = trim($_POST['available_time'] ?? '');

    if ($name === "" || $spec === "" || $day === "" || $time === "") {
        $error = "Please fill in all fields.";
    } elseif (count(parse_available_days($day)) === 0) {
        $error = "Enter valid days such as Mon, Wed, Fri or Mon - Fri.";
    } elseif (parse_available_time_range($time) === null) {
        $error = "Enter a valid time range such as 9:00 AM - 12:00 PM.";
    } else {
        $stmt = $conn->prepare("INSERT INTO doctors (name, specialization, available_day, available_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $spec, $day, $time);
        $stmt->execute();
        header("Location: doctors.php?added=1");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_doctor'])) {
    $id = (int)($_POST['doctor_id'] ?? 0);
    $stmt = $conn->prepare("UPDATE doctors SET status = IF(status='active','inactive','active') WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header("Location: doctors.php?status_changed=1");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doctor'])) {
    $id = (int)($_POST['doctor_id'] ?? 0);
    $countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ?");
    $countStmt->bind_param('i', $id);
    $countStmt->execute();
    $appointmentCount = (int)$countStmt->get_result()->fetch_assoc()['total'];

    if ($appointmentCount > 0) {
        header("Location: doctors.php?in_use=1");
    } else {
        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header("Location: doctors.php?deleted=1");
    }
    exit;
}

if (isset($_GET['added'])) {
    $success = "Doctor and availability schedule added successfully.";
} elseif (isset($_GET['updated'])) {
    $success = "Doctor details and availability updated successfully.";
} elseif (isset($_GET['status_changed'])) {
    $success = "Doctor status updated successfully.";
} elseif (isset($_GET['deleted'])) {
    $success = "Doctor removed successfully.";
}
if (isset($_GET['in_use'])) {
    $error = "This doctor has appointment history and cannot be deleted. Set the doctor to inactive instead.";
}

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <h1>Manage Doctors</h1>
        <p class="muted">Maintain doctor details and the exact days and times patients can book.</p>
    </div>
    <a href="../index.php" class="btn btn-secondary">← Hospital Home</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="panel">
    <h3>Add New Doctor</h3>
    <form method="POST" class="form-grid">
        <div>
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="e.g. Dr. Nur Aina" required>
        </div>
        <div>
            <label for="specialization">Specialization</label>
            <input id="specialization" type="text" name="specialization" value="<?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?>" placeholder="e.g. General Medicine" required>
        </div>
        <div>
            <label for="available_day">Available Days</label>
            <input id="available_day" type="text" name="available_day" value="<?php echo htmlspecialchars($_POST['available_day'] ?? ''); ?>" placeholder="e.g. Mon, Wed, Fri" required>
            <small class="field-help">Use comma-separated days or a range such as Mon - Fri.</small>
        </div>
        <div>
            <label for="available_time">Available Time</label>
            <input id="available_time" type="text" name="available_time" value="<?php echo htmlspecialchars($_POST['available_time'] ?? ''); ?>" placeholder="e.g. 9:00 AM - 12:00 PM" required>
            <small class="field-help">Appointment options are created in 30-minute intervals.</small>
        </div>
        <div class="full"><button type="submit" name="add_doctor">+ Add Doctor</button></div>
    </form>
</div>

<div class="panel">
    <h3>Current Doctors</h3>
    <div class="table-wrap">
        <table>
        <tr><th>Name</th><th>Specialization</th><th>Booking Schedule</th><th>Status</th><th>Actions</th></tr>
        <?php
        $result = $conn->query("SELECT * FROM doctors ORDER BY name ASC");
        if ($result->num_rows === 0) {
            echo "<tr><td colspan='5'>No doctors have been added yet.</td></tr>";
        }
        while ($doc = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td><strong>" . htmlspecialchars($doc['name']) . "</strong></td>";
            echo "<td>" . htmlspecialchars($doc['specialization']) . "</td>";
            echo "<td><strong>" . htmlspecialchars($doc['available_day']) . "</strong><br><span class='muted'>" . htmlspecialchars($doc['available_time']) . "</span></td>";
            echo "<td><span class='status status-" . ($doc['status'] === 'active' ? 'approved' : 'cancelled') . "'>" . htmlspecialchars($doc['status']) . "</span></td>";
            echo "<td><div class='actions-wrap'>";
            echo "<a class='action-link edit' href='edit_doctor.php?id=" . (int)$doc['id'] . "'>Edit Details</a>";
            echo "<form method='POST' class='inline-form'><input type='hidden' name='doctor_id' value='" . (int)$doc['id'] . "'><button class='action-button edit' type='submit' name='toggle_doctor'>" . ($doc['status'] === 'active' ? 'Set Inactive' : 'Set Active') . "</button></form>";
            echo "<form method='POST' class='inline-form' onsubmit=\"return confirm('Delete this doctor? This is allowed only when there is no appointment history.');\"><input type='hidden' name='doctor_id' value='" . (int)$doc['id'] . "'><button class='action-button delete' type='submit' name='delete_doctor'>Delete</button></form>";
            echo "</div></td>";
            echo "</tr>";
        }
        ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
