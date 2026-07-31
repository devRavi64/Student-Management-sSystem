<?php
require_once '../config/db.php';
include '../includes/header.php';

$error = '';
$success = '';

// Pre-fill student if student_id is passed in the URL query
$pre_selected_student = isset($_GET['student_id']) && is_numeric($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

// Handle Actions (Add/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';

    if ($action === 'add') {
        $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
        $subject = trim($_POST['subject']);
        $marks = isset($_POST['marks']) ? (float)$_POST['marks'] : 0;
        $grade = trim($_POST['grade']);
        $exam_date = trim($_POST['exam_date']);

        if ($student_id <= 0 || empty($subject) || empty($grade) || empty($exam_date)) {
            $error = "All fields are required.";
        } elseif ($marks < 0 || $marks > 100) {
            $error = "Marks must be between 0 and 100.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO marks (student_id, subject, marks, grade, exam_date) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$student_id, $subject, $marks, $grade, $exam_date]);
                $success = "Marks added successfully!";
            } catch (PDOException $e) {
                $error = "Failed to add marks: " . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_mark') {
        $mark_id = isset($_POST['mark_id']) ? (int)$_POST['mark_id'] : 0;
        $student_id = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
        if ($mark_id > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM marks WHERE id = ?");
                $stmt->execute([$mark_id]);
                $success = "Mark record deleted successfully.";
            } catch (PDOException $e) {
                $error = "Failed to delete record: " . $e->getMessage();
            }
        }
    }
}

// Fetch all students for selector dropdown
try {
    $stmt = $pdo->query("SELECT id, student_id, first_name, last_name, class FROM students ORDER BY class ASC, first_name ASC");
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
}

// Fetch all recorded marks to show in list
try {
    $stmt = $pdo->query("SELECT m.*, s.student_id as stud_code, s.first_name, s.last_name, s.class 
                         FROM marks m 
                         JOIN students s ON m.student_id = s.id 
                         ORDER BY m.id DESC LIMIT 30");
    $all_marks = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_marks = [];
}
?>

<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">Marks Management</h2>
            <p class="text-muted">Enter and manage student grades and exam details.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo sanitize($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo sanitize($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Add Mark Entry Form -->
        <div class="col-12 col-lg-4">
            <div class="card card-common h-100">
                <div class="card-header bg-white">
                    <i class="fas fa-plus-circle text-primary me-2"></i>New Marks Entry
                </div>
                <div class="card-body">
                    <form action="marks.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label for="student_id" class="form-label">Select Student <span class="text-danger">*</span></label>
                            <select class="form-select" id="student_id" name="student_id" required>
                                <option value="" selected disabled>Choose student...</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>" <?php echo ($pre_selected_student === (int)$student['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize($student['student_id'] . " - " . $student['first_name'] . " " . $student['last_name'] . " (" . $student['class'] . ")"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" selected disabled>Choose subject...</option>
                                <option value="Mathematics">Mathematics</option>
                                <option value="Science">Science</option>
                                <option value="English">English</option>
                                <option value="History">History</option>
                                <option value="Geography">Geography</option>
                                <option value="Computer Science">Computer Science</option>
                                <option value="Art">Art</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="marks" class="form-label">Marks (0 - 100) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="marks" name="marks" placeholder="e.g. 85.5" required>
                        </div>

                        <div class="mb-3">
                            <label for="grade" class="form-label">Grade</label>
                            <input type="text" class="form-control bg-light" id="grade" name="grade" readonly placeholder="Auto Calculated" required>
                        </div>

                        <div class="mb-4">
                            <label for="exam_date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="exam_date" name="exam_date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2"><i class="fas fa-save me-2"></i>Save Grade</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Marks Table -->
        <div class="col-12 col-lg-8">
            <div class="card card-common h-100">
                <div class="card-header">
                    <i class="fas fa-history text-primary me-2"></i>Recent Grade Entries
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Student ID</th>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Exam Date</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($all_marks) > 0): ?>
                                    <?php foreach ($all_marks as $mark): ?>
                                        <tr>
                                            <td class="ps-4 fw-semibold text-primary"><?php echo sanitize($mark['stud_code']); ?></td>
                                            <td><strong><?php echo sanitize($mark['first_name'] . ' ' . $mark['last_name']); ?></strong></td>
                                            <td><?php echo sanitize($mark['subject']); ?></td>
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
                                                <form action="marks.php" method="POST" onsubmit="return confirm('Delete this mark entry?')">
                                                    <input type="hidden" name="action" value="delete_mark">
                                                    <input type="hidden" name="mark_id" value="<?php echo $mark['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No marks recorded yet. Use the form to add grades.</td>
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
