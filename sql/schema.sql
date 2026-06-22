CREATE TABLE IF NOT EXISTS leads (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  first_name VARCHAR(120) NULL,
  last_name VARCHAR(120) NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(60) NOT NULL,
  whatsapp VARCHAR(60) NULL,
  gender VARCHAR(40) NULL,
  age_range VARCHAR(40) NULL,
  state_of_residence VARCHAR(120) NULL,
  highest_qualification VARCHAR(180) NULL,
  current_occupation VARCHAR(180) NULL,
  employment_status VARCHAR(80) NULL,
  preferred_course VARCHAR(120) NULL,
  prior_tech_experience VARCHAR(20) NULL,
  career_goals TEXT NULL,
  message TEXT NULL,
  source VARCHAR(500) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(1000) NULL,
  status ENUM('new','contacted','qualified','converted','rejected') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_leads_email (email),
  INDEX idx_leads_phone (phone),
  INDEX idx_leads_status (status),
  INDEX idx_leads_created_at (created_at),
  INDEX idx_leads_ip_created (ip_address, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create an admin user after import by replacing the hash below with your own password_hash output.
-- Example generator: php -r "echo password_hash('ChangeThisPassword', PASSWORD_DEFAULT), PHP_EOL;"
-- INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$2y$10$replace_this_with_a_real_hash');
