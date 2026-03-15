-- Create table for single-page donation app with personal password editing
CREATE TABLE IF NOT EXISTS donations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    organization VARCHAR(200) DEFAULT '',
    phone VARCHAR(20) DEFAULT '',
    amount DECIMAL(10,2) NOT NULL,
    donor_password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If table already exists from old version, run this migration once
ALTER TABLE donations
    ADD COLUMN donor_password_hash VARCHAR(255) NOT NULL AFTER amount;
