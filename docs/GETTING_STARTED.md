# Getting Started

## 1) Requirements

- XAMPP (Apache + MySQL)
- PHP 8+
- MySQL 8+
- Modern browser (Chrome/Edge/Firefox)

## 2) Project Placement

Copy the project into your XAMPP web root:

```text
C:\xampp\htdocs\library-management-system
```

## 3) Database Setup

Create the database:

```sql
CREATE DATABASE library_management_system;
```

Import SQL files in order:

1. `database/schema.sql`
2. `database/seed.sql`

## 4) Environment Configuration

Create a `.env` file based on `.env.example` and configure:

- `DB_HOST`
- `DB_PORT`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Optional:

- `APP_URL=/library-management-system`
- `APP_PUBLIC_URL=http://localhost/library-management-system`

## 5) Run the Application

Start Apache and MySQL, then open:

```text
http://localhost/library-management-system/login.php
```

## 6) Default Admin Account

- Email: `admin@libraryms.test`
- Password: `admin123`

## 7) Quick Health Check

- Can you load `login.php`?
- Can you log in with admin account?
- Are books and borrowers visible in admin modules?
