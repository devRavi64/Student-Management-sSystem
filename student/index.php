<?php
require_once '../config/db.php';
include '../includes/header.php';

$student_id = $_SESSION['student_db_id'];

try {
    // Fetch full student information
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        // Fallback safety
        header("Location: logout.php");
        exit;
    }

    // Fetch marks summary to show count and average in dashboard card
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, COALESCE(AVG(marks), 0) as avg FROM marks WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $summary = $stmt->fetch();

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<div class="container-fluid px-0">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">Welcome, <?php echo sanitize($student['first_name']); ?>!</h2>
            <p class="text-muted">Here is your student dashboard summary and personal profile information.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Profile Overview Card -->
        <div class="col-12 col-lg-4">
            <div class="card card-common text-center py-4 px-3 h-100">
                <div class="card-body">
                    <?php if ($student['profile_photo']): ?>
                        <img src="../uploads/<?php echo sanitize($student['profile_photo']); ?>" alt="Profile" class="rounded-circle border mb-3 shadow-sm" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 150px; height: 150px;">
                            <i class="fas fa-user-circle fa-7x"></i>
                        </div>
                    <?php endif; ?>

                    <h4 class="fw-bold mb-1"><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?></h4>
                    <span class="badge bg-info text-dark px-3 py-2 mb-4"><?php echo sanitize($student['class']); ?></span>

                    <hr class="my-4">

                    <!-- Stats summaries -->
                    <div class="row g-2">
                        <div class="col-6 border-end">
                            <span class="text-muted small d-block">Exams Taken</span>
                            <strong class="fs-5 text-dark"><?php echo $summary['count']; ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Average Mark</span>
                            <strong class="fs-5 text-success"><?php echo number_format($summary['avg'], 2); ?>%</strong>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <a href="marks.php" class="btn btn-outline-primary w-100 mb-2"><i class="fas fa-book me-2"></i>My Grades Card</a>
                        <a href="reports.php" class="btn btn-primary w-100"><i class="fas fa-print me-2"></i>My Result Report</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Details details card -->
        <div class="col-12 col-lg-8">
            <!-- Info card -->
            <div class="card card-common mb-4">
                <div class="card-header bg-white">
                    <i class="fas fa-info-circle text-info me-2"></i>My Personal Details
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Student ID</span>
                            <strong class="text-dark fs-5"><?php echo sanitize($student['student_id']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Class / Grade</span>
                            <strong class="text-dark fs-5"><?php echo sanitize($student['class']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Full Name</span>
                            <strong class="text-dark"><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Date of Birth</span>
                            <strong class="text-dark"><?php echo sanitize($student['dob']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Gender</span>
                            <strong class="text-dark"><?php echo sanitize($student['gender']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Email Address</span>
                            <strong class="text-dark"><?php echo sanitize($student['email']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Phone Number</span>
                            <strong class="text-dark"><?php echo sanitize($student['phone']); ?></strong>
                        </div>
                        <div class="col-12">
                            <span class="text-muted small d-block">Residential Address</span>
                            <strong class="text-dark"><?php echo sanitize($student['address']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parent Details card -->
            <div class="card card-common">
                <div class="card-header bg-white">
                    <i class="fas fa-user-friends text-info me-2"></i>Parent / Guardian Contacts
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Parent / Guardian Name</span>
                            <strong class="text-dark"><?php echo sanitize($student['parent_name']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Parent Contact Number</span>
                            <strong class="text-dark"><?php echo sanitize($student['parent_phone']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
