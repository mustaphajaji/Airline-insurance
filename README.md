# Airline Insurance — XAMPP Installation Guide

## Prerequisites
- XAMPP (PHP 7.4+ / PHP 8.x, MySQL 5.7+, Apache)
- A modern web browser

---

## Installation Steps

### Step 1 — Copy the project
Place the entire `airline-insurance` folder into your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\airline-insurance\   (Windows)
/Applications/XAMPP/htdocs/airline-insurance/   (macOS)
/opt/lampp/htdocs/airline-insurance/   (Linux)
```

### Step 2 — Start XAMPP services
Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### Step 3 — Import the database
1. Open your browser and go to **http://localhost/phpmyadmin**
2. Click **New** and create a database named: `airline_insurance`
3. Select the `airline_insurance` database
4. Click the **Import** tab
5. Choose the file: `airline-insurance/database/schema.sql`
6. Click **Go** to import

### Step 4 — Run the setup script
Open your browser and visit:

```
http://localhost/airline-insurance/setup.php
```

Click **Run Setup Now**. This will insert the demo accounts with properly hashed passwords.

### Step 5 — Access the application
- **Landing Page:** http://localhost/airline-insurance/
- **Customer Login:** http://localhost/airline-insurance/auth/login.php
- **Admin Panel:** http://localhost/airline-insurance/admin/dashboard.php

---

## Demo Credentials

### Administrator
| Field    | Value                          |
|----------|-------------------------------|
| Email    | admin@airlineinsurance.com    |
| Password | Admin@12345                   |

### Customer
| Field    | Value                              |
|----------|------------------------------------|
| Email    | customer@airlineinsurance.com      |
| Password | Customer@123                       |

---

## Feature Overview

### Customer Area
- Register / Log In with brute-force lockout protection
- Browse 6 insurance plan types
- Submit policy applications with flight details
- Pay premiums (card / bank transfer — demo mode)
- File claims with document uploads (PDF/JPG/PNG)
- Track policy and claim statuses in real time
- Receive in-app notifications for every status change
- Mobile-responsive with hamburger navigation
- Manage profile and change password

### Admin Panel
- Dashboard with revenue and activity statistics
- Review and approve / reject policy applications
- Manage all active, expired and cancelled policies
- Triage claims: mark under review → approve → settle
- View full payment history and revenue totals
- Suspend / reactivate customer accounts
- Create and manage insurance plan products
- Admin profile and password management

---

## Security Features
- CSRF tokens on every form
- Password hashing with PHP `PASSWORD_BCRYPT`
- Account lockout after 5 failed login attempts (15-min cooldown)
- Session idle timeout (60 minutes)
- Remember-me via hashed cookie tokens
- Prepared statements (PDO) on all queries — no SQL injection
- File upload type and size validation
- PHP execution blocked inside `/uploads`
- Directory listing disabled via `.htaccess`

---

## Project Structure

```
airline-insurance/
├── admin/              Admin pages
├── auth/               Login, register, password reset
├── assets/
│   ├── css/            style.css · auth.css · customer.css · admin.css
│   └── js/             app.js
├── config/             config.php · database.php
├── customer/           Customer dashboard, plans, apply, claims, payments
├── database/           schema.sql
├── includes/           bootstrap, auth helpers, icons, layout partials
├── uploads/claims/     Uploaded claim documents
├── index.php           Public landing page
└── setup.php           One-click demo data seeder
```

---

## Configuration
Database credentials are in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'airline_insurance');
define('DB_USER', 'root');
define('DB_PASS', '');
```

Change these if your XAMPP MySQL uses a different username or password.
