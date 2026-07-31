<?php
require_once '../config/db.php';
session_start();

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    try {
        // Fetch profile photo path first to delete file
        $stmt = $pdo->prepare("SELECT profile_photo FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetchColumn();

        if ($photo && file_exists('../uploads/' . $photo)) {
            unlink('../uploads/' . $photo);
        }

        // Delete student (Database cascade will handle deleting related marks records)
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);

    } catch (PDOException $e) {
        die("Error deleting student: " . $e->getMessage());
    }
}

header("Location: students.php");
exit;
?>
