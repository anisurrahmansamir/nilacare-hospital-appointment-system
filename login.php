<?php
require 'config.php';
$pageTitle = "Login";
$noContainer = true;
$authRole = "patient";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that email.";
    }
}

include 'includes/header.php';
?>

<main class="auth-wrapper">
    <section class="auth-layout" aria-labelledby="patient-login-title">
        <aside class="auth-intro">
            <span class="portal-label">Patient portal</span>
            <h1>Manage your hospital visit with ease.</h1>
            <p>Use your secure patient account to arrange and manage appointments at <?php echo htmlspecialchars($hospitalName); ?>.</p>
            <ul class="auth-benefits">
                <li><span>✓</span> View doctors and their availability</li>
                <li><span>✓</span> Book an available date and time</li>
                <li><span>✓</span> Edit, cancel, and track appointments</li>
            </ul>
            <div class="auth-help">Need administrator access? <a href="admin/login.php">Open the admin login</a>.</div>
        </aside>

        <div class="auth-card">
            <div class="portal-switch" aria-label="Choose login type">
                <a class="active" href="login.php">Patient Login</a>
                <a href="admin/login.php">Admin Login</a>
            </div>
            <div class="auth-card-heading">
                <div class="auth-symbol" aria-hidden="true"><span>+</span></div>
                <div>
                    <span class="eyebrow">Welcome back</span>
                    <h2 id="patient-login-title">Patient Login</h2>
                </div>
            </div>
            <p class="sub">Enter your account details to continue.</p>

            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

            <form method="POST">
                <div class="form-field">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="email" placeholder="name@example.com" required>
                </div>

                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="auth-submit">Sign In to Patient Portal</button>
            </form>
            <div class="auth-card-footer">
                <p>New patient? <a href="register.php">Create an account</a></p>
                <a class="back-link" href="index.php">← Return to hospital homepage</a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
