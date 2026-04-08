# FrCRCE college Library Management System

A modern, web-based Library Management System designed for **FrCRCE college, Bandra, Mumbai**. This system provides a seamless digital interface for students, professors, and administrators to manage book collections, requests, and borrowing transactions.

---

## 🚀 Key Features

### 👥 Role-Based Access Control
- **Admin Dashboard**: Full control over users, book inventory, fine settings, and system-wide analytics.
- **Professor Dashboard**: Browse books, request books on behalf of students (bulk), and manage personal borrowing history.
- **Student Dashboard**: Search for books, track current requests, view due dates, and monitor fines.

### 📚 Advanced Book Management
- **Smart AJAX Search**: Find books instantly by Title, Author, Category, or ISBN without page reloads.
- **Request Lifecycle**: Intelligent status tracking from `Requested` → `Approved` → `Issued` → `Returned`.
- **Automatic Fines**: System calculates overdue fines based on configurable per-role settings.

### 🏛️ Digital Receipts & Reports
- **Receipt Generation**: Professional PDF/HTML receipts for every book issuance and return.
- **Borrowing History**: Complete digital trail of all past transactions.

### 🛡️ Security First
- **BCRYPT Hashing**: All user passwords are securely hashed using modern bcrypt standards.
- **SQL Injection Protection**: Built with prepared statements for all database interactions.
- **Session Protection**: Secure session management to prevent unauthorized access.

---

## 🛠️ Technology Stack

- **Backend**: PHP (Vanilla)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, Modern CSS3 (Variables, Flexbox, Grid), JavaScript (AJAX)
- **Assets**: FontAwesome 6, Google Fonts (Inter)

---

## 📂 Project Structure

```text
library-system/
├── admin/          # Admin-specific controllers and views
├── assets/         # CSS, JS, and Images
├── auth/           # Login, Registration, and Logout logic
├── config/         # Database and global configuration
├── includes/       # Shared components (navbar, sidebar, etc.)
├── professor/      # Professor-specific dashboards
├── search/         # AJAX search handlers
├── student/        # Student-specific dashboards
├── uploads/        # Directory for book cover images
├── library_db.sql  # Database schema and sample data
└── setup.php       # Initial system configuration script
```

---

## ⚙️ Installation Guide

### 1. Prerequisites
- **Web Server**: XAMPP, WAMP, MAMP, or any PHP server environment.
- **PHP Version**: 7.4 or higher recommended.
- **MySQL Version**: 5.7 or higher.

### 2. Database Setup
1. Open your database management tool (e.g., phpMyAdmin).
2. Create a new database named `library_db`.
3. Import the `library_db.sql` file located in the `library-system/` folder.

### 3. Configuration
1. Open `library-system/config/database.php`.
2. Update the following constants to match your environment:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');   // Your DB username
   define('DB_PASS', '');       // Your DB password
   define('DB_NAME', 'library_db');
   ```

### 4. Finalizing Setup
1. Point your browser to: `http://localhost/library-system/setup.php`
2. This script will:
   - Verify the database connection.
   - Generate secure password hashes for sample accounts.
   - Confirm table structure.
3. **IMPORTANT**: Delete `setup.php` after the installation is complete for security reasons.

---

## 🔑 Default Login Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Admin** | `admin@crce.edu.in` | `Admin123` |
| **Professor** | `prof@crce.edu.in` | `Prof1234` |
| **Student** | `student@crce.edu.in` | `Student123` |

---

## 📝 License
This project is developed for **FrCRCE college, Mumbai**. All rights reserved.
