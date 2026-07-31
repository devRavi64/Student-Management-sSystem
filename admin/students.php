<?php
require_once '../config/db.php';
include '../includes/header.php';

// Pagination settings
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$class_filter = isset($_GET['class_filter']) ? trim($_GET['class_filter']) : '';

// Build query conditions
$where_clauses = [];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(first_name LIKE ? OR last_name LIKE ? OR student_id LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($class_filter !== '') {
    $where_clauses[] = "class = ?";
    $params[] = $class_filter;
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch distinct classes for the filter dropdown
try {
    $class_stmt = $pdo->query("SELECT DISTINCT class FROM students ORDER BY class ASC");
    $classes = $class_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $classes = [];
}

// Get total count for pagination
try {
    $count_sql = "SELECT COUNT(*) FROM students $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_records = $count_stmt->fetchColumn();
} catch (PDOException $e) {
    $total_records = 0;
}

$total_pages = ceil($total_records / $limit);
if ($total_pages < 1) $total_pages = 1;
if ($page > $total_pages) $page = $total_pages;

// Fetch filtered students
try {
    $data_sql = "SELECT * FROM students $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $data_stmt = $pdo->prepare($data_sql);
    $data_stmt->execute($params);
    $students = $data_stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
    $error = "Error retrieving student records: " . $e->getMessage();
}
?>

<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark">Students List</h2>
            <p class="text-muted">Manage, search, and view registered students.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="student-add.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Register New Student</a>
        </div>
    </div>

    <!-- Search and Filter Card -->
    <div class="card card-common mb-4">
        <div class="card-body">
            <form action="students.php" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-5">
                    <label for="search" class="form-label small fw-semibold text-muted">Search Name / ID</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control" id="search" name="search" placeholder="e.g. John Doe or SMS-10001" value="<?php echo sanitize($search); ?>">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="class_filter" class="form-label small fw-semibold text-muted">Filter by Class</label>
                    <select class="form-select" id="class_filter" name="class_filter">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $cls): ?>
                            <option value="<?php echo sanitize($cls); ?>" <?php echo ($class_filter === $cls) ? 'selected' : ''; ?>>
                                <?php echo sanitize($cls); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Filter</button>
                    <a href="students.php" class="btn btn-light border w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Table Card -->
    <div class="card card-common">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Student ID</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th class="text-center actions-column">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="ps-4 fw-semibold text-primary"><?php echo sanitize($student['student_id']); ?></td>
                                    <td>
                                        <?php if ($student['profile_photo']): ?>
                                            <img src="../uploads/<?php echo sanitize($student['profile_photo']); ?>" alt="Profile" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-light text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <i class="fas fa-user-circle fa-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo sanitize($student['first_name'] . ' ' . $student['last_name']); ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo sanitize($student['class']); ?></span></td>
                                    <td><?php echo sanitize($student['email']); ?></td>
                                    <td><?php echo sanitize($student['phone']); ?></td>
                                    <td class="text-center actions-column">
                                        <div class="btn-group btn-group-sm">
                                            <a href="student-view.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-info" title="View details"><i class="fas fa-eye"></i></a>
                                            <a href="student-edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-warning" title="Edit student"><i class="fas fa-edit"></i></a>
                                            <a href="student-delete.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-danger" title="Delete student" onclick="return confirm('Are you sure you want to delete this student? All related marks records will also be deleted.')"><i class="fas fa-trash-alt"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 text-secondary"></i>
                                    <p class="mb-0">No student records found matching the criteria.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination Footer -->
        <?php if ($total_pages > 1): ?>
            <div class="card-footer bg-white border-top py-3">
                <nav aria-label="Student list pagination">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&class_filter=<?php echo urlencode($class_filter); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&class_filter=<?php echo urlencode($class_filter); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&class_filter=<?php echo urlencode($class_filter); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
