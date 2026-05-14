# Library Management System with Role-Based Authentication

A `PHP + MySQL + Tailwind CSS` web application for managing books, borrowers, borrowing records, returns, penalties, and reports in a school library context.

This README is written as a **classroom/demo guide** for three audiences:

- **Presenter** (the person running the demo)
- **Students** (class participants)
- **Professor/Instructor** (the evaluator or reviewer)

---

## 1) Project Overview

The system models a practical library workflow:

- Borrowers (students) can register and upload a student ID.
- Admin/Librarian reviews registrations and approves or rejects accounts.
- Approved users can borrow/return books.
- The system tracks due dates, overdue items, and penalties.
- Reports are available for presentation and discussion.

This makes it suitable for:

- Database coursework
- Web application demonstrations
- Role-based authentication discussions
- End-to-end CRUD and workflow evaluation

---

## 2) Core Features

- Role-based login (`admin/librarian` and `borrower/student`)
- Borrower self-registration
- Student ID upload and admin review flow
- Optional email verification
- Secure password hashing via `password_hash()`
- Book catalog management with quantity tracking
- Borrow and return transaction flow
- Overdue detection with penalty calculation
- Print-friendly and on-screen reports

---

## 3) Tech Stack

- PHP 8+
- MySQL 8+
- HTML
- Tailwind CSS (CDN)
- Vanilla JavaScript (light interactions)

---

## 4) Project Structure

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

---

## 5) Setup Guide (Presenter First)

> **Recommended owner of setup:** Presenter
>
> Students can follow later on their own machines after the class demo.

### Step 1 — Install and Start XAMPP

1. Install XAMPP.
2. Start these modules in XAMPP Control Panel:
   - `Apache`
   - `MySQL`

Put the project in your web root (typical Windows path):

```text
C:\xampp\htdocs\library-management-system
```

### Step 2 — Create Database

Create database:

```sql
CREATE DATABASE library_management_system;
```

### Step 3 — Import SQL Files

Import **in this order**:

1. `database/schema.sql`
2. `database/seed.sql`

Using MySQL CLI:

```bash
mysql -u root -p library_management_system < database/schema.sql
mysql -u root -p library_management_system < database/seed.sql
```

### Step 4 — Configure Environment

Copy:

```text
.env.example -> .env
```

Set DB variables in `.env`:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Optional app URL values:

```env
APP_URL=/library-management-system
APP_PUBLIC_URL=http://localhost/library-management-system
```

### 5. Configure Gmail SMTP for a system email account

Use a dedicated Gmail account for system email delivery.
### Step 5 — Configure Demo Email (Optional but Useful for Presentation)

If showing email verification in class:

1. Use one presenter-owned Gmail account.
2. Enable 2-Step Verification.
3. Generate an App Password.
4. Put values in `.env`.

Example:

```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=library.notifications@gmail.com
MAIL_PASSWORD=your_16_character_app_password
MAIL_FROM_EMAIL=library.notifications@gmail.com
MAIL_FROM_NAME=Library Management System
MAIL_ENCRYPTION=tls
```

### Step 6 — Decide Verification Mode

For class demo with admin ID review flow, recommended:

```env
EMAIL_VERIFICATION_MODE=none
EMAIL_VERIFICATION_EMAILS=
EMAIL_VERIFICATION_EXPIRES_HOURS=24
```

Other modes:

- Require every borrower email verification:

```env
EMAIL_VERIFICATION_MODE=all
```

- Disable verification:

```env
EMAIL_VERIFICATION_MODE=none
```

### Step 7 — Run the App

Open:

```text
http://localhost/library-management-system/login.php
```

Alternative (PHP built-in server):

```bash
php -S localhost:8000
```

---

## 6) Default Seeded Accounts

After importing `seed.sql`:

- **Admin Email:** `admin@libraryms.test`
- **Admin Password:** `admin123`

> Change credentials before production/public deployment.

---

## 7) Presenter Guide (How to Run a Clean Demo)

Use this sequence for a smooth live presentation.

### A. Before Class (Checklist)

- Confirm Apache/MySQL running.
- Confirm DB imports succeeded.
- Confirm `.env` values are valid.
- Confirm seeded admin can log in.
- Pre-create at least 3–5 sample books.
- Prepare one sample student account for approval demo.

### B. Demo Flow (Suggested Script)

1. **Login as Admin**
   - Show role-based dashboard.
2. **Book Management**
   - Add a new book.
   - Edit quantity.
   - Show search/filter.
3. **Borrower Registration Review**
   - Show pending student registration with ID card upload.
   - Approve borrower.
4. **Borrow Transaction**
   - Borrow a book for approved borrower.
5. **Return Transaction**
   - Return book and explain status changes.
6. **Overdue + Penalty**
   - Explain auto-overdue and penalty concept.
7. **Reports**
   - Open borrowed/returned/overdue reports.

### C. Presenter Tips

- Speak in terms of *real school workflow* (librarian + students).
- Explain why role separation matters (security + responsibility).
- If time is short, prioritize: login → borrow flow → reports.

---

## 8) Student Guide (How to Understand and Practice)

### What Students Should Learn

- How authentication and roles are implemented
- How CRUD works in a full web app
- How relational DB tables support transactions
- How business rules (approval, overdue, penalties) are applied

### Suggested Practice Tasks

1. Register as a borrower and upload student ID.
2. Observe account state before admin approval.
3. After approval, login and browse available books.
4. Trace one borrow and one return flow.
5. Inspect DB records for consistency (books, borrow logs, returns).

### Mini Reflection Questions

- Why should borrowers not have admin-level permissions?
- What happens if stock quantity is not validated?
- Why store penalties instead of calculating everything only at display time?

---

## 9) Professor/Instructor Guide (Evaluation Lens)

### Suggested Rubric Dimensions

- **Functionality:** Required features run correctly end-to-end.
- **Data Integrity:** Quantities, statuses, and borrow/return records stay consistent.
- **Security Basics:** Password hashing, role checks, restricted routes.
- **Usability:** Clear workflow and understandable UI.
- **Maintainability:** Organized file structure, readable logic, predictable setup.

### Recommended Validation Scenarios

- Unauthorized user cannot access admin routes.
- Unapproved borrower cannot proceed as approved user.
- Borrow operation decreases available quantity correctly.
- Return operation restores quantity and updates status.
- Overdue records appear in reporting views.

### Questions to Ask During Defense

- Which business rules are enforced at server side?
- What assumptions are currently hardcoded?
- If scaled to multiple librarians, what should change first?

---

## 10) User Roles Summary

### Admin / Librarian

Can:

- Access admin dashboard
- Add/edit/delete/search books
- Review borrower registrations
- Add/edit borrower records
- Process borrowing and returns
- View penalties and reports

### Borrower / Student

Can:

- Register account
- Upload student ID for review
- Wait for approval
- Verify email (if enabled)
- Login/logout
- View available books
- View currently borrowed books
- Check due dates and overdue status
- Review returned book history

## Implementation Notes

- The code includes comments above non-trivial functions to explain what each helper does.
- Shared logic is stored in `includes/` so the page files stay easier to follow.
- Tailwind CSS is loaded from the CDN, so you do not need Node.js just to style the app.
- Small JavaScript is only used for basic UI behavior like the mobile menu and confirmation prompts.
- SMTP delivery is handled directly by the app, so there is no Composer dependency required for the deployment.
- Registration, admin borrower create, and admin borrower edit now use shared dropdown options for `course` and `year_level`.
- Uploaded student IDs are stored in `uploads/student-ids/`.

## Common Troubleshooting

- If the database does not connect, recheck `.env`.
- If page links look broken, verify `APP_URL`.
- If the admin cannot log in, confirm that `database/seed.sql` was imported.
- If MySQL reports duplicate values, check whether the email, student ID, or ISBN already exists.
- If Gmail does not send, confirm that you used a Google App Password instead of the normal Gmail password.
- If verification links fail, check that the `users` table includes the new verification columns.

---

## 11) Troubleshooting

### Problem: "Database connection failed"

Check:

- `.env` DB values
- MySQL service is running
- Database name exists

### Problem: "I cannot login with seeded admin"

Check:

- `seed.sql` was imported
- You are using exact seeded credentials
- No accidental edits to users table

### Problem: "Email verification links not working"

Check:

- `APP_PUBLIC_URL` is correct
- SMTP credentials are valid
- Gmail App Password is used (not normal account password)

---

## 12) Notes for Academic Use

- This project is suitable as a **teaching/demo system**, not production-ready software.
- For production, add stricter validation, audit logging, rate-limiting, and stronger operational security practices.
- Encourage students to treat this as a base for iterative improvement.

---

## 13) Quick Start (One-Minute Reminder)

1. Start Apache + MySQL.
2. Create `library_management_system` DB.
3. Import `schema.sql` then `seed.sql`.
4. Copy `.env.example` to `.env` and configure DB.
5. Open `http://localhost/library-management-system/login.php`.
6. Login as `admin@libraryms.test` / `admin123`.

