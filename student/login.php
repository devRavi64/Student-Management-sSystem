<?php
require_once '../config/db.php';
include '../includes/header.php';

$error = '';

if (isset($_SESSION['student_logged_in'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['login_input']); // Student ID or Email
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            // Search student by Email OR Student ID
            $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ? OR student_id = ?");
            $stmt->execute([$login_input, $login_input]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password'])) {
                // Secure Session Initialization for Student
                session_regenerate_id(true);
                $_SESSION['student_logged_in'] = true;
                $_SESSION['student_db_id'] = $student['id'];
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['student_name'] = $student['first_name'] . ' ' . $student['last_name'];
                $_SESSION['student_email'] = $student['email'];
                $_SESSION['student_class'] = $student['class'];

                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid Student ID/Email or Password.";
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
                    <i class="fas fa-user-graduate fa-3x text-info"></i>
                </div>
                <h3 class="fw-bold">Student Portal</h3>
                <p class="text-muted">Sign in to view your dashboard & grades</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo sanitize($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="login_input" class="form-label">Student ID or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card text-muted"></i></span>
                        <input type="text" class="form-control" id="login_input" name="login_input" required placeholder="SMS-10001 or student@sms.com">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-info w-100 py-2.5 mb-3 text-dark fw-bold">Sign In as Student</button>
                
                <div class="text-center">
                    <a href="../index.php" class="text-decoration-none small"><i class="fas fa-arrow-left me-1"></i>Back to Portal Selector</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
