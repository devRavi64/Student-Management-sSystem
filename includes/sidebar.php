<?php
$current_uri = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['admin_logged_in']);
?>
<!-- Sidebar -->
<div id="sidebar">
    <div class="sidebar-header d-flex align-items-center justify-content-between">
        <?php if ($is_admin): ?>
            <span class="fs-5 fw-bold text-white"><i class="fas fa-graduation-cap me-2 text-warning"></i>SMS Admin</span>
        <?php else: ?>
            <span class="fs-5 fw-bold text-white"><i class="fas fa-user-graduate me-2 text-info"></i>SMS Student</span>
        <?php endif; ?>
    </div>
    
    <div class="nav flex-column py-3">
        <?php if ($is_admin): ?>
            <!-- Admin Navigation -->
            <a href="index.php" class="nav-link <?php echo ($current_uri == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="students.php" class="nav-link <?php echo ($current_uri == 'students.php' || $current_uri == 'student-view.php' || $current_uri == 'student-edit.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-graduate"></i>
                <span>Students List</span>
            </a>
            <a href="student-add.php" class="nav-link <?php echo ($current_uri == 'student-add.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i>
                <span>Add Student</span>
            </a>
            <a href="marks.php" class="nav-link <?php echo ($current_uri == 'marks.php') ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>Marks Entry</span>
            </a>
            <a href="reports.php" class="nav-link <?php echo ($current_uri == 'reports.php') ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Reports</span>
            </a>
        <?php else: ?>
            <!-- Student Navigation -->
            <a href="index.php" class="nav-link <?php echo ($current_uri == 'index.php') ? 'active' : ''; ?>">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
            <a href="marks.php" class="nav-link <?php echo ($current_uri == 'marks.php') ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>My Marks</span>
            </a>
            <a href="reports.php" class="nav-link <?php echo ($current_uri == 'reports.php') ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice"></i>
                <span>My Report Card</span>
            </a>
        <?php endif; ?>
        
        <hr class="bg-secondary mx-3 my-4">
        
        <a href="logout.php" class="nav-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
