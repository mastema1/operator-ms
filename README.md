# Operator Management System

Laravel application for managing factory operators, attendance, and production line assignments.

## Stack
- Laravel 11 + PHP 8.2
- Livewire 3 + Tailwind CSS
- Authentication: Laravel Breeze

## Core Functions

### Operator Management (`/operators`)
- **CRUD Operations**: Create, view, edit, delete operators
- **Fields**: Matricule, first/last name, poste assignment, ancienneté, contract type, production line (ligne), capability status
- **Live Search**: Real-time filtering across all operator fields
- **Modal Forms**: Add/edit operators via AJAX modals
- **Pagination**: Custom styled pagination with AJAX navigation

### Absence Management (`/absences`)
- **Daily Attendance**: Toggle present/absent status for each operator
- **Live Counters**: Real-time present/absent/total operator counts
- **Search & Filter**: Find operators by name or poste
- **Date Display**: French-formatted current date header

### Post Status (`/post-status`)
- **Occupancy Tracking**: View which postes are occupied/vacant
- **Critical Status**: Visual indicators for critical vs non-critical postes
- **Backup Finder**: Modal showing available operators for vacant critical postes
- **Live Updates**: Real-time status changes via Livewire

### Poste Management (`/postes`)
- **CRUD Operations**: Manage production line positions
- **Critical Flag**: Mark postes as critical or non-critical
- **Predefined Postes**: Poste 1-40, Polyvalent, Bol, Fire Wall
- **Status Badges**: Visual critical/non-critical indicators

### Dashboard (`/dashboard`)
- **Quick Overview**: Operator management with search and actions
- **AJAX Interface**: All operations without page reloads
- **Modal Integration**: Inline create/edit functionality

## Quick Start
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
php artisan serve
```

**Default Login**: `test@local` / `12345678`

## Navigation
- **Dashboard**: `/dashboard` - Quick operator overview
- **Operators**: `/operators` - Full operator management
- **Absences**: `/absences` - Daily attendance tracking  
- **Post Status**: `/post-status` - Production line occupancy
- **Postes**: `/postes` - Position management

## Key Features
- **Live Search**: Instant filtering without page reloads
- **AJAX Operations**: All CRUD operations via modals/toggles
- **Real-time Counters**: Attendance statistics update instantly
- **Custom Pagination**: Consistent styling across all pages
- **Responsive Design**: Mobile-friendly interface
- **Secure Authentication**: Login required for all functions

## Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- Database (MySQL/MariaDB/SQLite). Default SQLite supported out-of-the-box

## Installation
1. Clone the repository
2. Install PHP deps
```bash
composer install
```
3. Install JS deps
```bash
npm install
```
4. Environment
```bash
cp .env.example .env
php artisan key:generate
```
5. Database
- For SQLite (default): ensure `database/database.sqlite` exists
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```
- Or configure `.env` for MySQL and create the database
6. Migrate
```bash
php artisan migrate --force
```
7. Build assets (dev or prod)
```bash
npm run dev
# or
npm run build
```
8. Breeze scaffolding (if not already installed)
```bash
php artisan breeze:install blade --dark
npm run build
```

## Running
```bash
php artisan serve
```
Visit http://localhost:8000 and register a user.

## Usage
- Use the top search to filter by first/last name or poste name
- Click “Add New Operator” to open the modal. Fill First Name, Last Name, Poste, and Capability
- Toggle capability in the table to update instantly
- On Absences, use the toggle to mark present/absent for today; summary cards update automatically
- On Post Status, view whether each poste is occupied. If vacant, click “Find Backup” to see present, matching operators

## Security
- CSRF protection via Laravel middleware
- Escaped Blade templates prevent XSS
- Eloquent uses parameter binding to avoid raw SQL injection
- Auth middleware restricts access to application pages

## Tests
Run the feature tests:
```bash
php artisan test
```
Covers:
- Login
- Auth-protected pages
- Create operator via modal
- Search filtering
- Capability and absence toggles

## Deployment
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Configure `APP_URL` and database credentials
- Run migrations: `php artisan migrate --force`
- Build assets: `npm run build`
- Cache config/routes/views:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
- Ensure proper file permissions for `storage/` and `bootstrap/cache`
- Use a process manager (e.g., Supervisor) or queue worker if needed
- Serve with Nginx/Apache and PHP-FPM; point webroot to `public/`

## Notes
- Seed initial postes/operators as needed via factories
- Pagination default: 15
- Date localization uses `fr_FR` for headers
