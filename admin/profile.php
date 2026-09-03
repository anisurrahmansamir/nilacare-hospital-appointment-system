<?php
require '../config.php';

$pageTitle = 'Admin Profile';
$activePage = 'profile';
$error = '';
$success = '';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$adminId = (int)$_SESSION['admin_id'];
$stmt = $conn->prepare('SELECT id, username FROM admins WHERE id = ?');
$stmt->bind_param('i', $adminId);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '') {
        $error = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
        $error = 'Username must be 3–50 characters and use only letters, numbers, dots, hyphens, or underscores.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'A new password must contain at least 8 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new password and confirmation do not match.';
    } else {
        $check = $conn->prepare('SELECT id FROM admins WHERE username = ? AND id != ?');
        $check->bind_param('si', $username, $adminId);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'That username is already used by another administrator.';
        } else {
            if ($newPassword !== '') {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = $conn->prepare('UPDATE admins SET username = ?, password = ? WHERE id = ?');
                $update->bind_param('ssi', $username, $hash, $adminId);
            } else {
                $update = $conn->prepare('UPDATE admins SET username = ? WHERE id = ?');
                $update->bind_param('si', $username, $adminId);
            }

            if ($update->execute()) {
                $_SESSION['admin_name'] = $username;
                $admin['username'] = $username;
                $success = 'Administrator profile updated successfully.';
            } else {
                $error = 'The administrator profile could not be updated.';
            }
        }
    }

    if ($error) $admin['username'] = $username;
}

include 'includes/header.php';
?>

<div class="admin-page-heading">
    <div>
        <span class="eyebrow">Account settings</span>
        <h1>Admin Profile</h1>
        <p class="muted">Update the username or password used to access the hospital administration panel.</p>
    </div>
    <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

<div class="admin-profile-layout">
    <aside class="admin-account-card">
        <div class="admin-account-avatar"><?php echo htmlspecialchars(strtoupper(substr($admin['username'], 0, 1))); ?></div>
        <span class="account-badge">Administrator</span>
        <h2><?php echo htmlspecialchars($admin['username']); ?></h2>
        <p><?php echo htmlspecialchars($hospitalName); ?></p>
        <div class="account-details">
            <div><span>Access level</span><strong>Full administration</strong></div>
            <div><span>Portal</span><strong>Hospital management</strong></div>
            <div><span>Location</span><strong><?php echo htmlspecialchars($hospitalLocation); ?></strong></div>
        </div>
        <a href="../index.php" class="text-link">Open Hospital Homepage →</a>
    </aside>

    <section class="panel admin-profile-form">
        <div class="panel-heading">
            <div><span class="eyebrow">Login information</span><h2>Account Details</h2></div>
            <p>Leave the password fields empty if you only want to change the username.</p>
        </div>
        <form method="POST">
            <div class="form-field">
                <label for="username">Administrator Username</label>
                <input type="text" id="username" name="username" maxlength="50" value="<?php echo htmlspecialchars($admin['username']); ?>" autocomplete="username" required>
                <small class="field-help">Use 3–50 letters, numbers, dots, hyphens, or underscores.</small>
            </div>

            <div class="form-field">
                <label for="password">New Password <span class="muted">(optional)</span></label>
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
            </div>

            <div class="form-field">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" placeholder="Enter the new password again">
            </div>

            <div class="profile-security-note"><strong>Account security</strong><span>Choose a password that is difficult to guess and do not share administrator access.</span></div>
            <button type="submit">Save Profile Changes</button>
        </form>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
