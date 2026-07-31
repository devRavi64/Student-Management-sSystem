<?php
session_start();

// If admin is logged in, redirect to admin dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin/index.php");
    exit;
}

// If student is logged in, redirect to student dashboard
if (isset($_SESSION['student_logged_in'])) {
    header("Location: student/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Gateway</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;450;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f1f5f9;
        }
        .portal-card {
            background-color: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            transition: all 0.3s ease;
        }
        .portal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.2);
        }
        .btn-portal {
            transition: all 0.2s ease;
        }
        .btn-portal:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="text-center mb-5">
        <div class="d-inline-block p-3 rounded-circle bg-primary bg-opacity-10 mb-3">
            <i class="fas fa-graduation-cap fa-4x text-primary"></i>
        </div>
        <h1 class="fw-bold">Student Management System</h1>
        <p class="text-muted">Welcome! Please select your portal login below.</p>
    </div>

    <div class="row justify-content-center g-4">
        <!-- Admin Portal Option -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card portal-card h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="mb-4">
                        <i class="fas fa-user-shield fa-4x text-warning mb-3"></i>
                        <h3 class="fw-bold">Admin Portal</h3>
                        <p class="text-muted small">Access dashboard to manage students, input marks, and view summaries.</p>
                    </div>
                    <a href="admin/login.php" class="btn btn-warning w-100 py-2.5 fw-semibold text-dark btn-portal">
                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Student Portal Option -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card portal-card h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="mb-4">
                        <i class="fas fa-user-graduate fa-4x text-info mb-3"></i>
                        <h3 class="fw-bold">Student Portal</h3>
                        <p class="text-muted small">Access personal profile, review subjects grade cards, and print reports.</p>
                    </div>
                    <a href="student/login.php" class="btn btn-info w-100 py-2.5 fw-semibold text-dark btn-portal">
                        <i class="fas fa-sign-in-alt me-2"></i>Student Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
