<?php
require_once '../config/db.php';
include '../includes/header.php';

// Fetch stats
try {
    // 1. Total Students
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    $total_students = $stmt->fetchColumn();

    // 2. Total Classes
    $stmt = $pdo->query("SELECT COUNT(DISTINCT class) FROM students");
    $total_classes = $stmt->fetchColumn();

    // 3. Average Marks
    $stmt = $pdo->query("SELECT AVG(marks) FROM marks");
    $avg_marks = $stmt->fetchColumn();
    $avg_marks = $avg_marks ? number_format($avg_marks, 2) : '0.00';

    // 4. Recent Students
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");
    $recent_students = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error fetching dashboard statistics: " . $e->getMessage());
}
?>

<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">Admin Dashboard</h2>
            <p class="text-muted">Manage students, entries, grades, and system settings.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <!-- Total Students -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-stat bg-white h-100 border-start border-primary border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Total Students</span>
                        <h2 class="display-6 fw-bold mb-0 text-dark mt-1"><?php echo $total_students; ?></h2>
                    </div>
                    <div class="p-3 bg-light text-primary rounded-3">
                        <i class="fas fa-user-graduate fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Classes -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-stat bg-white h-100 border-start border-success border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Active Classes</span>
                        <h2 class="display-6 fw-bold mb-0 text-dark mt-1"><?php echo $total_classes; ?></h2>
                    </div>
                    <div class="p-3 bg-light text-success rounded-3">
                        <i class="fas fa-school fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Marks -->
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card card-stat bg-white h-100 border-start border-warning border-4">
                <div class="card-body d-flex align-items-center justify-content-between p-4">
                    <div>
                        <span class="text-muted text-uppercase small fw-bold">Average Marks</span>
                        <h2 class="display-6 fw-bold mb-0 text-dark mt-1"><?php echo $avg_marks; ?>%</h2>
                    </div>
                    <div class="p-3 bg-light text-warning rounded-3">
                        <i class="fas fa-chart-bar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Section -->
    <div class="card card-common mb-5">
        <div class="card-header text-dark">
            <i class="fas fa-bolt text-danger me-2"></i>Quick Actions
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <a href="student-add.php" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                        <i class="fas fa-user-plus fa-lg"></i>
                        <span>Add Student</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="students.php" class="btn btn-outline-success w-100 py-3 d-flex flex-column align-items-center gap-2">
                        <i class="fas fa-users fa-lg"></i>
                        <span>View Students</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="marks.php" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center gap-2">
                        <i class="fas fa-edit fa-lg"></i>
                        <span>Enter Marks</span>
                    </a>
                </div>
                <div class="col-6 col-md-3">
                    <a href="reports.php" class="btn btn-outline-info w-100 py-3 d-flex flex-column align-items-center gap-2">
                        <i class="fas fa-file-invoice fa-lg"></i>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Students Table -->
    <div class="card card-common">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Recent Enrollments</span>
            <a href="students.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_students) > 0): ?>
                            <?php foreach ($recent_students as $student): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary"><?php echo sanitize($student['student_id']); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($student['profile_photo']): ?>
                                                <img src="../uploads/<?php echo sanitize($student['profile_photo']); ?>" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user-circle"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo sanitize($student['class']); ?></span></td>
                                    <td><?php echo sanitize($student['email']); ?></td>
                                    <td><?php echo sanitize($student['phone']); ?></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group btn-group-sm">
                                            <a href="student-view.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-info" title="View details"><i class="fas fa-eye"></i></a>
                                            <a href="student-edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-warning" title="Edit student"><i class="fas fa-edit"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No students enrolled yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
