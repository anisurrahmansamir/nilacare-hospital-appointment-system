<?php
require 'config.php';

$pageTitle = 'My Profile';
$error = '';
$success = '';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $newPassword = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $phone === '') {
        $error = 'Name, email, and phone number are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'A new password must contain at least 8 characters.';
    } else {
        $check = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $check->bind_param('si', $email, $userId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'That email address is already used by another patient.';
        } else {
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?');
                $update->bind_param('ssssi', $name, $email, $phone, $hash, $userId);
            } else {
                $update = $conn->prepare('UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?');
                $update->bind_param('sssi', $name, $email, $phone, $userId);
            }

            if ($update->execute()) {
                $_SESSION['user_name'] = $name;
                $success = 'Your profile has been updated.';
                $user['name'] = $name;
                $user['email'] = $email;
                $user['phone'] = $phone;
            } else {
                $error = 'Your profile could not be updated. Please try again.';
            }
        }
    }

    if ($error) {
        $user['name'] = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <div><span class="eyebrow">Patient account</span><h1>My Profile</h1></div>
    <a href="index.php" class="btn btn-secondary">Hospital Home</a>
</div>

<div class="profile-summary">
    <div class="profile-avatar"><?php echo htmlspecialchars(strtoupper(substr($user['name'], 0, 1))); ?></div>
    <div><h2><?php echo htmlspecialchars($user['name']); ?></h2><p>Patient since <?php echo date('d M Y', strtotime($user['created_at'])); ?></p></div>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="form-panel">
<form method="POST">
    <div class="form-grid">
        <div>
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div>
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>
        <div class="full">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="full">
            <label for="password">New Password <span class="muted">(leave blank to keep the current password)</span></label>
            <input type="password" id="password" name="password" minlength="8" autocomplete="new-password">
        </div>
    </div>
    <button type="submit">Save Profile Changes</button>
    <a href="dashboard.php" class="btn btn-secondary">My Appointments</a>
</form>
</div>

<?php include 'includes/footer.php'; ?>

