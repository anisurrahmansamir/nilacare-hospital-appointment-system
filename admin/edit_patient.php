<?php
require '../config.php';
$pageTitle = "Edit Patient";
$activePage = "patients";
$error = "";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: patients.php");
    exit;
}
$patient = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $newPassword = $_POST['password'];

    if ($name === "" || $email === "" || $phone === "") {
        $error = "Name, email, and phone are required.";
    } else {
        // Make sure the email isn't already used by a different patient
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "That email is already used by another patient.";
        } else {
            if ($newPassword !== "") {
                $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $name, $email, $phone, $hashed, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $phone, $id);
            }

            if ($stmt->execute()) {
                header("Location: patients.php?updated=1");
                exit;
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
    // Keep the entered values on screen if there was an error
    $patient['name'] = $name;
    $patient['email'] = $email;
    $patient['phone'] = $phone;
}

include 'includes/header.php';
?>

<h1>Edit Patient</h1>
<p class="muted"><a href="patients.php">&larr; Back to Patients</a></p>

<?php if ($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>

<div class="panel">
<form method="POST">
    <label>Full Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>

    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($patient['email']); ?>" required>

    <label>Phone Number</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required>

    <label>New Password <span class="muted">(leave blank to keep current password)</span></label>
    <input type="password" name="password" placeholder="••••••••">

    <button type="submit">Save Changes</button>
</form>
</div>

<?php include 'includes/footer.php'; ?>
