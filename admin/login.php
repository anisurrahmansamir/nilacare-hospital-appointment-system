<?php
require '../config.php';
$pageTitle = "Admin Login";
$basePath = "../";
$noContainer = true;
$authRole = "admin";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        if (password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Admin account not found.";
    }
}

include '../includes/header.php';
?>

<main class="auth-wrapper">
    <section class="auth-layout auth-layout-admin" aria-labelledby="admin-login-title">
        <aside class="auth-intro">
            <span class="portal-label">Staff administration</span>
            <h1>Hospital operations in one clear dashboard.</h1>
            <p>Authorised staff can manage patients, doctor schedules, appointment details, and booking statuses.</p>
            <ul class="auth-benefits">
                <li><span>✓</span> Maintain patients and doctor profiles</li>
                <li><span>✓</span> Control doctor availability schedules</li>
                <li><span>✓</span> Review and update appointments</li>
            </ul>
            <div class="auth-help">Are you a patient? <a href="../login.php">Open the patient login</a>.</div>
        </aside>

        <div class="auth-card">
            <div class="portal-switch" aria-label="Choose login type">
                <a href="../login.php">Patient Login</a>
                <a class="active" href="login.php">Admin Login</a>
            </div>
            <div class="auth-card-heading">
                <div class="auth-symbol admin-symbol" aria-hidden="true"><span>+</span></div>
                <div>
                    <span class="eyebrow">Authorised access</span>
                    <h2 id="admin-login-title">Admin Login</h2>
                </div>
            </div>
            <p class="sub">Sign in to the <?php echo htmlspecialchars($hospitalName); ?> administration panel.</p>

            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-field">
                    <label for="username">Username</label>
                    <input id="username" type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" autocomplete="username" placeholder="Enter your username" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="auth-submit">Sign In to Admin Panel</button>
            </form>
            <div class="demo-credentials"><strong>Demo account</strong><span>Username: admin &nbsp;·&nbsp; Password: admin123</span></div>
            <div class="auth-card-footer">
                <a class="back-link" href="../index.php">← Return to hospital homepage</a>
            </div>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>
