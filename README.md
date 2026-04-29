# Library Management System with Role-Based Authentication

A `PHP + MySQL + Tailwind CSS` web application for managing library books, borrowers, borrowing records, returns, penalties, and reports.

## Features

- Admin/librarian and borrower login
- Borrower self-registration
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

### 1. Create the database

Create a database named:

```sql
CREATE DATABASE library_management_system;
```

### 2. Import the SQL files

Import the files in this order:

1. `database/schema.sql`
2. `database/seed.sql`

You can use phpMyAdmin or the MySQL command line:

```bash
mysql -u root -p library_management_system < database/schema.sql
mysql -u root -p library_management_system < database/seed.sql
```

### 3. Update database settings

Open `includes/config.php` and check these values:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

`APP_URL` is detected automatically based on the folder location. In most setups, you do not need to change it manually.

If you ever want to hardcode a base URL for troubleshooting, replace:

```php
define('APP_URL', detect_app_url());
```

with a fixed value like:

```php
define('APP_URL', '/library-management-system');
```

### 4. Start a local PHP server

Inside the project folder, run:

```bash
php -S localhost:8000
```

Then open:

```text
http://localhost:8000/login.php
```

If you are using XAMPP, Laragon, Herd, or WAMP, place the folder in your web root and open the matching local URL.

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

## Common Troubleshooting

- If the database does not connect, recheck `includes/config.php`.
- If page links look broken, verify `APP_URL`.
- If the admin cannot log in, confirm that `database/seed.sql` was imported.
- If MySQL reports duplicate values, check whether the email, student ID, or ISBN already exists.
