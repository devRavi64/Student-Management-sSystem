<?php
$current_page = basename($_SERVER['PHP_SELF']);
$folder = basename(dirname($_SERVER['PHP_SELF']));
$base_path = ($folder === 'admin' || $folder === 'student') ? '../' : './';
$public_pages = ['login.php', 'register.php'];

$is_authenticated = (isset($_SESSION['admin_logged_in']) && $folder === 'admin') || (isset($_SESSION['student_logged_in']) && $folder === 'student');
?>

<?php if ($is_authenticated && !in_array($current_page, $public_pages)): ?>
    </div> <!-- End of #main-content -->
</div> <!-- End of .d-flex -->
<?php endif; ?>

<!-- Bootstrap 5 Bundle JS with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?php echo $base_path; ?>js/main.js"></script>
</body>
</html>
