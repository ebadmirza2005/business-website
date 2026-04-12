-- Orders table for payment tracking
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    package_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    session_id VARCHAR(255) UNIQUE NOT NULL,
    payment_intent VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_session (session_id),
    INDEX idx_status (status),
    INDEX idx_payment_intent (payment_intent)
);

-- Add indexes for faster queries
ALTER TABLE orders ADD INDEX idx_created_at (created_at) IF NOT EXISTS;
ALTER TABLE orders ADD INDEX idx_status_created (status, created_at) IF NOT EXISTS;
