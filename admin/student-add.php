<?php
require_once '../config/db.php';
include '../includes/header.php';

$error = '';
$success = '';

// Auto-generate Student ID
try {
    $stmt = $pdo->query("SELECT MAX(id) FROM students");
    $max_id = $stmt->fetchColumn() ?: 0;
    $next_id = $max_id + 10001;
    $auto_student_id = "SMS-" . $next_id;
} catch (PDOException $e) {
    $auto_student_id = "SMS-10001";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and clean inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $dob = trim($_POST['dob']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Retrieve password
    $class = trim($_POST['class']);
    $parent_name = trim($_POST['parent_name']);
    $parent_phone = trim($_POST['parent_phone']);
    
    // File Upload handling
    $profile_photo = null;
    $upload_ok = true;

    // Server-side validation
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($gender) || 
        empty($address) || empty($phone) || empty($email) || empty($password) || empty($class) || 
        empty($parent_name) || empty($parent_phone)) {
        $error = "All fields except Profile Photo are required.";
        $upload_ok = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
        $upload_ok = false;
    } elseif (strlen($password) < 6) {
        $error = "Student Password must be at least 6 characters long.";
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
            $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = "This email address is already registered to a student.";
                $upload_ok = false;
            }
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
            $upload_ok = false;
        }
    }

    // File Upload Validation & Execution
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
            // Generate a unique file name
            $profile_photo = $auto_student_id . "_" . time() . "." . $file_ext;
            $upload_dir = '../uploads/';
            if (!move_uploaded_file($file_tmp, $upload_dir . $profile_photo)) {
                $error = "Failed to upload profile photo.";
                $upload_ok = false;
            }
        }
    }

    // Insert to DB
    if ($upload_ok) {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $sql = "INSERT INTO students (student_id, first_name, last_name, email, password, dob, gender, address, phone, class, parent_name, parent_phone, profile_photo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $auto_student_id,
                $first_name,
                $last_name,
                $email,
                $hashed_password,
                $dob,
                $gender,
                $address,
                $phone,
                $class,
                $parent_name,
                $parent_phone,
                $profile_photo
            ]);

            $success = "Student {$auto_student_id} registered successfully!";
            
            // Re-generate next Student ID for next entry
            $stmt = $pdo->query("SELECT MAX(id) FROM students");
            $max_id = $stmt->fetchColumn() ?: 0;
            $next_id = $max_id + 10001;
            $auto_student_id = "SMS-" . $next_id;
            
        } catch (PDOException $e) {
            $error = "Database Insertion Failed: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid px-0">
    <div class="row mb-4">
        <div class="col">
            <h2 class="fw-bold text-dark">Register Student</h2>
            <p class="text-muted">Enroll a new student to the system.</p>
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
            <i class="fas fa-user-plus text-primary me-2"></i>Registration Form
        </div>
        <div class="card-body">
            <form action="student-add.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                
                <!-- Student Auto ID Banner -->
                <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <span class="text-muted small">Generated Student ID:</span>
                        <strong class="text-primary fs-5 ms-2"><?php echo sanitize($auto_student_id); ?></strong>
                    </div>
                    <span class="badge bg-primary">Auto Generated</span>
                </div>

                <h5 class="mb-3 text-secondary border-bottom pb-2">Personal Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="dob" name="dob" required>
                    </div>
                    <div class="col-md-4">
                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="" selected disabled>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class / Grade <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="class" name="class" placeholder="e.g. Grade 10" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Student Portal Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" minlength="6" placeholder="At least 6 characters" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="e.g. 0123456789" required>
                    </div>
                    <div class="col-12">
                        <label for="address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                </div>

                <h5 class="mb-3 text-secondary border-bottom pb-2">Parent / Guardian Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="parent_name" class="form-label">Parent/Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parent_name" name="parent_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="parent_phone" class="form-label">Parent/Guardian Phone <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parent_phone" name="parent_phone" required>
                    </div>
                </div>

                <h5 class="mb-3 text-secondary border-bottom pb-2">Profile Photo (Optional)</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="profile_photo" class="form-label">Choose File (JPG, PNG, GIF - Max 2MB)</label>
                        <input class="form-control" type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4 py-2"><i class="fas fa-save me-2"></i>Save Student</button>
                    <a href="students.php" class="btn btn-light px-4 py-2 border ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
