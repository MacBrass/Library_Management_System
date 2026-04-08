# Fr. CRCE Library Management System

This repository contains the library management system for Fr. CRCE.

## Two Architecture Versions

This project ships with two distinct implementations to suit different environments. When making changes, it is important to remember what files serve each version.

### 1. PHP Version (Live Backend Server)
- **Environment**: Designed to run via XAMPP/WAMP or a remote Apache/Nginx web server with PHP support.
- **Files**: All `.php` files (e.g., `index.php`, `auth/login.php`, `student/dashboard.php`).
- **Data Source**: Connects to the local MySQL database defined in `config/database.php`. Needs the `library_db.sql` imported.
- **Logic**: Handles login, sessions, and book data via standard PHP backend processing.

### 2. Live HTML/JS Version (Static Server / Local Testing)
- **Environment**: Designed to run cleanly as static files (via a basic Live Server or directly in the browser).
- **Files**: All `.html` files (e.g., `index.html`, `auth/login.html`, `student/dashboard.html`).
- **Data Source**: Uses mock data arrays heavily defined in `assets/js/demo-backend.js`.
- **Logic**: All session and database mimicry is done inside the browser utilizing JavaScript localStorage (`demo-backend.js` and `demo-ui.js`).

## Keeping Versions Synced
If you apply styling, UI alignment (`style.css`), or structural updates, ensure those changes are duplicated to **both** the `.html` variant and the corresponding `.php` file.

**Shared Assets:**
Both versions actively share the `assets/` folder (such as `main.js`, `style.css`), so design updates inside `assets/css/style.css` apply automatically to both versions. However, if you add a new element to HTML structurally, don't forget to push it to the PHP version too!

## Test Accounts

For the static HTML version (`demo-backend.js`), these default credentials exist:
- **Admin**: `admin@frcrce.ac.in` / `Admin123`
- **Professor**: `prof@frcrce.ac.in` / `Prof1234`
- **Student**: `student@frcrce.ac.in` / `Student123`
