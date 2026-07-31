<?php
require_once '../config/db.php';
include '../includes/header.php';

$student_id = $_SESSION['student_db_id'];

try {
    // Fetch Student Info
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        header("Location: logout.php");
        exit;
    }

    // Fetch Student Marks history
    $marks_stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? ORDER BY exam_date DESC");
    $marks_stmt->execute([$student_id]);
    $marks_list = $marks_stmt->fetchAll();

    // Calculations
    $total_marks = 0;
    $subjects_count = count($marks_list);
    foreach ($marks_list as $m) {
        $total_marks += $m['marks'];
    }
    $average_marks = $subjects_count > 0 ? number_format($total_marks / $subjects_count, 2) : '0.00';
    $total_marks = number_format($total_marks, 2);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<div class="container-fluid px-0">
    <!-- Action Panel (hidden during print) -->
    <div class="row mb-4 align-items-center print-hide">
        <div class="col-8">
            <h2 class="fw-bold text-dark">My Report Card</h2>
            <p class="text-muted">Generate and print your official academic results transcript.</p>
        </div>
        <div class="col-4 text-end">
            <button onclick="window.print();" class="btn btn-primary shadow-sm"><i class="fas fa-print me-2"></i>Print / Download PDF</button>
        </div>
    </div>

    <!-- Printable Report Card layout -->
    <div class="card card-common p-4 border shadow-sm">
        <div class="card-body">
            
            <!-- Report Card Header -->
            <div class="row align-items-center mb-4 border-bottom pb-4">
                <div class="col-sm-2 text-center text-sm-start mb-3 mb-sm-0">
                    <i class="fas fa-graduation-cap fa-5x text-info"></i>
                </div>
                <div class="col-sm-10 text-center text-sm-start">
                    <h2 class="fw-bold text-dark mb-1">STUDENT ACADEMIC REPORT CARD</h2>
                    <h5 class="text-secondary mb-0">Student Management System</h5>
                    <span class="text-muted small">Generated on: <?php echo date('Y-m-d H:i'); ?></span>
                </div>
            </div>

            <!-- Student Metadata -->
            <h5 class="text-primary fw-bold mb-3">Student & Enrollment Profile</h5>
            <div class="row g-3 mb-5 p-3 bg-light rounded border">
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Student ID</span>
                    <strong class="text-dark"><?php echo sanitize($student['student_id']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Full Name</span>
                    <strong class="text-dark"><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Class / Grade</span>
                    <strong class="text-dark"><?php echo sanitize($student['class']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Date of Birth</span>
                    <strong class="text-dark"><?php echo sanitize($student['dob']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Email Address</span>
                    <strong class="text-dark"><?php echo sanitize($student['email']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Phone Number</span>
                    <strong class="text-dark"><?php echo sanitize($student['phone']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Parent/Guardian Name</span>
                    <strong class="text-dark"><?php echo sanitize($student['parent_name']); ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <span class="text-muted small d-block">Parent Phone Number</span>
                    <strong class="text-dark"><?php echo sanitize($student['parent_phone']); ?></strong>
                </div>
            </div>

            <!-- Marks Transcript -->
            <h5 class="text-primary fw-bold mb-3">Subject Wise Marks Transcript</h5>
            <div class="table-responsive mb-5">
                <table class="table table-bordered table-striped text-center">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-start ps-3">Subject Name</th>
                            <th>Exam Date</th>
                            <th>Marks Obtained</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($subjects_count > 0): ?>
                            <?php foreach ($marks_list as $mark): ?>
                                <tr>
                                    <td class="text-start ps-3 fw-semibold"><?php echo sanitize($mark['subject']); ?></td>
                                    <td><?php echo sanitize($mark['exam_date']); ?></td>
                                    <td class="fw-bold"><?php echo sanitize($mark['marks']); ?>%</td>
                                    <td>
                                        <span class="badge <?php 
                                            echo ($mark['grade'] === 'F') ? 'bg-danger text-white' : (($mark['grade'] === 'A' || $mark['grade'] === 'A+') ? 'bg-success text-white' : 'bg-primary text-white'); 
                                        ?>">
                                            <?php echo sanitize($mark['grade']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="py-4 text-muted">No marks records available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Academic Summaries -->
            <div class="row justify-content-end">
                <div class="col-12 col-md-5">
                    <div class="card bg-light border p-3">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td class="text-muted fw-semibold">Total Exams:</td>
                                <td class="text-end fw-bold text-dark"><?php echo $subjects_count; ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Total Cumulative Marks:</td>
                                <td class="text-end fw-bold text-dark"><?php echo $total_marks; ?></td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-muted fw-bold">Overall Average:</td>
                                <td class="text-end fw-bold text-success fs-5"><?php echo $average_marks; ?>%</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sign-offs (visible in print or styling layout) -->
            <div class="row mt-5 pt-5 border-top text-center">
                <div class="col-6">
                    <p class="mb-4">____________________________</p>
                    <strong class="text-dark">Class Teacher Signature</strong>
                </div>
                <div class="col-6">
                    <p class="mb-4">____________________________</p>
                    <strong class="text-dark">Principal Signature</strong>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Print-only CSS helpers -->
<style>
    @media print {
        .print-hide, #sidebar, .navbar-custom, .sidebar-overlay, .btn {
            display: none !important;
        }
        body {
            background-color: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #main-content {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .bg-light {
            background-color: #f8f9fa !important;
        }
        .table-dark th {
            background-color: #212529 !important;
            color: #ffffff !important;
        }
    }
</style>

<?php include '../includes/footer.php'; ?>
