-- DentConsent MySQL Database Schema
-- Compatible with XAMPP (MySQL/MariaDB)

CREATE DATABASE IF NOT EXISTS dent_consent;
USE dent_consent;

-- 1. Users Table (Core authentication and role management)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'doctor', 'patient') NOT NULL DEFAULT 'patient',
    fcm_token VARCHAR(255) NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (email),
    INDEX idx_user_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Doctor Profiles (Professional details)
CREATE TABLE IF NOT EXISTS doctor_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(15) NULL,
    gender ENUM('Male', 'Female', 'Other') NULL,
    council_id VARCHAR(50) NOT NULL UNIQUE,
    specialization VARCHAR(100) NOT NULL,
    experience_years INT DEFAULT 0,
    qualifications TEXT NULL, -- Stored as comma-separated or JSON string
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_doctor_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Patient Profiles (Identity and contact info)
CREATE TABLE IF NOT EXISTS patient_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(15) NULL,
    dob DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    residential_address TEXT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    allergies TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_patient_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_patient_mobile (mobile_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Medical Conditions (Link to patients)
CREATE TABLE IF NOT EXISTS patient_medical_conditions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NOT NULL,
    condition_name VARCHAR(100) NOT NULL, -- e.g., 'Diabetes', 'Hypertension'
    details TEXT NULL,
    CONSTRAINT fk_medical_patient FOREIGN KEY (patient_id) REFERENCES patient_profiles(user_id) ON DELETE CASCADE,
    INDEX idx_condition_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Treatments (The core workflow entity)
CREATE TABLE IF NOT EXISTS treatments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    doctor_id BIGINT UNSIGNED NOT NULL,
    patient_id BIGINT UNSIGNED NOT NULL,
    category VARCHAR(100) NOT NULL, -- e.g., 'Single Tooth', 'Multiple Tooth'
    status ENUM('pending', 'consent_sent', 'signed', 'completed', 'cancelled') DEFAULT 'pending',
    clinical_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_treatment_doctor FOREIGN KEY (doctor_id) REFERENCES doctor_profiles(user_id) ON DELETE RESTRICT,
    CONSTRAINT fk_treatment_patient FOREIGN KEY (patient_id) REFERENCES patient_profiles(user_id) ON DELETE RESTRICT,
    INDEX idx_treatment_doctor (doctor_id),
    INDEX idx_treatment_patient (patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Consent Records (Audit trail of the education and signature)
CREATE TABLE IF NOT EXISTS consent_records (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    treatment_id BIGINT UNSIGNED NOT NULL UNIQUE,
    quiz_score INT NOT NULL,
    total_questions INT NOT NULL,
    is_checklist_confirmed BOOLEAN DEFAULT FALSE,
    signature_path VARCHAR(255) NULL, -- Path to stored signature image
    signed_at DATETIME NULL,
    ip_address VARCHAR(45) NULL, -- Supports IPv6
    user_agent TEXT NULL,        -- Device info for legal record
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consent_treatment FOREIGN KEY (treatment_id) REFERENCES treatments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Audit Logs (Healthcare compliance)
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(255) NOT NULL,
    entity_name VARCHAR(50) NULL,
    entity_id BIGINT UNSIGNED NULL,
    changes JSON NULL, -- Track before/after for sensitive data
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Messages Table (Patient-Doctor communication)
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id BIGINT UNSIGNED NOT NULL,
    receiver_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_receiver FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_msg_sender (sender_id),
    INDEX idx_msg_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
