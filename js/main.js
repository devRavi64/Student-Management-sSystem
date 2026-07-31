// Student Management System - Main Javascript File

document.addEventListener('DOMContentLoaded', function () {
    
    // Sidebar toggle for mobile/responsive layout
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');
    
    if (sidebarToggle && sidebar && mainContent) {
        // Create sidebar overlay if it doesn't exist
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }
        
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('active');
            overlay.classList.toggle('active');
        });
        
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('active');
            mainContent.classList.remove('active');
            overlay.classList.remove('active');
        });
    }

    // Auto calculate grades and total marks in Marks Management form if elements exist
    const marksInput = document.getElementById('marks');
    const gradeInput = document.getElementById('grade');
    
    if (marksInput && gradeInput) {
        marksInput.addEventListener('input', function() {
            const mark = parseFloat(this.value);
            if (!isNaN(mark)) {
                if (mark >= 90 && mark <= 100) {
                    gradeInput.value = 'A+';
                } else if (mark >= 80) {
                    gradeInput.value = 'A';
                } else if (mark >= 70) {
                    gradeInput.value = 'B';
                } else if (mark >= 60) {
                    gradeInput.value = 'C';
                } else if (mark >= 50) {
                    gradeInput.value = 'D';
                } else if (mark >= 0 && mark < 50) {
                    gradeInput.value = 'F';
                } else {
                    gradeInput.value = 'Invalid';
                }
            } else {
                gradeInput.value = '';
            }
        });
    }

    // Client-side simple validation for forms (Bootstrap 5 standard starter)
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
