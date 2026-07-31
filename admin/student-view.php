<?php
require_once '../config/db.php';
include '../includes/header.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: students.php");
    exit;
}

try {
    // Fetch Student Info
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        header("Location: students.php");
        exit;
    }

    // Fetch Student Marks history
    $marks_stmt = $pdo->prepare("SELECT * FROM marks WHERE student_id = ? ORDER BY exam_date DESC");
    $marks_stmt->execute([$id]);
    $marks_list = $marks_stmt->fetchAll();

    // Calculations
    $total_marks = 0;
    $subjects_count = count($marks_list);
    foreach ($marks_list as $m) {
        $total_marks += $m['marks'];
    }
    $average_marks = $subjects_count > 0 ? number_format($total_marks / $subjects_count, 2) : '0.00';

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<div class="container-fluid px-0">
    <!-- Back Button / Header -->
    <div class="row mb-4">
        <div class="col">
            <a href="students.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
            <h2 class="fw-bold text-dark">Student Profile</h2>
            <p class="text-muted">Viewing details for student: <?php echo sanitize($student['student_id']); ?></p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Card - Profile Overview -->
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
                    <span class="badge bg-primary px-3 py-2 mb-4"><?php echo sanitize($student['class']); ?></span>

                    <hr class="my-4">

                    <!-- Basic Quick Stats -->
                    <div class="row g-2">
                        <div class="col-6 border-end">
                            <span class="text-muted small d-block">Subjects Taken</span>
                            <strong class="fs-5 text-dark"><?php echo $subjects_count; ?></strong>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Average Mark</span>
                            <strong class="fs-5 text-success"><?php echo $average_marks; ?>%</strong>
                        </div>
                    </div>

                    <div class="mt-4 pt-2">
                        <a href="student-edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning w-100 mb-2"><i class="fas fa-edit me-2"></i>Edit Details</a>
                        <a href="student-delete.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this student?')"><i class="fas fa-trash-alt me-2"></i>Delete Profile</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details Card -->
        <div class="col-12 col-lg-8">
            <div class="card card-common mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle text-primary me-2"></i>Personal & Contact Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">First Name</span>
                            <strong class="text-dark"><?php echo sanitize($student['first_name']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Last Name</span>
                            <strong class="text-dark"><?php echo sanitize($student['last_name']); ?></strong>
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
                            <span class="text-muted small d-block">Address</span>
                            <strong class="text-dark"><?php echo sanitize($student['address']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-common mb-4">
                <div class="card-header">
                    <i class="fas fa-user-friends text-primary me-2"></i>Parent / Guardian Details
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Parent / Guardian Name</span>
                            <strong class="text-dark"><?php echo sanitize($student['parent_name']); ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small d-block">Parent Phone Number</span>
                            <strong class="text-dark"><?php echo sanitize($student['parent_phone']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Performance / Marks Card -->
            <div class="card card-common">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-graduation-cap text-primary me-2"></i>Academic Marks History</span>
                    <a href="marks.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Add Marks</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Exam Date</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($subjects_count > 0): ?>
                                    <?php foreach ($marks_list as $mark): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold"><?php echo sanitize($mark['subject']); ?></td>
                                            <td><?php echo sanitize($mark['marks']); ?>%</td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo ($mark['grade'] === 'F') ? 'bg-danger' : (($mark['grade'] === 'A' || $mark['grade'] === 'A+') ? 'bg-success' : 'bg-primary'); 
                                                ?>">
                                                    <?php echo sanitize($mark['grade']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo sanitize($mark['exam_date']); ?></td>
                                            <td class="text-end pe-4">
                                                <!-- Delete button for specific mark -->
                                                <form action="marks.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this mark entry?')">
                                                    <input type="hidden" name="action" value="delete_mark">
                                                    <input type="hidden" name="mark_id" value="<?php echo $mark['id']; ?>">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No marks recorded yet.</td>
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
