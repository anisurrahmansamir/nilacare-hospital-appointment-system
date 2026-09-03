<?php
require 'config.php';
require 'includes/appointment_helpers.php';

$pageTitle = "Edit Appointment";
$error = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patientId = (int)$_SESSION['user_id'];
$appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['appointment_id'] ?? 0);

$stmt = $conn->prepare("SELECT id, doctor_id, appointment_date, appointment_time, reason, status FROM appointments WHERE id = ? AND patient_id = ?");
$stmt->bind_param('ii', $appointmentId, $patientId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header('Location: dashboard.php');
    exit;
}
$appointment = $result->fetch_assoc();

if (!in_array($appointment['status'], ['pending', 'approved'], true)) {
    header('Location: dashboard.php?edit_unavailable=1');
    exit;
}

$selectedDoctor = (int)$appointment['doctor_id'];
$selectedDate = $appointment['appointment_date'];
$selectedTime = substr($appointment['appointment_time'], 0, 5);
$reason = $appointment['reason'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedDoctor = (int)($_POST['doctor_id'] ?? 0);
    $selectedDate = $_POST['appointment_date'] ?? '';
    $selectedTime = normalise_appointment_time($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    $doctor = get_active_doctor($conn, $selectedDoctor);
    if (!$doctor) {
        $error = 'Please choose an active doctor.';
    } elseif (!doctor_is_available($doctor, $selectedDate, $selectedTime, $error)) {
        // Schedule message is supplied by the helper.
    } elseif (appointment_slot_has_clash($conn, $selectedDoctor, $selectedDate, $selectedTime, $appointmentId)) {
        $error = 'That time slot is already booked. Please choose another available time.';
    } else {
        // Any patient change returns the appointment to pending for staff review.
        $update = $conn->prepare("UPDATE appointments SET doctor_id = ?, appointment_date = ?, appointment_time = ?, reason = ?, status = 'pending' WHERE id = ? AND patient_id = ?");
        $update->bind_param('isssii', $selectedDoctor, $selectedDate, $selectedTime, $reason, $appointmentId, $patientId);
        if ($update->execute()) {
            header('Location: dashboard.php?updated=1');
            exit;
        }
        $error = 'The appointment could not be updated. Please try again.';
    }
}

$doctorList = $conn->prepare("SELECT id, name, specialization, available_day, available_time, status FROM doctors WHERE status='active' OR id = ? ORDER BY status DESC, name ASC");
$doctorList->bind_param('i', $selectedDoctor);
$doctorList->execute();
$doctors = $doctorList->get_result();
include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow">Appointment #<?php echo $appointmentId; ?></span>
        <h1>Edit Appointment</h1>
        <p class="muted">Changing an approved appointment returns it to pending so hospital staff can review the new details.</p>
    </div>
    <a href="dashboard.php" class="btn btn-secondary">← My Appointments</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="form-panel">
<form method="POST" data-appointment-form>
    <input type="hidden" name="appointment_id" value="<?php echo $appointmentId; ?>">
    <div class="form-grid">
        <div class="full">
            <label for="doctor_id">Doctor</label>
            <select name="doctor_id" id="doctor_id" required>
                <?php while ($doc = $doctors->fetch_assoc()): ?>
                    <?php $days = implode(',', parse_available_days($doc['available_day'])); ?>
                    <option value="<?php echo $doc['id']; ?>"
                        data-doctor-name="<?php echo htmlspecialchars($doc['name'], ENT_QUOTES); ?>"
                        data-days="<?php echo htmlspecialchars($days, ENT_QUOTES); ?>"
                        data-schedule="<?php echo htmlspecialchars($doc['available_day'], ENT_QUOTES); ?>"
                        data-time="<?php echo htmlspecialchars($doc['available_time'], ENT_QUOTES); ?>"
                        <?php echo $doc['status'] !== 'active' ? 'disabled' : ''; ?>
                        <?php echo (int)$doc['id'] === $selectedDoctor ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($doc['name'] . ' — ' . $doc['specialization'] . ($doc['status'] !== 'active' ? ' (inactive — choose another doctor)' : '')); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="full availability-panel" data-availability-summary></div>
        <div>
            <label for="appointment_date">Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selectedDate); ?>">
        </div>
        <div>
            <label for="appointment_time">Time</label>
            <select id="appointment_time" name="appointment_time" required data-selected-time="<?php echo htmlspecialchars($selectedTime); ?>"></select>
        </div>
        <div class="full">
            <label for="reason">Reason for Visit</label>
            <textarea id="reason" name="reason" rows="4" maxlength="255"><?php echo htmlspecialchars($reason); ?></textarea>
        </div>
    </div>
    <button type="submit">Save Appointment Changes</button>
    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
</form>
</div>

<script src="assets/booking.js"></script>
<?php include 'includes/footer.php'; ?>
