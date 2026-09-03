<?php
require 'config.php';
$pageTitle = "Register";
$noContainer = true;
$authRole = "register";
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    if ($name === "" || $email === "" || $phone === "" || $password === "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
            if ($stmt->execute()) {
                $success = "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<main class="auth-wrapper register-wrapper">
    <section class="auth-layout" aria-labelledby="register-title">
        <aside class="auth-intro">
            <span class="portal-label">New patient registration</span>
            <h1>Your care journey starts here.</h1>
            <p>Create a patient account to access the online appointment services at <?php echo htmlspecialchars($hospitalName); ?>.</p>
            <ul class="auth-benefits">
                <li><span>✓</span> Simple appointment booking</li>
                <li><span>✓</span> Doctor availability shown clearly</li>
                <li><span>✓</span> Your bookings in one dashboard</li>
            </ul>
            <div class="auth-help">Already registered? <a href="login.php">Sign in to your account</a>.</div>
        </aside>

        <div class="auth-card">
            <div class="auth-card-heading">
                <div class="auth-symbol" aria-hidden="true"><span>+</span></div>
                <div>
                    <span class="eyebrow">Create an account</span>
                    <h2 id="register-title">Patient Registration</h2>
                </div>
            </div>
            <p class="sub">Complete the details below. All fields are required.</p>

            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <?php if ($success): ?><div class="alert alert-success">Registration successful. <a href="login.php">Sign in to your account</a>.</div><?php endif; ?>

            <form method="POST">
                <div class="form-field">
                    <label for="name">Full Name</label>
                    <input id="name" type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" autocomplete="name" placeholder="Enter your full name" required>
                </div>
                <div class="form-field">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autocomplete="email" placeholder="name@example.com" required>
                </div>
                <div class="form-field">
                    <label for="phone">Phone Number</label>
                    <input id="phone" type="tel" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" autocomplete="tel" placeholder="e.g. 012-345 6789" required>
                </div>
                <div class="form-field">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="At least 8 characters" required>
                </div>
                <button type="submit" class="auth-submit">Create Patient Account</button>
            </form>
            <div class="auth-card-footer">
                <p>Already registered? <a href="login.php">Patient login</a></p>
                <a class="back-link" href="index.php">← Return to hospital homepage</a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
