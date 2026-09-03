<?php
require '../config.php';
require '../includes/appointment_helpers.php';
$pageTitle = "Edit Doctor";
$activePage = "doctors";
$error = "";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$doctorId = (int)($_GET['id'] ?? $_POST['doctor_id'] ?? 0);
$stmt = $conn->prepare("SELECT id, name, specialization, available_day, available_time, status FROM doctors WHERE id = ?");
$stmt->bind_param('i', $doctorId);
$stmt->execute();
$doctor = $stmt->get_result()->fetch_assoc();

if (!$doctor) {
    header("Location: doctors.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $availableDay = trim($_POST['available_day'] ?? '');
    $availableTime = trim($_POST['available_time'] ?? '');
    $status = $_POST['status'] ?? '';

    if ($name === '' || $specialization === '' || $availableDay === '' || $availableTime === '') {
        $error = "Please fill in all fields.";
    } elseif (!in_array($status, ['active', 'inactive'], true)) {
        $error = "Please select a valid doctor status.";
    } elseif (count(parse_available_days($availableDay)) === 0) {
        $error = "Enter valid days such as Mon, Wed, Fri or Mon - Fri.";
    } elseif (parse_available_time_range($availableTime) === null) {
        $error = "Enter a valid time range such as 9:00 AM - 12:00 PM.";
    } else {
        $update = $conn->prepare("UPDATE doctors SET name = ?, specialization = ?, available_day = ?, available_time = ?, status = ? WHERE id = ?");
        $update->bind_param('sssssi', $name, $specialization, $availableDay, $availableTime, $status, $doctorId);
        $update->execute();
        header("Location: doctors.php?updated=1");
        exit;
    }

    $doctor = [
        'id' => $doctorId,
        'name' => $name,
        'specialization' => $specialization,
        'available_day' => $availableDay,
        'available_time' => $availableTime,
        'status' => $status,
    ];
}

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow">Doctor schedule</span>
        <h1>Edit Doctor</h1>
        <p class="muted">Changes to availability immediately control the date and time choices offered to patients and staff.</p>
    </div>
    <div class="header-actions">
        <a href="doctors.php" class="btn btn-secondary">← Back to Doctors</a>
        <a href="../index.php" class="btn btn-outline">Hospital Home</a>
    </div>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="panel form-panel">
    <form method="POST" class="form-grid">
        <input type="hidden" name="doctor_id" value="<?php echo (int)$doctor['id']; ?>">
        <div>
            <label for="name">Doctor Name</label>
            <input id="name" type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
        </div>
        <div>
            <label for="specialization">Specialization</label>
            <input id="specialization" type="text" name="specialization" value="<?php echo htmlspecialchars($doctor['specialization']); ?>" required>
        </div>
        <div>
            <label for="available_day">Available Days</label>
            <input id="available_day" type="text" name="available_day" value="<?php echo htmlspecialchars($doctor['available_day']); ?>" placeholder="e.g. Mon, Wed, Fri" required>
            <small class="field-help">Accepted examples: Mon, Wed, Fri or Mon - Fri.</small>
        </div>
        <div>
            <label for="available_time">Available Time</label>
            <input id="available_time" type="text" name="available_time" value="<?php echo htmlspecialchars($doctor['available_time']); ?>" placeholder="e.g. 9:00 AM - 12:00 PM" required>
            <small class="field-help">The booking page creates 30-minute time options inside this range.</small>
        </div>
        <div class="full">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="active" <?php echo $doctor['status'] === 'active' ? 'selected' : ''; ?>>Active — available for booking</option>
                <option value="inactive" <?php echo $doctor['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive — hidden from booking</option>
            </select>
        </div>
        <div class="full"><button type="submit">Save Doctor &amp; Schedule</button></div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
