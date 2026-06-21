# Hanara Schools Management System

A production-grade, high-fidelity school management system tailored for Hanara Schools in Ghana, covering Nursery, Kindergarten, Primary, and Junior High School (JHS) levels. Built in accordance with Ghana Education Service (GES) academic standards and grading guidelines.

## Tech Stack
- **Backend**: Laravel 11, SQLite/MySQL
- **Frontend**: Blade, Tailwind CSS v3, Livewire 3, Alpine.js
- **Aesthetic Guidelines**: High-fidelity Google Stitch Material 3 colors, typography, elevations, and design system tokens.

---

## Features (Phase 1 — Foundation & Architecture)
- **Database Architecture**: Flexible, cross-academic-year historical pivots preserving enrollment history without student record duplication.
- **Authentication & RBAC**: Spatie-based 7-tier role system (Proprietor, HeadTeacher, ClassTeacher, SubjectTeacher, Accounts, FrontDesk, Parent) with granular action permissions.
- **First-Login Force**: Users are forced to reset password upon their first login prior to accessing their dashboard.
- **Proprietor Dashboard**: High-fidelity custom dashboard showing enrollment charts, metrics cards, at-risk alerts, and activity feed.
- **System Settings**: General school details, grading system parameters, and active calendar selection.
- **Audit Logging**: Immutable tracking of database changes including previous and new values, actor, and IP address.
- **Database Backup**: Local database copy/mysqldump with daily scheduling and 7-day file retention.

---

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- SQLite (default) or MySQL

### Installation

1. **Clone the repository and enter directory**:
   ```bash
   cd school-app
   ```

2. **Install Composer dependencies**:
   ```bash
   composer install
   ```

3. **Install NPM dependencies**:
   ```bash
   npm install
   ```

4. **Copy the Environment configuration**:
   ```bash
   copy .env.example .env
   ```

5. **Generate the App Key**:
   ```bash
   php artisan key:generate
   ```

6. **Initialize the SQLite database**:
   - Create an empty database file in the database folder (e.g. `database/database.sqlite`).

7. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate:fresh --seed
   ```

8. **Build Assets**:
   ```bash
   npm run build
   # OR for hot reloading
   npm run dev
   ```

9. **Run the Development Server**:
   ```bash
   php artisan serve
   ```

---

## Demo Credentials (Seeded Accounts)

All accounts are seeded with the default password **`password123`** and have `must_change_password` set to `true` to demonstrate first-login behavior.

| Role | Email | Profile Name |
|---|---|---|
| **Proprietor** | `proprietor@hanara.edu.gh` | Nana Akua Mensah |
| **Head Teacher** | `headteacher@hanara.edu.gh` | Kofi Addo |
| **Bursar (Accounts)** | `accounts@hanara.edu.gh` | Kwame Boateng |
| **Front Desk** | `frontdesk@hanara.edu.gh` | Ama Serwaa |
| **Class Teacher** | `ekow.eshun@hanara.edu.gh` | Ekow Eshun |
| **Subject Teacher** | `kweku.mensah@hanara.edu.gh` | Kweku Mensah |
| **Parent (Sample)** | `parent@example.com` | (Seeded Guardian Name) |

---

## Verification & Testing

### Running Tests
Execute the comprehensive test suite verifying authentication, RBAC boundaries, dashboard redirection, settings validation, and student ID generation:
```bash
php artisan test
```

### Manual Backup
To trigger an immediate backup of the database:
```bash
php artisan app:backup-database
```
Backups are saved in `storage/app/backups/`.
