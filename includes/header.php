<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$folder = basename(dirname($_SERVER['PHP_SELF']));

// Determine base path for links and assets
$base_path = ($folder === 'admin' || $folder === 'student') ? '../' : './';

// Public pages list
$public_pages = ['login.php', 'register.php'];

// Session verification based on user role folder
if ($folder === 'admin' && !in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['admin_logged_in'])) {
        header("Location: login.php");
        exit;
    }
} elseif ($folder === 'student' && !in_array($current_page, $public_pages)) {
    if (!isset($_SESSION['student_logged_in'])) {
        header("Location: login.php");
        exit;
    }
}

// XSS Sanitization helper function
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;450;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo $base_path; ?>css/style.css" rel="stylesheet">
</head>
<body>

<?php 
$is_authenticated = (isset($_SESSION['admin_logged_in']) && $folder === 'admin') || (isset($_SESSION['student_logged_in']) && $folder === 'student');
if ($is_authenticated && !in_array($current_page, $public_pages)): 
?>
<div class="d-flex">
    <!-- Include Sidebar -->
    <?php include $base_path . 'includes/sidebar.php'; ?>
    
    <!-- Main Content wrapper -->
    <div id="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom">
            <div class="container-fluid">
                <button type="button" id="sidebarToggle" class="btn btn-outline-secondary me-3">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-brand mb-0 h1 text-primary fw-bold d-none d-sm-inline-block">Student Management System</span>
                
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link text-decoration-none dropdown-toggle text-dark" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg me-1 text-primary"></i> 
                            <strong>
                                <?php 
                                if (isset($_SESSION['admin_logged_in'])) {
                                    echo sanitize($_SESSION['admin_name'] ?? 'Admin');
                                } else {
                                    echo sanitize($_SESSION['student_name'] ?? 'Student');
                                }
                                ?>
                            </strong>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
<?php endif; ?>
