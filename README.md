# Library Management System with Role-Based Authentication

A `PHP + MySQL + Tailwind CSS` web application for managing library books, borrowers, borrowing records, returns, penalties, and reports.

## Features

- Admin/librarian and borrower login
- Borrower self-registration
- Admin verification for borrower registration using uploaded student IDs
- Optional email verification for borrower registration
- Password hashing using `password_hash()`
- Role-based dashboard redirect
- Book management with quantity and availability tracking
- Borrower management with student details
- Borrow and return workflows
- Automatic overdue marking with penalty calculation
- On-screen and print-friendly reports

## Tech Stack

- PHP 8+
- MySQL 8+
- HTML
- Tailwind CSS via CDN
- Vanilla JavaScript for small UI interactions

## Project Structure

```text
library-management-system/
|-- admin/
|-- assets/
|-- database/
|   |-- schema.sql
|   `-- seed.sql
|-- includes/
|   |-- auth.php
|   |-- bootstrap.php
|   |-- config.php
|   |-- db.php
|   |-- helpers.php
|   |-- layout.php
|   `-- library.php
|-- student/
|-- index.php
|-- login.php
|-- logout.php
|-- register.php
`-- README.md
```

## Setup Guide

### 1. Install and start XAMPP

Install XAMPP, then start these modules from the XAMPP Control Panel:

- `Apache`
- `MySQL`

Place the project folder inside your XAMPP web root, usually:

```text
C:\xampp\htdocs\library-management-system
```

### 2. Create the database

Create a database named:

```sql
CREATE DATABASE library_management_system;
```

### 3. Import the SQL files

Import the files in this order:

1. `database/schema.sql`
2. `database/seed.sql`

You can use phpMyAdmin or the MySQL command line:

```bash
mysql -u root -p library_management_system < database/schema.sql
mysql -u root -p library_management_system < database/seed.sql
```

If you already imported an older version of the schema, run these `ALTER TABLE` statements once:

```sql
ALTER TABLE users
    ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved' AFTER role,
    ADD COLUMN email_verified_at DATETIME DEFAULT NULL AFTER approval_status,
    ADD COLUMN verification_token_hash VARCHAR(255) DEFAULT NULL AFTER email_verified_at,
    ADD COLUMN verification_token_expires_at DATETIME DEFAULT NULL AFTER verification_token_hash;

ALTER TABLE borrower_profiles
    ADD COLUMN student_id_card_path VARCHAR(255) DEFAULT NULL AFTER contact_info;
```

### 4. Create your `.env` file

Copy `.env.example` to `.env`, then update the values for your machine.

Important database values:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

This project supports `.env` on a normal XAMPP setup. You do not need to hardcode database or SMTP values in `includes/config.php`.

`APP_URL` is still auto-detected by default. If your local setup needs it, you can add:

```env
APP_URL=/library-management-system
```

For email verification links, also set the full public/base URL used by your browser:

```env
APP_PUBLIC_URL=http://localhost/library-management-system
```

### 5. Configure Gmail SMTP for the presenter account

Use one Gmail account owned by the presenter for the live demo.

1. Turn on 2-Step Verification in that Google account.
2. Create an App Password in Google Account settings.
3. Put the Gmail address and App Password into `.env`.

Example:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=presenter@gmail.com
MAIL_PASSWORD=your_16_character_app_password
MAIL_FROM_EMAIL=presenter@gmail.com
MAIL_FROM_NAME=Library Management System
MAIL_ENCRYPTION=tls
```

### 6. Choose whether borrower emails also need verification

For the new admin ID-review flow, the recommended setup is:

```env
EMAIL_VERIFICATION_MODE=none
EMAIL_VERIFICATION_EMAILS=
EMAIL_VERIFICATION_EXPIRES_HOURS=24
```

Behavior:

- Borrowers register, upload a student ID, and wait for admin approval.
- Admin-created borrower accounts are approved automatically.
- Email verification is disabled unless you explicitly turn it on.

If you want every borrower to verify:

```env
EMAIL_VERIFICATION_MODE=all
```

If you want to disable verification temporarily:

```env
EMAIL_VERIFICATION_MODE=none
```

### 7. Open the project

In XAMPP, open:

```text
http://localhost/library-management-system/login.php
```

If you prefer PHP's built-in server instead of Apache, run:

```bash
php -S localhost:8000
```

## Default Admin Account

Use this seeded admin account after importing `seed.sql`:

- Email: `admin@libraryms.test`
- Password: `admin123`

## User Roles

### Admin / Librarian

Admins can:

- View the dashboard
- Add, edit, delete, and search books
- Add, view, and edit borrowers
- Borrow books for students
- Return books
- View penalties
- Open reports for borrowed, returned, overdue, and most borrowed books

### Borrower / Student

Borrowers can:

- Register an account
- Upload a student ID for admin review
- Wait for the librarian/admin to approve the borrower account
- Verify their email if their address is included in `EMAIL_VERIFICATION_EMAILS` or if `EMAIL_VERIFICATION_MODE=all`
- Log in and log out
- View available books
- View currently borrowed books
- Check due dates and overdue status
- Review returned book history

## Implementation Notes

- The code includes comments above non-trivial functions to explain what each helper does.
- Shared logic is stored in `includes/` so the page files stay easier to follow.
- Tailwind CSS is loaded from the CDN, so you do not need Node.js just to style the app.
- Small JavaScript is only used for basic UI behavior like the mobile menu and confirmation prompts.
- SMTP delivery is handled directly by the app, so there is no Composer dependency required for the classroom demo.
- Registration, admin borrower create, and admin borrower edit now use shared dropdown options for `course` and `year_level`.
- Uploaded student IDs are stored in `uploads/student-ids/`.

## Common Troubleshooting

- If the database does not connect, recheck `.env`.
- If page links look broken, verify `APP_URL`.
- If the admin cannot log in, confirm that `database/seed.sql` was imported.
- If MySQL reports duplicate values, check whether the email, student ID, or ISBN already exists.
- If Gmail does not send, confirm that you used a Google App Password instead of the normal Gmail password.
- If verification links fail, check that the `users` table includes the new verification columns.
