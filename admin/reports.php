<?php
require_once '../config/db.php';
include '../includes/header.php';

$class_filter = isset($_GET['class_filter']) ? trim($_GET['class_filter']) : '';

// 1. Fetch Class Wise Summaries
try {
    $class_summary_sql = "SELECT s.class, 
                                 COUNT(DISTINCT s.id) as total_students,
                                 COALESCE(AVG(m.marks), 0) as avg_marks
                          FROM students s 
                          LEFT JOIN marks m ON s.id = m.student_id 
                          GROUP BY s.class 
                          ORDER BY s.class ASC";
    $class_summary_stmt = $pdo->query($class_summary_sql);
    $class_summaries = $class_summary_stmt->fetchAll();
} catch (PDOException $e) {
    $class_summaries = [];
}

// 2. Fetch distinct classes for dropdown
try {
    $classes_stmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class ASC");
    $classes = $classes_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $classes = [];
}

// 3. Fetch Student Performance Reports
try {
    $student_report_params = [];
    $student_report_sql = "SELECT s.id, s.student_id, s.first_name, s.last_name, s.class, 
                                  COUNT(m.id) as subjects_count,
                                  COALESCE(SUM(m.marks), 0) as total_marks,
                                  COALESCE(AVG(m.marks), 0) as avg_marks
                           FROM students s
                           LEFT JOIN marks m ON s.id = m.student_id ";
    
    if ($class_filter !== '') {
        $student_report_sql .= " WHERE s.class = ? ";
        $student_report_params[] = $class_filter;
    }
    
    $student_report_sql .= " GROUP BY s.id ORDER BY avg_marks DESC, total_marks DESC";
    $student_report_stmt = $pdo->prepare($student_report_sql);
    $student_report_stmt->execute($student_report_params);
    $student_reports = $student_report_stmt->fetchAll();
} catch (PDOException $e) {
    $student_reports = [];
}
?>

<div class="container-fluid px-0">
    <!-- Header Controls -->
    <div class="row mb-4 align-items-center">
        <div class="col-8">
            <h2 class="fw-bold text-dark">Academic Reports</h2>
            <p class="text-muted">Generate, analyze, and print academic reports.</p>
        </div>
        <div class="col-4 text-end">
            <button onclick="window.print();" class="btn btn-outline-primary shadow-sm"><i class="fas fa-print me-2"></i>Print Report</button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card card-common mb-5">
        <div class="card-body">
            <form action="reports.php" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-8">
                    <label for="class_filter" class="form-label small fw-semibold text-muted">Filter by Class for Performance List</label>
                    <select class="form-select" id="class_filter" name="class_filter">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?php echo sanitize($cls); ?>" <?php echo ($class_filter === $cls) ? 'selected' : ''; ?>>
                                <?php echo sanitize($cls); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-2"></i>Filter Report</button>
                    <a href="reports.php" class="btn btn-light border w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Class-wise Summary Table -->
        <div class="col-12 col-lg-5">
            <div class="card card-common">
                <div class="card-header bg-white">
                    <i class="fas fa-chart-pie text-primary me-2"></i>Class Wise Enrollment & Performance Summary
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Class</th>
                                    <th>Total Students</th>
                                    <th class="text-end pe-4">Class Avg Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($class_summaries) > 0): ?>
                                    <?php foreach ($class_summaries as $summary): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold"><?php echo sanitize($summary['class']); ?></td>
                                            <td><?php echo sanitize($summary['total_students']); ?></td>
                                            <td class="text-end pe-4 fw-bold text-success"><?php echo number_format($summary['avg_marks'], 2); ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">No classes recorded yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Performance Table -->
        <div class="col-12 col-lg-7">
            <div class="card card-common">
                <div class="card-header bg-white">
                    <i class="fas fa-trophy text-primary me-2"></i>Student Performance Leaderboard
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Rank</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Exams Taken</th>
                                    <th>Avg Mark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($student_reports) > 0): ?>
                                    <?php 
                                    $rank = 1;
                                    foreach ($student_reports as $report): 
                                    ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted"><?php echo $rank++; ?></td>
                                            <td class="text-primary fw-semibold"><?php echo sanitize($report['student_id']); ?></td>
                                            <td><strong><?php echo sanitize($report['first_name'] . ' ' . $report['last_name']); ?></strong></td>
                                            <td><span class="badge bg-light text-dark border"><?php echo sanitize($report['class']); ?></span></td>
                                            <td><?php echo sanitize($report['subjects_count']); ?></td>
                                            <td class="fw-bold text-success"><?php echo number_format($report['avg_marks'], 2); ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No reports matching criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
