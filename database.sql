
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    value TEXT NOT NULL
);

INSERT INTO settings (name, value) VALUES ('ad_banner_enabled', '1');
INSERT INTO settings (name, value) VALUES ('event_banner_enabled', '1');
INSERT INTO settings (name, value) VALUES ('ad_banner_content', 'This is an ad banner.');
INSERT INTO settings (name, value) VALUES ('event_banner_content', 'This is an event banner.');


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'member') NOT NULL DEFAULT 'member'
);

INSERT INTO users (username, password, role) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO pages (title, content, slug) VALUES
('Home', 'Welcome to the home page.', 'home'),
('CV', 'This is my CV.', 'cv'),
('Services', 'These are my services.', 'services'),
('Portfolio', 'This is my portfolio.', 'portfolio'),
('Contact', 'Contact me here.', 'contact');
CREATE TABLE blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO blocks (title, content) VALUES ('Welcome', 'Welcome to our website!');
CREATE TABLE polls (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE poll_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poll_id INT NOT NULL,
    option_text VARCHAR(255) NOT NULL,
    votes INT NOT NULL DEFAULT 0,
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
);
ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL;
