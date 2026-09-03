-- Nilai Care Medical Centre Appointment System Database
-- Import this file into phpMyAdmin (XAMPP) before running the project

CREATE DATABASE IF NOT EXISTS hospital_appointment_system;
USE hospital_appointment_system;

-- Patients / users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctors table
CREATE TABLE doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    available_day VARCHAR(50) NOT NULL,
    available_time VARCHAR(50) NOT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending','approved','cancelled','completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE,
    INDEX idx_doctor_slot (doctor_id, appointment_date, appointment_time, status)
);

-- Admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Default admin login -> username: admin | password: admin123
INSERT INTO admins (username, password) VALUES
('admin', '$2y$12$W3MsQOvqU2H9vvZ2kyfh3uMnQ0XY4ZAoudMpTIWYrqP2ybis82Dz2');

-- Sample doctors
INSERT INTO doctors (name, specialization, available_day, available_time) VALUES
('Dr. Aisha Rahman', 'Cardiology', 'Mon, Wed, Fri', '9:00 AM - 12:00 PM'),
('Dr. John Lim', 'Dermatology', 'Tue, Thu', '1:00 PM - 4:00 PM'),
('Dr. Sara Menon', 'Pediatrics', 'Mon - Fri', '10:00 AM - 2:00 PM');
