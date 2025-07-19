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
