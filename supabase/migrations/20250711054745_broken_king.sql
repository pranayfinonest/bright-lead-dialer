-- FINONEST TeleCRM Database Schema

-- Users table with enhanced fields
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    bio TEXT,
    role ENUM('admin', 'manager', 'caller') DEFAULT 'caller',
    agent_id VARCHAR(50),
    department VARCHAR(100),
    manager_id INT,
    shift VARCHAR(50),
    active BOOLEAN DEFAULT TRUE,
    otp_secret VARCHAR(255),
    otp_enabled BOOLEAN DEFAULT FALSE,
    failed_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_role (role),
    INDEX idx_manager_id (manager_id),
    INDEX idx_active (active)
);

-- Password resets table
CREATE TABLE password_resets (
    user_id INT PRIMARY KEY,
    otp VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Leads table with enhanced tracking
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
    lead_score INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_assigned_to (assigned_to),
    INDEX idx_phone (phone),
    INDEX idx_source (source),
    INDEX idx_lead_score (lead_score)
);

-- Calls table with comprehensive tracking
CREATE TABLE calls (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    phone_number VARCHAR(20) NOT NULL,
    disposition ENUM('connected', 'no_answer', 'busy', 'failed', 'converted', 'not_interested', 'callback', 'wrong_number') NOT NULL,
    duration INT DEFAULT 0,
    notes TEXT,
    revenue DECIMAL(10, 2) DEFAULT 0,
    call_type ENUM('outbound', 'inbound') DEFAULT 'outbound',
    recording_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_lead_id (lead_id),
    INDEX idx_disposition (disposition),
    INDEX idx_created_at (created_at),
    INDEX idx_call_type (call_type)
);

-- Campaigns table
CREATE TABLE campaigns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('outbound', 'followup', 'warm_leads', 'voice_blast') DEFAULT 'outbound',
    status ENUM('active', 'paused', 'completed', 'draft') DEFAULT 'draft',
    start_date DATE,
    end_date DATE,
    description TEXT,
    target_count INT DEFAULT 0,
    dialer_settings JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_type (type)
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

-- Messages table for multi-channel communication
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    type ENUM('whatsapp', 'sms', 'email') NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    template_id INT,
    status ENUM('sent', 'delivered', 'failed', 'pending') DEFAULT 'pending',
    response_received BOOLEAN DEFAULT FALSE,
    external_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_external_id (external_id)
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
    variables JSON,
    usage_count INT DEFAULT 0,
    is_global BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_category (category),
    INDEX idx_is_global (is_global)
);

-- Schedule table for appointments and callbacks
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
    reminder_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_type (type)
);

-- Activities table for comprehensive logging
CREATE TABLE activities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT,
    type ENUM('call', 'sms', 'email', 'whatsapp', 'lead', 'meeting', 'note', 'login', 'logout') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('success', 'failed', 'pending') DEFAULT 'success',
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
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

-- Conversions table for tracking successful sales
CREATE TABLE conversions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    lead_id INT NOT NULL,
    call_id INT,
    product VARCHAR(255),
    revenue DECIMAL(10, 2) NOT NULL,
    commission DECIMAL(10, 2) DEFAULT 0,
    notes TEXT,
    conversion_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_conversion_date (conversion_date),
    INDEX idx_created_at (created_at)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    action_url VARCHAR(500),
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_read_at (read_at),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type)
);

-- System settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key)
);

-- Integrations table
CREATE TABLE integrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('whatsapp', 'sms', 'email', 'crm', 'api', 'social') NOT NULL,
    status ENUM('active', 'inactive', 'error') DEFAULT 'inactive',
    config JSON,
    api_key VARCHAR(500),
    webhook_url VARCHAR(500),
    last_sync TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status)
);

-- Lead sources table
CREATE TABLE lead_sources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('website', 'social', 'referral', 'advertisement', 'cold_call', 'import') NOT NULL,
    config JSON,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@finonest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Manager User', 'manager@finonest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager'),
('Caller User', 'caller@finonest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'caller');

-- Insert sample message templates
INSERT INTO message_templates (user_id, name, type, category, message, usage_count, is_global) VALUES
(NULL, 'Home Loan Introduction', 'whatsapp', 'introduction', 'Hi {name}, I''m calling regarding your interest in home loans. Our special rates start from 8.5% PA. Are you available for a quick call?', 245, TRUE),
(NULL, 'Follow-up Reminder', 'whatsapp', 'followup', 'Hello {name}, Hope you''re doing well. Just following up on our conversation about {product}. When would be a good time to discuss further?', 189, TRUE),
(NULL, 'Document Request', 'whatsapp', 'documents', 'Hi {name}, To proceed with your {product} application, we need the following documents: {documents}. Please share them at your convenience.', 156, TRUE),
(NULL, 'Appointment Reminder', 'sms', 'reminder', 'Dear {name}, This is a reminder about your appointment scheduled for {date} at {time}. Please confirm your availability. - TeleCRM', 178, TRUE),
(NULL, 'Thank You Message', 'sms', 'confirmation', 'Thank you {name} for choosing our services. Your application reference number is {ref_number}. We''ll contact you within 24 hours.', 134, TRUE),
(NULL, 'Loan Proposal', 'email', 'proposal', 'Dear {name},\n\nWe have a special home loan offer tailored for you...', 89, TRUE);

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'FINONEST', 'string', 'Company name displayed in the system'),
('company_tagline', 'trust comes first', 'string', 'Company tagline'),
('dialer_auto_dial_delay', '3', 'number', 'Delay between auto dial calls in seconds'),
('max_call_duration', '1800', 'number', 'Maximum call duration in seconds'),
('working_hours_start', '09:00', 'string', 'Working hours start time'),
('working_hours_end', '18:00', 'string', 'Working hours end time'),
('timezone', 'Asia/Kolkata', 'string', 'System timezone'),
('currency', 'INR', 'string', 'Default currency'),
('lead_auto_assignment', 'true', 'boolean', 'Enable automatic lead assignment'),
('call_recording_enabled', 'false', 'boolean', 'Enable call recording');

-- Insert default lead sources
INSERT INTO lead_sources (name, type, active) VALUES
('Website Form', 'website', TRUE),
('Facebook Ads', 'social', TRUE),
('Google Ads', 'advertisement', TRUE),
('Referral Program', 'referral', TRUE),
('Cold Calling', 'cold_call', TRUE),
('CSV Import', 'import', TRUE),
('IndiaMART', 'website', TRUE),
('JustDial', 'website', TRUE);