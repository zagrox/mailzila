-- Insert admin user with password: Admin@123
INSERT INTO users (email, password, first_name, last_name, is_active) 
VALUES (
    'admin@mailzila.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'Admin',
    'User',
    1
); 