<?php
require_once '../config/db.php';
include '../includes/header.php';

$student_id = $_SESSION['student_db_id'];

try {
    // Fetch marks list
    $stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? ORDER BY exam_date DESC");
    $stmt->execute([$student_id]);
    $marks_list = $stmt->fetchAll();

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
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">My Academic Grades</h2>
            <p class="text-muted">Review your scores, marks, and grades card details.</p>
        </div>
    </div>

    <!-- Stats Cards for Marks -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-stat bg-white h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Subjects / Exams Taken</span>
                        <h3 class="fw-bold text-dark mt-1"><?php echo $subjects_count; ?></h3>
                    </div>
                    <div class="p-3 bg-light text-primary rounded-3">
                        <i class="fas fa-file-invoice fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-stat bg-white h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Total Marks Obtained</span>
                        <h3 class="fw-bold text-dark mt-1"><?php echo $total_marks; ?></h3>
                    </div>
                    <div class="p-3 bg-light text-success rounded-3">
                        <i class="fas fa-calculator fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-stat bg-white h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Overall Average Marks</span>
                        <h3 class="fw-bold text-dark mt-1"><?php echo $average_marks; ?>%</h3>
                    </div>
                    <div class="p-3 bg-light text-warning rounded-3">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marks Table Card -->
    <div class="card card-common">
        <div class="card-header bg-white">
            <i class="fas fa-graduation-cap text-info me-2"></i>My Results List
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Subject</th>
                            <th>Exam Date</th>
                            <th>Marks Obtained</th>
                            <th class="pe-4">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($subjects_count > 0): ?>
                            <?php foreach ($marks_list as $mark): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-dark"><?php echo sanitize($mark['subject']); ?></td>
                                    <td><?php echo sanitize($mark['exam_date']); ?></td>
                                    <td class="fw-bold"><?php echo sanitize($mark['marks']); ?>%</td>
                                    <td class="pe-4">
                                        <span class="badge <?php 
                                            echo ($mark['grade'] === 'F') ? 'bg-danger' : (($mark['grade'] === 'A' || $mark['grade'] === 'A+') ? 'bg-success' : 'bg-primary'); 
                                        ?>">
                                            <?php echo sanitize($mark['grade']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No marks records available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
