INSERT INTO users (name, email, password, role, created_at)
VALUES (
    'Main Librarian',
    'admin@libraryms.test',
    '$2y$12$cloJ8prsPoEgmiYcCHXRheQgG9ptqB.VqQTiz9QHMi0mpESpi4gcS',
    'admin',
    NOW()
);

INSERT INTO books (title, author, isbn, category, quantity, available_quantity, created_at)
VALUES
    ('Introduction to Algorithms', 'Thomas H. Cormen', '9780262046305', 'Computer Science', 5, 5, NOW()),
    ('Clean Code', 'Robert C. Martin', '9780132350884', 'Programming', 4, 4, NOW()),
    ('Database System Concepts', 'Abraham Silberschatz', '9780078022159', 'Database', 3, 3, NOW()),
    ('Operating System Concepts', 'Abraham Silberschatz', '9781119456339', 'Systems', 2, 2, NOW());

