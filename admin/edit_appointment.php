<?php
require '../config.php';
require '../includes/appointment_helpers.php';

$pageTitle = 'Edit Appointment';
$activePage = 'appointments';
$error = '';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$appointmentId = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['appointment_id'] ?? 0);
$stmt = $conn->prepare('SELECT id, patient_id, doctor_id, appointment_date, appointment_time, reason, status FROM appointments WHERE id = ?');
$stmt->bind_param('i', $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    header('Location: appointments.php');
    exit;
}
$appointment = $result->fetch_assoc();

$selectedPatient = (int)$appointment['patient_id'];
$selectedDoctor = (int)$appointment['doctor_id'];
$selectedDate = $appointment['appointment_date'];
$selectedTime = substr($appointment['appointment_time'], 0, 5);
$reason = $appointment['reason'];
$status = $appointment['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPatient = (int)($_POST['patient_id'] ?? 0);
    $selectedDoctor = (int)($_POST['doctor_id'] ?? 0);
    $selectedDate = $_POST['appointment_date'] ?? '';
    $selectedTime = normalise_appointment_time($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $status = $_POST['status'] ?? 'pending';
    $validStatuses = ['pending', 'approved', 'cancelled', 'completed'];

    $patientCheck = $conn->prepare('SELECT id FROM users WHERE id = ?');
    $patientCheck->bind_param('i', $selectedPatient);
    $patientCheck->execute();
    $patientCheck->store_result();
    $doctor = get_active_doctor($conn, $selectedDoctor);

    if ($patientCheck->num_rows !== 1) {
        $error = 'Please select a valid patient.';
    } elseif (!$doctor) {
        $error = 'Please select an active doctor.';
    } elseif (!in_array($status, $validStatuses, true)) {
        $error = 'Please select a valid appointment status.';
    } elseif (!doctor_is_available($doctor, $selectedDate, $selectedTime, $error)) {
        // Schedule message is supplied by the helper.
    } elseif ($status !== 'cancelled' && appointment_slot_has_clash($conn, $selectedDoctor, $selectedDate, $selectedTime, $appointmentId)) {
        $error = 'That doctor already has an active booking at the selected date and time.';
    } else {
        $update = $conn->prepare('UPDATE appointments SET patient_id = ?, doctor_id = ?, appointment_date = ?, appointment_time = ?, reason = ?, status = ? WHERE id = ?');
        $update->bind_param('iissssi', $selectedPatient, $selectedDoctor, $selectedDate, $selectedTime, $reason, $status, $appointmentId);
        if ($update->execute()) {
            header('Location: appointments.php?updated=1');
            exit;
        }
        $error = 'The appointment could not be updated. Please try again.';
    }
}

$patients = $conn->query('SELECT id, name, email FROM users ORDER BY name ASC');
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
        <p class="muted">Update the patient, doctor, available slot, reason, or booking status.</p>
    </div>
    <a href="appointments.php" class="btn btn-secondary">← Appointments</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div class="panel">
<form method="POST" data-appointment-form>
    <input type="hidden" name="appointment_id" value="<?php echo $appointmentId; ?>">
    <div class="form-grid">
        <div>
            <label for="patient_id">Patient</label>
            <select name="patient_id" id="patient_id" required>
                <?php while ($patient = $patients->fetch_assoc()): ?>
                    <option value="<?php echo $patient['id']; ?>" <?php echo (int)$patient['id'] === $selectedPatient ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($patient['name'] . ' (' . $patient['email'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="status">Status</label>
            <select name="status" id="status" required>
                <?php foreach (['pending', 'approved', 'cancelled', 'completed'] as $value): ?>
                    <option value="<?php echo $value; ?>" <?php echo $value === $status ? 'selected' : ''; ?>><?php echo ucfirst($value); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
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
    <button type="submit">Save Appointment</button>
    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
</form>
</div>

<script src="../assets/booking.js"></script>
<?php include 'includes/footer.php'; ?>
