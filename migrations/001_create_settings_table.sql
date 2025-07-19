
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    value TEXT NOT NULL
);

INSERT INTO settings (name, value) VALUES ('ad_banner_enabled', '1');
INSERT INTO settings (name, value) VALUES ('event_banner_enabled', '1');
INSERT INTO settings (name, value) VALUES ('ad_banner_content', 'This is an ad banner.');
INSERT INTO settings (name, value) VALUES ('event_banner_content', 'This is an event banner.');
