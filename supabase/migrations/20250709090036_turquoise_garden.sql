-- FINONEST TeleCRM Database Schema

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    role ENUM('admin', 'agent', 'manager') DEFAULT 'agent',
    agent_id VARCHAR(50),
    department VARCHAR(100),
    shift VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Leads table
CREATE TABLE leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    status ENUM('hot', 'warm', 'cold', 'converted', 'dead') DEFAULT 'cold',
    source VARCHAR(100),
    assigned_to INT,
    notes TEXT,
    last_contact TIMESTAMP NULL,
    next_followup DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_phone (phone)
);

-- Calls table
CREATE TABLE calls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    phone_number VARCHAR(20) NOT NULL,
    disposition ENUM('connected', 'no_answer', 'busy', 'failed', 'converted', 'not_interested', 'callback', 'wrong_number') NOT NULL,
    duration INT DEFAULT 0,
    notes TEXT,
    revenue DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_disposition (disposition),
    INDEX idx_created_at (created_at)
);

-- Campaigns table
CREATE TABLE campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('outbound', 'followup', 'warm_leads') DEFAULT 'outbound',
    status ENUM('active', 'paused', 'completed', 'draft') DEFAULT 'draft',
    start_date DATE,
    end_date DATE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- Campaign leads junction table
CREATE TABLE campaign_leads (
    id INT PRIMARY KEY AUTO_INCREMENT,
    campaign_id INT NOT NULL,
    lead_id INT NOT NULL,
    status ENUM('pending', 'called', 'converted', 'failed') DEFAULT 'pending',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    UNIQUE KEY unique_campaign_lead (campaign_id, lead_id),
    INDEX idx_campaign_id (campaign_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_status (status)
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    type ENUM('whatsapp', 'sms', 'email') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('sent', 'delivered', 'failed', 'pending') DEFAULT 'pending',
    response_received BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Message templates table
CREATE TABLE message_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    type ENUM('whatsapp', 'sms', 'email') NOT NULL,
    category VARCHAR(100),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_category (category)
);

-- Schedule table
CREATE TABLE schedule (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    title VARCHAR(255) NOT NULL,
    type ENUM('call', 'meeting', 'followup', 'reminder') DEFAULT 'call',
    purpose TEXT,
    scheduled_at DATETIME NOT NULL,
    status ENUM('scheduled', 'completed', 'missed', 'cancelled') DEFAULT 'scheduled',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status),
    INDEX idx_priority (priority)
);

-- Activities table (for recent activity tracking)
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    type ENUM('call', 'sms', 'email', 'lead', 'meeting', 'note') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('success', 'failed', 'pending') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);

-- Callbacks table
CREATE TABLE callbacks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT NOT NULL,
    scheduled_at DATETIME NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    notes TEXT,
    status ENUM('pending', 'completed', 'missed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status)
);

-- Conversions table (for tracking successful conversions)
CREATE TABLE conversions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT NOT NULL,
    call_id INT,
    product VARCHAR(255),
    revenue DECIMAL(10, 2) NOT NULL,
    commission DECIMAL(10, 2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at)
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@finonest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample message templates
INSERT INTO message_templates (user_id, name, type, category, message, usage_count) VALUES
(NULL, 'Home Loan Introduction', 'whatsapp', 'introduction', 'Hi {name}, I''m calling regarding your interest in home loans. Our special rates start from 8.5% PA. Are you available for a quick call?', 245),
(NULL, 'Follow-up Reminder', 'whatsapp', 'followup', 'Hello {name}, Hope you''re doing well. Just following up on our conversation about {product}. When would be a good time to discuss further?', 189),
(NULL, 'Document Request', 'whatsapp', 'documents', 'Hi {name}, To proceed with your {product} application, we need the following documents: {documents}. Please share them at your convenience.', 156),
(NULL, 'Appointment Reminder', 'sms', 'reminder', 'Dear {name}, This is a reminder about your appointment scheduled for {date} at {time}. Please confirm your availability. - TeleCRM', 178),
(NULL, 'Thank You Message', 'sms', 'confirmation', 'Thank you {name} for choosing our services. Your application reference number is {ref_number}. We''ll contact you within 24 hours.', 134),
(NULL, 'Loan Proposal', 'email', 'proposal', 'Dear {name},\n\nWe have a special home loan offer tailored for you...', 89);