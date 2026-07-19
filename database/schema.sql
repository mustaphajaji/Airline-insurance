-- ============================================================
-- Airline Insurance — Database Schema
-- Import via phpMyAdmin or: mysql -u root < schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS airline_insurance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE airline_insurance;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME DEFAULT NULL,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE policy_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_code VARCHAR(20) NOT NULL UNIQUE,
    category ENUM('flight_delay','flight_cancellation','lost_baggage','personal_accident','medical_emergency','other') NOT NULL,
    plan_name VARCHAR(120) NOT NULL,
    description TEXT,
    coverage_amount DECIMAL(12,2) NOT NULL,
    premium_amount DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL DEFAULT 365,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE policies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_number VARCHAR(30) DEFAULT NULL UNIQUE,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    airline_name VARCHAR(100) DEFAULT NULL,
    flight_number VARCHAR(20) DEFAULT NULL,
    departure_city VARCHAR(100) DEFAULT NULL,
    destination_city VARCHAR(100) DEFAULT NULL,
    flight_date DATE DEFAULT NULL,
    coverage_amount DECIMAL(12,2) NOT NULL,
    premium_amount DECIMAL(10,2) NOT NULL,
    beneficiary_name VARCHAR(100) DEFAULT NULL,
    beneficiary_relationship VARCHAR(50) DEFAULT NULL,
    notes TEXT,
    status ENUM('pending','approved','rejected','active','expired','cancelled') NOT NULL DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES policy_plans(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    policy_id INT NOT NULL,
    user_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('card','bank_transfer') NOT NULL DEFAULT 'card',
    card_last4 VARCHAR(4) DEFAULT NULL,
    transaction_ref VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    claim_number VARCHAR(30) NOT NULL UNIQUE,
    policy_id INT NOT NULL,
    user_id INT NOT NULL,
    claim_type VARCHAR(50) NOT NULL,
    incident_date DATE NOT NULL,
    description TEXT NOT NULL,
    amount_claimed DECIMAL(12,2) NOT NULL,
    amount_approved DECIMAL(12,2) DEFAULT NULL,
    document_path VARCHAR(255) DEFAULT NULL,
    document_name VARCHAR(255) DEFAULT NULL,
    status ENUM('submitted','under_review','approved','rejected','paid') NOT NULL DEFAULT 'submitted',
    admin_notes TEXT,
    reviewed_by INT DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (policy_id) REFERENCES policies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed: Admin password=Admin@12345  Customer password=Customer@123
INSERT INTO users (full_name,email,phone,password_hash,role,status) VALUES
('System Administrator','admin@airlineinsurance.com','+1 555 010 0001','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','active'),
('Sarah Mitchell','customer@airlineinsurance.com','+1 555 010 0002','$2y$10$TKh8H1.PfuAa3jVYLBOE2.PYAqNGSJkCXN.j0HiJOWyGGpv/cP3.','customer','active');

INSERT INTO policy_plans (plan_code,category,plan_name,description,coverage_amount,premium_amount,duration_days,status) VALUES
('FD-100','flight_delay','Flight Delay Shield','Compensation for covered delays of 3 hours or more, including meals and rebooking costs.',1500.00,18.00,365,'active'),
('FC-200','flight_cancellation','Cancellation Cover Plus','Reimbursement for non-refundable tickets and reasonable rebooking expenses when a flight is cancelled.',4000.00,32.00,365,'active'),
('LB-300','lost_baggage','Baggage Protect','Covers lost, damaged or delayed baggage including essential item replacement.',2000.00,14.00,365,'active'),
('PA-400','personal_accident','Personal Accident Guard','Lump-sum benefit for accidental injury or death occurring during air travel.',50000.00,45.00,365,'active'),
('ME-500','medical_emergency','Travel Medical Care','Covers emergency medical treatment, hospitalisation and medical evacuation while travelling.',25000.00,38.00,365,'active'),
('OT-600','other','Trip Hardship Cover','A flexible plan covering other unexpected travel disruptions causing financial hardship.',3000.00,22.00,365,'active');

INSERT INTO notifications (user_id,title,message,is_read) VALUES
(2,'Welcome to Airline Insurance','Your account has been created. Browse our plans to get covered for your next flight.',0);
