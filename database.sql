-- Student Management System Database Schema

CREATE DATABASE IF NOT EXISTS `student_management_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `student_management_db`;

-- 1. Users Table (for Admin authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Students Table (Updated with Password)
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` VARCHAR(20) NOT NULL UNIQUE, -- Auto Generated string, e.g., SMS-10001
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Added password column for student login
  `dob` DATE NOT NULL,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `address` TEXT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `class` VARCHAR(50) NOT NULL,
  `parent_name` VARCHAR(100) NOT NULL,
  `parent_phone` VARCHAR(20) NOT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Marks Table
CREATE TABLE IF NOT EXISTS `marks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL, -- Foreign key referencing students.id
  `subject` VARCHAR(100) NOT NULL,
  `marks` DECIMAL(5,2) NOT NULL,
  `grade` VARCHAR(5) NOT NULL,
  `exam_date` DATE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user: admin@sms.com / adminpassword
INSERT INTO `users` (`name`, `email`, `password`) VALUES
('System Admin', 'admin@sms.com', '$2y$10$wO3.7l9yGvToxh9g8T4NLez7aWv.5m8sKkW5uYp6xXFq4V3fG4v1S')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert default student: SMS-10001 / studentpassword (hash: $2y$10$Vv1M8g8eD6sB.9q8E9lTyejN8T6133hVjQ1vR8zS4N/t3yK6H/3Cq)
-- Let's define: email: student@sms.com, password: studentpassword
INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `email`, `password`, `dob`, `gender`, `address`, `phone`, `class`, `parent_name`, `parent_phone`) VALUES
('SMS-10001', 'John', 'Doe', 'student@sms.com', '$2y$10$Vv1M8g8eD6sB.9q8E9lTyejN8T6133hVjQ1vR8zS4N/t3yK6H/3Cq', '2010-05-15', 'Male', '123 Main St, New York', '555-0199', 'Grade 10', 'Robert Doe', '555-0188')
ON DUPLICATE KEY UPDATE `email`=`email`;
