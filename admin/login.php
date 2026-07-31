<?php
require_once '../config/db.php';
include '../includes/header.php';

$error = '';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Secure Session Initialization
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_email'] = $user['email'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid email address or password.";
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div class="login-container">
    <div class="card login-card shadow-lg border-0">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="mb-3 d-inline-block p-3 rounded-circle bg-light">
                    <i class="fas fa-user-shield fa-3x text-warning"></i>
                </div>
                <h3 class="fw-bold">SMS Admin Portal</h3>
                <p class="text-muted">Sign in to manage the system</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="email" class="form-label">Admin Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="admin@sms.com">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-warning w-100 py-2.5 mb-3 text-dark fw-bold">Sign In as Admin</button>
                
                <div class="text-center mt-3">
                    <span class="text-muted small">Need an account?</span> 
                    <a href="register.php" class="text-decoration-none small fw-bold text-warning">Register Admin</a>
                </div>
                
                <div class="text-center mt-2">
                    <a href="../index.php" class="text-decoration-none small"><i class="fas fa-arrow-left me-1"></i>Back to Portal Selector</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
