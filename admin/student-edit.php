<?php
require_once '../config/db.php';
include '../includes/header.php';

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: students.php");
    exit;
}

$error = '';
$success = '';

// Load student details
try {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();
    
    if (!$student) {
        header("Location: students.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect & Clean Inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Retrieve optional password
    $class = trim($_POST['class']);
    $parent_name = trim($_POST['parent_name']);
    $parent_phone = trim($_POST['parent_phone']);
    
    $profile_photo = $student['profile_photo']; // Default to current photo
    $upload_ok = true;

    // Server-side validation
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender) || 
        empty($address) || empty($phone) || empty($email) || empty($class) || 
        empty($parent_name) || empty($parent_phone)) {
        $error = "All fields except Profile Photo are required.";
        $upload_ok = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        $upload_ok = false;
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
        $upload_ok = false;
    } elseif (!preg_match("/^[0-9+ \-]{7,20}$/", $phone)) {
        $error = "Please enter a valid student phone number.";
        $upload_ok = false;
    } elseif (!preg_match("/^[0-9+ \-]{7,20}$/", $parent_phone)) {
        $error = "Please enter a valid parent phone number.";
        $upload_ok = false;
    }

    // Check for duplicate email
    if ($upload_ok) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                $error = "This email address is already registered to another student.";
                $upload_ok = false;
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
            $upload_ok = false;
        }
    }

    // Process photo update
    if ($upload_ok && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['profile_photo'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Invalid file type. Only JPG, JPEG, PNG, and GIF allowed.";
            $upload_ok = false;
        } elseif ($file_size > 2 * 1024 * 1024) { // 2MB Limit
            $error = "File size exceeds the limit of 2MB.";
            $upload_ok = false;
        } else {
            // Delete old photo if exists
            if ($student['profile_photo'] && file_exists('../uploads/' . $student['profile_photo'])) {
                unlink('../uploads/' . $student['profile_photo']);
            }
            
            // Generate a unique file name
            $profile_photo = $student['student_id'] . "_" . time() . "." . $file_ext;
            $upload_dir = '../uploads/';
            if (!move_uploaded_file($file_tmp, $upload_dir . $profile_photo)) {
                $error = "Failed to upload profile photo.";
                $upload_ok = false;
            }
        }
    }

    // Update in DB
    if ($upload_ok) {
        try {
            if (!empty($password)) {
                // Update with new password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql = "UPDATE students 
                        SET first_name = ?, last_name = ?, dob = ?, gender = ?, address = ?, phone = ?, email = ?, password = ?, class = ?, parent_name = ?, parent_phone = ?, profile_photo = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $first_name, $last_name, $dob, $gender, $address, $phone, $email, $hashed_password, $class, $parent_name, $parent_phone, $profile_photo, $id
                ]);
            } else {
                // Keep existing password
                $sql = "UPDATE students 
                        SET first_name = ?, last_name = ?, dob = ?, gender = ?, address = ?, phone = ?, email = ?, class = ?, parent_name = ?, parent_phone = ?, profile_photo = ? 
                        WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $first_name, $last_name, $dob, $gender, $address, $phone, $email, $class, $parent_name, $parent_phone, $profile_photo, $id
                ]);
            }

            $success = "Student profile updated successfully!";
            
            // Reload updated student details from DB
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
            $stmt->execute([$id]);
            $student = $stmt->fetch();
            
        } catch (PDOException $e) {
            $error = "Database Update Failed: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col">
            <a href="students.php" class="btn btn-outline-secondary btn-sm mb-3"><i class="fas fa-arrow-left me-2"></i>Back to List</a>
            <h2 class="fw-bold text-dark">Edit Student</h2>
            <p class="text-muted">Updating registration details for student: <?php echo sanitize($student['student_id']); ?></p>
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

    <div class="card card-common">
        <div class="card-header bg-white">
            <i class="fas fa-edit text-warning me-2"></i>Edit Student Form
        </div>
        <div class="card-body">
            <form action="student-edit.php?id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                
                <h5 class="mb-3 text-secondary border-bottom pb-2">Personal Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo sanitize($student['first_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo sanitize($student['last_name']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dob" name="dob" value="<?php echo sanitize($student['dob']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="Male" <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($student['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class / Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="class" name="class" value="<?php echo sanitize($student['class']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo sanitize($student['email']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Portal Password <span class="text-muted">(Leave empty to keep current password)</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="Enter new password to change">
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?php echo sanitize($student['phone']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?php echo sanitize($student['address']); ?></textarea>
                    </div>
                </div>

                <h5 class="mb-3 text-secondary border-bottom pb-2">Parent / Guardian Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parent_name" name="parent_name" value="<?php echo sanitize($student['parent_name']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="parent_phone" class="form-label">Parent/Guardian Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parent_phone" name="parent_phone" value="<?php echo sanitize($student['parent_phone']); ?>" required>
                    </div>
                </div>

                <h5 class="mb-3 text-secondary border-bottom pb-2">Profile Photo</h5>
                <div class="row g-3 mb-4 align-items-center">
                    <div class="col-auto">
                        <?php if ($student['profile_photo']): ?>
                            <img src="../uploads/<?php echo sanitize($student['profile_photo']); ?>" alt="Profile" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light text-secondary rounded border d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="fas fa-user-circle fa-3x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col">
                        <label for="profile_photo" class="form-label">Upload new photo (Optional, will replace existing)</label>
                        <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-warning px-4 py-2"><i class="fas fa-save me-2"></i>Update Student</button>
                    <a href="student-view.php?id=<?php echo $id; ?>" class="btn btn-light px-4 py-2 border ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
