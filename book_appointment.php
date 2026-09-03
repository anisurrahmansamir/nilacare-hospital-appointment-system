<?php
require 'config.php';
require 'includes/appointment_helpers.php';

$pageTitle = "Book Appointment";
$error = "";
$success = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$patient_id = (int)$_SESSION['user_id'];
$selected_doctor = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : 0;
$selected_date = '';
$selected_time = '';
$reason = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_doctor = (int)($_POST['doctor_id'] ?? 0);
    $selected_date = $_POST['appointment_date'] ?? '';
    $selected_time = normalise_appointment_time($_POST['appointment_time'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($selected_doctor === 0 || $selected_date === "" || $selected_time === "") {
        $error = "Please select a doctor, an available date, and an available time.";
    } else {
        $doctor = get_active_doctor($conn, $selected_doctor);
        if (!$doctor) {
            $error = "The selected doctor is not currently available for booking.";
        } elseif (!doctor_is_available($doctor, $selected_date, $selected_time, $error)) {
            // The helper provides a clear schedule message.
        } elseif (appointment_slot_has_clash($conn, $selected_doctor, $selected_date, $selected_time)) {
            $error = "That time slot is already booked. Please choose another available time.";
        } else {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $patient_id, $selected_doctor, $selected_date, $selected_time, $reason);
            if ($stmt->execute()) {
                $success = "Appointment request submitted to " . $hospitalName . ". Status: pending approval.";
                $selected_doctor = 0;
                $selected_date = '';
                $selected_time = '';
                $reason = '';
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

$doctors = $conn->query("SELECT id, name, specialization, available_day, available_time FROM doctors WHERE status='active' ORDER BY name ASC");

include 'includes/header.php';
?>

<div class="page-header">
    <div>
        <span class="eyebrow">Patient booking</span>
        <h1>Book an Appointment</h1>
        <p class="muted">Only dates and times inside each doctor's working schedule can be booked.</p>
    </div>
    <a href="index.php" class="btn btn-secondary">← Hospital Home</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?> <a href="dashboard.php">View my appointments</a></div><?php endif; ?>

<div class="form-panel">
<form method="POST" data-appointment-form>
    <div class="form-grid">
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
                        <?php echo (int)$doc['id'] === $selected_doctor ? 'selected' : ''; ?>>
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
            <input type="date" id="appointment_date" name="appointment_date" required
                   min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($selected_date); ?>">
        </div>

        <div>
            <label for="appointment_time">Available Time</label>
            <select id="appointment_time" name="appointment_time" required
                    data-selected-time="<?php echo htmlspecialchars($selected_time); ?>">
                <option value="">-- Choose an available time --</option>
            </select>
        </div>

        <div class="full">
            <label for="reason">Reason for Visit</label>
            <textarea id="reason" name="reason" rows="4" maxlength="255" placeholder="Briefly describe your symptoms or reason for the visit"><?php echo htmlspecialchars($reason); ?></textarea>
        </div>
    </div>

    <button type="submit">Submit Appointment Request</button>
    <a href="dashboard.php" class="btn btn-secondary">My Appointments</a>
</form>
</div>

<script src="assets/booking.js"></script>
<?php include 'includes/footer.php'; ?>
