-- Online Voting System database schema
-- Compatible with MySQL 8.0+ and MariaDB 10.6+

CREATE DATABASE IF NOT EXISTS online_voting
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE online_voting;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    role ENUM('admin', 'candidate', 'voter') NOT NULL DEFAULT 'voter',
    voter_id VARCHAR(40) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_voter_id (voter_id),
    KEY idx_users_role_active (role, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidates (
    candidateID INT UNSIGNED NOT NULL AUTO_INCREMENT,
    userID INT UNSIGNED NOT NULL,
    manifesto TEXT NULL,
    party_name VARCHAR(150) NULL,
    slogan VARCHAR(255) NULL,
    approved TINYINT(1) NOT NULL DEFAULT 0,
    approved_at DATETIME NULL,
    applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    total_votes INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (candidateID),
    UNIQUE KEY uq_candidates_user (userID),
    KEY idx_candidates_approval_votes (approved, total_votes),
    CONSTRAINT fk_candidates_user FOREIGN KEY (userID) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS elections (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('upcoming', 'active', 'closed') NOT NULL DEFAULT 'upcoming',
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_elections_status_dates (status, start_date, end_date),
    CONSTRAINT fk_elections_creator FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS votes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    candidateID INT UNSIGNED NOT NULL,
    voterID INT UNSIGNED NOT NULL,
    electionID INT UNSIGNED NOT NULL,
    voted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(512) NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_votes_one_per_voter (voterID),
    KEY idx_votes_candidate (candidateID),
    KEY idx_votes_election (electionID),
    CONSTRAINT fk_votes_candidate FOREIGN KEY (candidateID) REFERENCES candidates (candidateID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_votes_voter FOREIGN KEY (voterID) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_votes_election FOREIGN KEY (electionID) REFERENCES elections (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_activity_created (created_at),
    KEY idx_activity_user (user_id),
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_system_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO system_settings (setting_key, setting_value) VALUES
    ('site_name', 'Online Voting System'),
    ('contact_email', 'admin@votingsystem.com'),
    ('voting_enabled', '1')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- Create an administrator separately with a password hash produced by the application.
-- Example after connecting:
-- INSERT INTO users (name, email, password, role) VALUES
-- ('System Administrator', 'admin@example.com', MD5('CHANGE_THIS_PASSWORD'), 'admin');
