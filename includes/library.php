<?php

declare(strict_types=1);

/**
 * Finds a user record by email address.
 */
function find_user_by_email(string $email): ?array
{
    $statement = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    return $user ?: null;
}

/**
 * Finds a user record by primary key.
 */
function find_user_by_id(int $userId): ?array
{
    $statement = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    return $user ?: null;
}

/**
 * Ensures existing installations have the user status column needed by super admin management.
 */
function ensure_user_account_status_column(): void
{
    static $checked = false;

    if ($checked) {
        return;
    }

    $checked = true;

    try {
        $statement = db()->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name'
        );
        $statement->execute([
            'table_name' => 'users',
            'column_name' => 'account_status',
        ]);

        if ((int) $statement->fetchColumn() === 0) {
            db()->exec(
                "ALTER TABLE users
                 ADD account_status ENUM('active', 'inactive') NOT NULL DEFAULT 'active'
                 AFTER approval_status"
            );
        }
    } catch (Throwable $exception) {
        // Keep pages usable in restricted environments; new installs get this column from schema.sql.
    }
}

/**
 * Returns a searchable list of all users for super admin management.
 *
 * @return list<array<string,mixed>>
 */
function get_users(string $search = ''): array
{
    ensure_user_account_status_column();

    $sql = 'SELECT id, name, email, role, approval_status, COALESCE(account_status, "active") AS account_status, created_at
            FROM users';
    $params = [];

    if ($search !== '') {
        $sql .= ' WHERE name LIKE :search OR email LIKE :search OR role LIKE :search';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY role = "super_admin" DESC, name ASC';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Updates the core profile fields for a user.
 *
 * @return array{success:bool,message:string}
 */
function update_user_profile(int $userId, array $data): array
{
    ensure_user_account_status_column();

    try {
        $statement = db()->prepare(
            'UPDATE users
             SET name = :name, email = :email, role = :role, account_status = :account_status
             WHERE id = :id'
        );
        $statement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'account_status' => $data['account_status'],
            'id' => $userId,
        ]);

        return [
            'success' => true,
            'message' => 'User profile updated successfully.',
        ];
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'message' => 'Unable to update user profile. Please check for duplicate email addresses.',
        ];
    }
}

/**
 * Changes whether a user account can log in.
 *
 * @return array{success:bool,message:string}
 */
function update_user_account_status(int $userId, string $status): array
{
    ensure_user_account_status_column();

    try {
        $statement = db()->prepare('UPDATE users SET account_status = :account_status WHERE id = :id');
        $statement->execute([
            'account_status' => $status,
            'id' => $userId,
        ]);

        return [
            'success' => true,
            'message' => $status === 'active' ? 'User account reactivated.' : 'User account deactivated.',
        ];
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'message' => 'Unable to update user account status.',
        ];
    }
}

/**
 * Generates a readable temporary password for password reset actions.
 */
function generate_temporary_password(): string
{
    return 'Lib-' . bin2hex(random_bytes(4)) . '-' . random_int(100, 999);
}

/**
 * Sets a new password for a user.
 *
 * @return array{success:bool,message:string}
 */
function set_user_password(int $userId, string $password): array
{
    try {
        $statement = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
        $statement->execute([
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'id' => $userId,
        ]);

        return [
            'success' => true,
            'message' => 'Password updated successfully.',
        ];
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'message' => 'Unable to update the password.',
        ];
    }
}

/**
 * Sends a temporary password email to the selected user.
 *
 * @return array{success:bool,message:string}
 */
function send_password_reset_email(array $user, string $temporaryPassword): array
{
    $subject = 'Your Library Management System password reset';
    $body = sprintf(
        '<p>Hello %s,</p>
        <p>A super admin reset your Library Management System password.</p>
        <p>Your temporary password is: <strong>%s</strong></p>
        <p>Please log in and ask the super admin to update it again if you did not request this change.</p>',
        e($user['name'] ?? 'User'),
        e($temporaryPassword)
    );

    return send_smtp_mail(
        (string) ($user['email'] ?? ''),
        (string) ($user['name'] ?? ''),
        $subject,
        $body
    );
}

/**
 * Creates a borrower account together with its profile.
 *
 * @return array{success:bool,message:string,user_id:int|null}
 */
function create_borrower_account(array $data): array
{
    $pdo = db();
    $markVerified = (bool) ($data['mark_verified'] ?? false);
    $approvalStatus = (string) ($data['approval_status'] ?? 'approved');

    try {
        $pdo->beginTransaction();

        $userStatement = $pdo->prepare(
            'INSERT INTO users (name, email, password, role, approval_status, email_verified_at, created_at)
             VALUES (:name, :email, :password, :role, :approval_status, :email_verified_at, NOW())'
        );

        $userStatement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'borrower',
            'approval_status' => $approvalStatus,
            'email_verified_at' => $markVerified ? date('Y-m-d H:i:s') : null,
        ]);

        $userId = (int) $pdo->lastInsertId();

        $profileStatement = $pdo->prepare(
            'INSERT INTO borrower_profiles (user_id, student_id, course, year_level, contact_info, student_id_card_path, created_at)
             VALUES (:user_id, :student_id, :course, :year_level, :contact_info, :student_id_card_path, NOW())'
        );

        $profileStatement->execute([
            'user_id' => $userId,
            'student_id' => $data['student_id'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'contact_info' => $data['contact_info'],
            'student_id_card_path' => $data['student_id_card_path'] ?? null,
        ]);

        $studentStatement = $pdo->prepare(
            'INSERT INTO students (user_id, student_id, course, year_level, contact_info, status, created_at)
             VALUES (:user_id, :student_id, :course, :year_level, :contact_info, :status, NOW())'
        );

        $studentStatement->execute([
            'user_id' => $userId,
            'student_id' => $data['student_id'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'contact_info' => $data['contact_info'],
            'status' => 'active',
        ]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Borrower account created successfully.',
            'user_id' => $userId,
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Unable to create the borrower account. Please check for duplicate email or student ID.',
            'user_id' => null,
        ];
    }
}

/**
 * Returns profile information for a borrower.
 */
function get_borrower_profile(int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT u.id AS user_id, u.name, u.email, u.role, u.approval_status, bp.student_id, bp.course, bp.year_level, bp.contact_info, bp.student_id_card_path
         FROM users u
         INNER JOIN borrower_profiles bp ON bp.user_id = u.id
         WHERE u.id = :user_id
         LIMIT 1'
    );
    $statement->execute(['user_id' => $userId]);
    $profile = $statement->fetch();

    return $profile ?: null;
}

/**
 * Returns a searchable list of borrowers.
 *
 * @return list<array<string,mixed>>
 */
function get_borrowers(string $search = ''): array
{
    $sql = 'SELECT u.id AS user_id, u.name, u.email, u.approval_status, bp.student_id, bp.course, bp.year_level, bp.contact_info, bp.student_id_card_path
            FROM users u
            INNER JOIN borrower_profiles bp ON bp.user_id = u.id
            WHERE u.role = :role';

    $params = ['role' => 'borrower'];

    if ($search !== '') {
        $sql .= ' AND (
            u.name LIKE :search
            OR u.email LIKE :search
            OR bp.student_id LIKE :search
            OR bp.course LIKE :search
        )';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY u.name ASC';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Updates a borrower account and profile.
 *
 * @return array{success:bool,message:string}
 */
function update_borrower(int $userId, array $data): array
{
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $sql = 'UPDATE users SET name = :name, email = :email, approval_status = :approval_status WHERE id = :id';
        $params = [
            'name' => $data['name'],
            'email' => $data['email'],
            'approval_status' => $data['approval_status'] ?? 'approved',
            'id' => $userId,
        ];

        if (!empty($data['password'])) {
            $sql = 'UPDATE users SET name = :name, email = :email, approval_status = :approval_status, password = :password WHERE id = :id';
            $params['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $userStatement = $pdo->prepare($sql);
        $userStatement->execute($params);

        $profileStatement = $pdo->prepare(
            'UPDATE borrower_profiles
             SET student_id = :student_id, course = :course, year_level = :year_level, contact_info = :contact_info, student_id_card_path = :student_id_card_path
             WHERE user_id = :user_id'
        );
        $profileStatement->execute([
            'student_id' => $data['student_id'],
            'course' => $data['course'],
            'year_level' => $data['year_level'],
            'contact_info' => $data['contact_info'],
            'student_id_card_path' => $data['student_id_card_path'] ?? null,
            'user_id' => $userId,
        ]);

        $pdo->commit();

        return [
            'success' => true,
            'message' => 'Borrower updated successfully.',
        ];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return [
            'success' => false,
            'message' => 'Unable to update borrower details. Please check for duplicate email or student ID.',
        ];
    }
}

/**
 * Stores an uploaded student ID image inside the public uploads folder.
 *
 * @param array<string,mixed> $file
 * @return array{success:bool,message:string,path:string|null}
 */
function store_student_id_upload(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'message' => 'Please upload a student ID image.',
            'path' => null,
        ];
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return [
            'success' => false,
            'message' => 'The uploaded student ID file is invalid.',
            'path' => null,
        ];
    }

    if ($size > 5 * 1024 * 1024) {
        return [
            'success' => false,
            'message' => 'Student ID uploads must be 5 MB or smaller.',
            'path' => null,
        ];
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        return [
            'success' => false,
            'message' => 'Student ID uploads must be JPG, PNG, or WEBP images.',
            'path' => null,
        ];
    }

    $uploadDirectory = dirname(__DIR__) . '/uploads/student-ids';

    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0777, true) && !is_dir($uploadDirectory)) {
        return [
            'success' => false,
            'message' => 'Unable to prepare the student ID upload folder.',
            'path' => null,
        ];
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $uploadDirectory . '/' . $fileName;

    if (!move_uploaded_file($tmpName, $destination)) {
        return [
            'success' => false,
            'message' => 'Unable to save the uploaded student ID image.',
            'path' => null,
        ];
    }

    return [
        'success' => true,
        'message' => 'Student ID uploaded successfully.',
        'path' => 'uploads/student-ids/' . $fileName,
    ];
}

/**
 * Returns a searchable list of books.
 *
 * @return list<array<string,mixed>>
 */
function get_books(string $search = '', bool $availableOnly = false): array
{
    $sql = 'SELECT * FROM books WHERE 1 = 1';
    $params = [];

    if ($search !== '') {
        $sql .= ' AND (
            title LIKE :search
            OR author LIKE :search
            OR isbn LIKE :search
            OR category LIKE :search
        )';
        $params['search'] = '%' . $search . '%';
    }

    if ($availableOnly) {
        $sql .= ' AND available_quantity > 0';
    }

    $sql .= ' ORDER BY title ASC';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Returns one book by its ID.
 */
function get_book(int $bookId): ?array
{
    $statement = db()->prepare('SELECT * FROM books WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $bookId]);
    $book = $statement->fetch();

    return $book ?: null;
}

/**
 * Inserts a new book record.
 *
 * @return array{success:bool,message:string}
 */
function create_book(array $data): array
{
    try {
        $statement = db()->prepare(
            'INSERT INTO books (title, author, isbn, category, quantity, available_quantity, created_at)
             VALUES (:title, :author, :isbn, :category, :quantity, :available_quantity, NOW())'
        );
        $statement->execute([
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'category' => $data['category'],
            'quantity' => $data['quantity'],
            'available_quantity' => $data['quantity'],
        ]);

        return [
            'success' => true,
            'message' => 'Book added successfully.',
        ];
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'message' => 'Unable to add the book. Please check if the ISBN is already used.',
        ];
    }
}

/**
 * Updates book information while keeping stock quantities valid.
 *
 * @return array{success:bool,message:string}
 */
function update_book(int $bookId, array $data): array
{
    $book = get_book($bookId);

    if ($book === null) {
        return [
            'success' => false,
            'message' => 'Book not found.',
        ];
    }

    $borrowedCopies = (int) $book['quantity'] - (int) $book['available_quantity'];
    $newQuantity = (int) $data['quantity'];

    if ($newQuantity < $borrowedCopies) {
        return [
            'success' => false,
            'message' => 'Quantity cannot be lower than the number of borrowed copies.',
        ];
    }

    $newAvailable = $newQuantity - $borrowedCopies;

    try {
        $statement = db()->prepare(
            'UPDATE books
             SET title = :title, author = :author, isbn = :isbn, category = :category, quantity = :quantity, available_quantity = :available_quantity
             WHERE id = :id'
        );
        $statement->execute([
            'title' => $data['title'],
            'author' => $data['author'],
            'isbn' => $data['isbn'],
            'category' => $data['category'],
            'quantity' => $newQuantity,
            'available_quantity' => $newAvailable,
            'id' => $bookId,
        ]);

        return [
            'success' => true,
            'message' => 'Book updated successfully.',
        ];
    } catch (Throwable $exception) {
        return [
            'success' => false,
            'message' => 'Unable to update the book. Please check if the ISBN is already used.',
        ];
    }
}

/**
 * Deletes a book if it does not have active borrow records.
 *
 * @return array{success:bool,message:string}
 */
function delete_book(int $bookId): array
{
    $statement = db()->prepare(
        "SELECT COUNT(*) FROM borrow_records WHERE book_id = :book_id AND status IN ('borrowed', 'overdue')"
    );
    $statement->execute(['book_id' => $bookId]);

    if ((int) $statement->fetchColumn() > 0) {
        return [
            'success' => false,
            'message' => 'Cannot delete a book that is currently borrowed.',
        ];
    }

    $deleteStatement = db()->prepare('DELETE FROM books WHERE id = :id');
    $deleteStatement->execute(['id' => $bookId]);

    return [
        'success' => true,
        'message' => 'Book deleted successfully.',
    ];
}

/**
 * Computes the penalty for a record based on its due date and return date.
 */
function calculate_penalty(string $dueDate, ?string $returnDate = null): float
{
    $due = new DateTimeImmutable($dueDate);
    $compareDate = $returnDate ? new DateTimeImmutable($returnDate) : new DateTimeImmutable('today');

    if ($compareDate <= $due) {
        return 0.0;
    }

    $daysLate = (int) $due->diff($compareDate)->format('%a');

    return $daysLate * PENALTY_PER_DAY;
}

/**
 * Updates overdue records whenever a logged-in page is opened.
 *
 * This helper both changes newly overdue records and refreshes
 * penalties for records that are already overdue.
 */
function refresh_overdue_records(): void
{
    $records = db()->query(
        "SELECT id, due_date, status
         FROM borrow_records
         WHERE (status = 'borrowed' AND due_date < CURDATE())
            OR status = 'overdue'"
    )->fetchAll();

    if ($records === []) {
        return;
    }

    $statement = db()->prepare(
        "UPDATE borrow_records SET status = :status, penalty = :penalty WHERE id = :id"
    );

    foreach ($records as $record) {
        $statement->execute([
            'status' => 'overdue',
            'penalty' => calculate_penalty($record['due_date']),
            'id' => $record['id'],
        ]);
    }
}

/**
 * Returns summary statistics for the admin dashboard.
 *
 * @return array<string,int>
 */
function get_admin_dashboard_stats(): array
{
    return [
        'total_books' => (int) db()->query('SELECT COALESCE(SUM(quantity), 0) FROM books')->fetchColumn(),
        'available_books' => (int) db()->query('SELECT COALESCE(SUM(available_quantity), 0) FROM books')->fetchColumn(),
        'borrowed_books' => (int) db()->query("SELECT COUNT(*) FROM borrow_records WHERE status IN ('borrowed', 'overdue')")->fetchColumn(),
        'overdue_books' => (int) db()->query("SELECT COUNT(*) FROM borrow_records WHERE status = 'overdue'")->fetchColumn(),
        'pending_borrowers' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'borrower' AND approval_status = 'pending'")->fetchColumn(),
    ];
}

/**
 * Returns summary statistics for the borrower dashboard.
 *
 * @return array<string,int>
 */
function get_borrower_dashboard_stats(int $userId): array
{
    $statement = db()->prepare(
        "SELECT
            SUM(CASE WHEN status IN ('borrowed', 'overdue') THEN 1 ELSE 0 END) AS active_loans,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) AS overdue_loans,
            SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) AS returned_books
         FROM borrow_records
         WHERE user_id = :user_id"
    );
    $statement->execute(['user_id' => $userId]);
    $stats = $statement->fetch() ?: [];

    return [
        'available_books' => (int) db()->query('SELECT COALESCE(SUM(available_quantity), 0) FROM books')->fetchColumn(),
        'active_loans' => (int) ($stats['active_loans'] ?? 0),
        'overdue_loans' => (int) ($stats['overdue_loans'] ?? 0),
        'returned_books' => (int) ($stats['returned_books'] ?? 0),
    ];
}

/**
 * Returns recent borrowing activity for the admin dashboard.
 *
 * @return list<array<string,mixed>>
 */
function get_recent_activity(int $limit = 8): array
{
    $statement = db()->prepare(
        "SELECT br.id, u.name, b.title, br.borrow_date, br.due_date, br.return_date, br.status, br.penalty
         FROM borrow_records br
         INNER JOIN users u ON u.id = br.user_id
         INNER JOIN books b ON b.id = br.book_id
         ORDER BY br.created_at DESC
         LIMIT :limit"
    );
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Returns borrowers that can be selected in the borrow form.
 *
 * @return list<array<string,mixed>>
 */
function get_borrower_options(): array
{
    return db()->query(
        'SELECT u.id, u.name, bp.student_id
         FROM users u
         INNER JOIN borrower_profiles bp ON bp.user_id = u.id
         WHERE u.role = "borrower" AND u.approval_status = "approved"
         ORDER BY u.name ASC'
    )->fetchAll();
}

/**
 * Returns active borrow records, optionally filtered by a search term.
 *
 * @return list<array<string,mixed>>
 */
function get_active_borrow_records(string $search = ''): array
{
    $sql = "SELECT br.id, u.name, bp.student_id, b.title, b.author, br.borrow_date, br.due_date, br.status, br.penalty
            FROM borrow_records br
            INNER JOIN users u ON u.id = br.user_id
            INNER JOIN borrower_profiles bp ON bp.user_id = u.id
            INNER JOIN books b ON b.id = br.book_id
            WHERE br.status IN ('borrowed', 'overdue')";

    $params = [];

    if ($search !== '') {
        $sql .= ' AND (u.name LIKE :search OR bp.student_id LIKE :search OR b.title LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY br.due_date ASC';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Creates a borrow transaction and updates the book stock.
 *
 * @return array{success:bool,message:string}
 */
function borrow_book(int $userId, int $bookId, string $dueDate): array
{
    $book = get_book($bookId);

    if ($book === null) {
        return ['success' => false, 'message' => 'Selected book was not found.'];
    }

    if ((int) $book['available_quantity'] <= 0) {
        return ['success' => false, 'message' => 'This book is currently unavailable.'];
    }

    if ($dueDate < date('Y-m-d')) {
        return ['success' => false, 'message' => 'Due date cannot be earlier than today.'];
    }

    $existingStatement = db()->prepare(
        "SELECT COUNT(*) FROM borrow_records WHERE user_id = :user_id AND book_id = :book_id AND status IN ('borrowed', 'overdue')"
    );
    $existingStatement->execute([
        'user_id' => $userId,
        'book_id' => $bookId,
    ]);

    if ((int) $existingStatement->fetchColumn() > 0) {
        return ['success' => false, 'message' => 'This borrower already has an active copy of the selected book.'];
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();

        $recordStatement = $pdo->prepare(
            "INSERT INTO borrow_records (user_id, book_id, borrow_date, due_date, status, penalty, created_at)
             VALUES (:user_id, :book_id, CURDATE(), :due_date, 'borrowed', 0, NOW())"
        );
        $recordStatement->execute([
            'user_id' => $userId,
            'book_id' => $bookId,
            'due_date' => $dueDate,
        ]);

        $bookStatement = $pdo->prepare(
            'UPDATE books SET available_quantity = available_quantity - 1 WHERE id = :id'
        );
        $bookStatement->execute(['id' => $bookId]);

        $pdo->commit();

        return ['success' => true, 'message' => 'Book borrowed successfully.'];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['success' => false, 'message' => 'Unable to save the borrow transaction.'];
    }
}

/**
 * Marks a borrow record as returned and restores book stock.
 *
 * @return array{success:bool,message:string}
 */
function return_book(int $recordId): array
{
    $statement = db()->prepare(
        'SELECT br.*, b.id AS book_id
         FROM borrow_records br
         INNER JOIN books b ON b.id = br.book_id
         WHERE br.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $recordId]);
    $record = $statement->fetch();

    if ($record === false) {
        return ['success' => false, 'message' => 'Borrow record not found.'];
    }

    if ($record['status'] === 'returned') {
        return ['success' => false, 'message' => 'This record has already been returned.'];
    }

    $returnDate = date('Y-m-d');
    $penalty = calculate_penalty($record['due_date'], $returnDate);
    $pdo = db();

    try {
        $pdo->beginTransaction();

        $updateRecord = $pdo->prepare(
            "UPDATE borrow_records
             SET return_date = :return_date, status = 'returned', penalty = :penalty
             WHERE id = :id"
        );
        $updateRecord->execute([
            'return_date' => $returnDate,
            'penalty' => $penalty,
            'id' => $recordId,
        ]);

        $updateBook = $pdo->prepare(
            'UPDATE books SET available_quantity = available_quantity + 1 WHERE id = :book_id'
        );
        $updateBook->execute(['book_id' => $record['book_id']]);

        $pdo->commit();

        return ['success' => true, 'message' => 'Book returned successfully.'];
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        return ['success' => false, 'message' => 'Unable to complete the return process.'];
    }
}

/**
 * Returns current and overdue records for a borrower.
 *
 * @return list<array<string,mixed>>
 */
function get_borrower_current_records(int $userId): array
{
    $statement = db()->prepare(
        "SELECT br.id, b.title, b.author, b.category, br.borrow_date, br.due_date, br.status, br.penalty
         FROM borrow_records br
         INNER JOIN books b ON b.id = br.book_id
         WHERE br.user_id = :user_id AND br.status IN ('borrowed', 'overdue')
         ORDER BY br.due_date ASC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Returns returned history for a borrower.
 *
 * @return list<array<string,mixed>>
 */
function get_borrower_history(int $userId): array
{
    $statement = db()->prepare(
        "SELECT br.id, b.title, b.author, br.borrow_date, br.due_date, br.return_date, br.penalty
         FROM borrow_records br
         INNER JOIN books b ON b.id = br.book_id
         WHERE br.user_id = :user_id AND br.status = 'returned'
         ORDER BY br.return_date DESC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Returns a report based on the selected report type.
 *
 * @param string $dateField Borrow, return, or due date column used for filtering.
 *
 * @return list<array<string,mixed>>
 */
function get_report_records(string $status, ?string $dateFrom = null, ?string $dateTo = null, string $dateField = 'borrow_date'): array
{
    $allowedDateFields = ['borrow_date', 'return_date', 'due_date'];

    if (!in_array($dateField, $allowedDateFields, true)) {
        $dateField = 'borrow_date';
    }

    $sql = "SELECT br.id, u.name, bp.student_id, b.title, b.author, br.borrow_date, br.due_date, br.return_date, br.status, br.penalty
            FROM borrow_records br
            INNER JOIN users u ON u.id = br.user_id
            INNER JOIN borrower_profiles bp ON bp.user_id = u.id
            INNER JOIN books b ON b.id = br.book_id
            WHERE br.status = :status";

    $params = ['status' => $status];

    if ($dateFrom !== null && $dateFrom !== '') {
        $sql .= " AND br.{$dateField} >= :date_from";
        $params['date_from'] = $dateFrom;
    }

    if ($dateTo !== null && $dateTo !== '') {
        $sql .= " AND br.{$dateField} <= :date_to";
        $params['date_to'] = $dateTo;
    }

    $sql .= " ORDER BY br.{$dateField} DESC";

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Returns the books with the highest number of borrow transactions.
 *
 * @return list<array<string,mixed>>
 */
function get_most_borrowed_books(): array
{
    return db()->query(
        'SELECT b.title, b.author, b.category, COUNT(br.id) AS borrow_count
         FROM books b
         LEFT JOIN borrow_records br ON br.book_id = b.id
         GROUP BY b.id, b.title, b.author, b.category
         ORDER BY borrow_count DESC, b.title ASC'
    )->fetchAll();
}


/**
 * Returns all cart items for a borrower.
 *
 * @return list<array<string,mixed>>
 */
function get_reservation_cart_items(int $userId): array
{
    $statement = db()->prepare(
        "SELECT rci.id, rci.book_id, rci.status, rci.created_at, b.title, b.author, b.isbn, b.category, b.available_quantity
         FROM reservation_cart_items rci
         INNER JOIN books b ON b.id = rci.book_id
         WHERE rci.user_id = :user_id AND rci.status = 'in_cart'
         ORDER BY rci.created_at DESC"
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

/**
 * Adds a book to a borrower's reservation cart.
 *
 * @return array{success:bool,message:string}
 */
function add_book_to_cart(int $userId, int $bookId): array
{
    $book = get_book($bookId);

    if ($book === null || (int) $book['available_quantity'] <= 0) {
        return ['success' => false, 'message' => 'Book is unavailable for reservation.'];
    }

    try {
        $statement = db()->prepare(
            "INSERT INTO reservation_cart_items (user_id, book_id, status, created_at)
             VALUES (:user_id, :book_id, 'in_cart', NOW())"
        );
        $statement->execute(['user_id' => $userId, 'book_id' => $bookId]);

        return ['success' => true, 'message' => 'Book added to reservation cart.'];
    } catch (Throwable $exception) {
        return ['success' => false, 'message' => 'This book is already in your reservation cart.'];
    }
}

/**
 * Removes an item from a borrower's reservation cart.
 */
function remove_book_from_cart(int $userId, int $cartItemId): void
{
    $statement = db()->prepare('DELETE FROM reservation_cart_items WHERE id = :id AND user_id = :user_id AND status = "in_cart"');
    $statement->execute(['id' => $cartItemId, 'user_id' => $userId]);
}

/**
 * Returns pending cart reservations for admin processing.
 *
 * @return list<array<string,mixed>>
 */
function get_pending_reservations(string $search = ''): array
{
    $sql = "SELECT rci.id, rci.user_id, rci.book_id, rci.created_at, u.name, u.email, bp.student_id, b.title, b.author, b.available_quantity
            FROM reservation_cart_items rci
            INNER JOIN users u ON u.id = rci.user_id
            INNER JOIN borrower_profiles bp ON bp.user_id = u.id
            INNER JOIN books b ON b.id = rci.book_id
            WHERE rci.status = 'in_cart'";
    $params = [];

    if ($search !== '') {
        $sql .= ' AND (u.name LIKE :search OR bp.student_id LIKE :search OR b.title LIKE :search)';
        $params['search'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY rci.created_at ASC';

    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Approves one reservation by creating a borrow record and marking it reserved.
 *
 * @return array{success:bool,message:string}
 */
function approve_reservation(int $reservationId, string $dueDate): array
{
    $pdo = db();
    $reservationStmt = $pdo->prepare(
        "SELECT rci.id, rci.user_id, rci.book_id, rci.status, b.available_quantity
         FROM reservation_cart_items rci
         INNER JOIN books b ON b.id = rci.book_id
         WHERE rci.id = :id
         LIMIT 1"
    );
    $reservationStmt->execute(['id' => $reservationId]);
    $reservation = $reservationStmt->fetch();

    if (!$reservation || $reservation['status'] !== 'in_cart') {
        return ['success' => false, 'message' => 'Reservation no longer available.'];
    }

    $result = borrow_book((int) $reservation['user_id'], (int) $reservation['book_id'], $dueDate);
    if (!$result['success']) {
        return $result;
    }

    $updateStmt = $pdo->prepare("UPDATE reservation_cart_items SET status = 'reserved', reserved_at = NOW() WHERE id = :id");
    $updateStmt->execute(['id' => $reservationId]);

    return ['success' => true, 'message' => 'Reservation approved and converted into a borrow record.'];
}
