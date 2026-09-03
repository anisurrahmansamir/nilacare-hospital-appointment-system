<?php
require '../config.php';
require '../includes/appointment_helpers.php';

$pageTitle = "Book Appointment for Patient";
$activePage = "book";
$error = "";
$success = "";
$selectedPatient = 0;
$selectedDoctor = 0;
$selectedDate = '';
$selectedTime = '';
$reason = '';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPatient = (int)($_POST['patient_id'] ?? 0);
    $selectedDoctor = (int)($_POST['doctor_id'] ?? 0);
    $selectedDate = $_POST['appointment_date'] ?? '';
    $selectedTime = normalise_appointment_time($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($selectedPatient === 0 || $selectedDoctor === 0 || $selectedDate === "" || $selectedTime === "") {
        $error = "Please select a patient, doctor, available date, and available time.";
    } else {
        $doctor = get_active_doctor($conn, $selectedDoctor);
        $patientCheck = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $patientCheck->bind_param('i', $selectedPatient);
        $patientCheck->execute();
        $patientCheck->store_result();

        if ($patientCheck->num_rows !== 1) {
            $error = "The selected patient account was not found.";
        } elseif (!$doctor) {
            $error = "The selected doctor is not currently active.";
        } elseif (!doctor_is_available($doctor, $selectedDate, $selectedTime, $error)) {
            // Schedule message is supplied by the helper.
        } elseif (appointment_slot_has_clash($conn, $selectedDoctor, $selectedDate, $selectedTime)) {
            $error = "That time slot is already booked for this doctor. Please choose another available time.";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason, status) VALUES (?, ?, ?, ?, ?, 'approved')");
            $stmt->bind_param("iisss", $selectedPatient, $selectedDoctor, $selectedDate, $selectedTime, $reason);
            if ($stmt->execute()) {
                $success = "Appointment booked and approved for the patient.";
                $selectedPatient = 0;
                $selectedDoctor = 0;
                $selectedDate = '';
                $selectedTime = '';
                $reason = '';
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

$patients = $conn->query("SELECT id, name, email FROM users ORDER BY name ASC");
$doctors = $conn->query("SELECT id, name, specialization, available_day, available_time FROM doctors WHERE status='active' ORDER BY name ASC");

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow">Staff booking</span>
        <h1>Book Appointment for a Patient</h1>
        <p class="muted">Staff bookings use the doctor's live schedule and are approved immediately.</p>
    </div>
    <a href="../index.php" class="btn btn-secondary">Hospital Home</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="appointments.php">View appointments</a></div><?php endif; ?>

<div class="panel">
<form method="POST" data-appointment-form>
    <div class="form-grid">
        <div class="full">
            <label for="patient_id">Select Patient</label>
            <select name="patient_id" id="patient_id" required>
                <option value="">-- Choose a patient --</option>
                <?php while ($patient = $patients->fetch_assoc()): ?>
                    <option value="<?php echo $patient['id']; ?>" <?php echo (int)$patient['id'] === $selectedPatient ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($patient['name'] . ' (' . $patient['email'] . ')'); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <p class="muted">Patient not registered? <a href="add_patient.php">Add a patient first</a>.</p>
        </div>

        <div class="full">
            <label for="doctor_id">Select Doctor</label>
            <select name="doctor_id" id="doctor_id" required>
                <option value="">-- Choose a doctor --</option>
                <?php while ($doc = $doctors->fetch_assoc()): ?>
                    <?php $days = implode(',', parse_available_days($doc['available_day'])); ?>
                    <option value="<?php echo $doc['id']; ?>"
                        data-doctor-name="<?php echo htmlspecialchars($doc['name'], ENT_QUOTES); ?>"
                        data-days="<?php echo htmlspecialchars($days, ENT_QUOTES); ?>"
                        data-schedule="<?php echo htmlspecialchars($doc['available_day'], ENT_QUOTES); ?>"
                        data-time="<?php echo htmlspecialchars($doc['available_time'], ENT_QUOTES); ?>"
                        <?php echo (int)$doc['id'] === $selectedDoctor ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($doc['name'] . ' — ' . $doc['specialization']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="full availability-panel" data-availability-summary>
            <strong>Select a doctor</strong>
            <span>The doctor's available days and appointment times will appear here.</span>
        </div>

        <div>
            <label for="appointment_date">Available Date</label>
            <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selectedDate); ?>">
        </div>
        <div>
            <label for="appointment_time">Available Time</label>
            <select id="appointment_time" name="appointment_time" required data-selected-time="<?php echo htmlspecialchars($selectedTime); ?>">
                <option value="">-- Choose an available time --</option>
            </select>
        </div>
        <div class="full">
            <label for="reason">Reason for Visit</label>
            <textarea id="reason" name="reason" rows="4" maxlength="255" placeholder="Briefly describe the reason for the visit"><?php echo htmlspecialchars($reason); ?></textarea>
        </div>
    </div>

    <button type="submit">Book and Approve Appointment</button>
    <a href="appointments.php" class="btn btn-secondary">Cancel</a>
</form>
</div>

<script src="../assets/booking.js"></script>
<?php include 'includes/footer.php'; ?>
