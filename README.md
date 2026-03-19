# Cherith Junior School Management System

A Laravel-based school management system with Filament panels for admin and teachers. Supports student records, payments, fees, and teacher result entry.

## Features

- **Admin Panel** (`/admin`)
  - Student management with admissions and fee tracking
  - Payment records and receipt generation
  - Class/Standard management
  - Subject management
  - Teacher assignment to subjects and classes
  - Teacher user creation and credential management

- **Teacher Panel** (`/teacher`)
  - Secure login for assigned teachers
  - View and enter student scores for assigned subjects/classes
  - Student list filtered by assigned class

- **Reports**
  - Student debt reports
  - Payment receipts

## Tech Stack

- **Backend:** Laravel 12.47.0, PHP 8.2.12
- **Frontend:** Blade + TailwindCSS
- **Admin/Teacher UI:** Filament v4.5
- **Database:** MySQL with InnoDB

## Installation

1. Clone repo
   ```bash
   git clone https://github.com/flavy-hash/cherith-school-management.git
   cd cherith-school-management
   ```

2. Install dependencies
   ```bash
   composer install
   npm install
   npm run build
   ```

3. Environment setup
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your `.env` for database (MySQL)

5. Run migrations and seed
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Start the development server
   ```bash
   php artisan serve
   ```

## Default Login

- **Admin:** `admin@cherithschool.ac.tz` / `password`
- **Teacher:** `teacher1@cherithschool.ac.tz` / `password`

## Usage

### Admin Workflow

1. Login at `/admin`
2. Add/edit subjects under **System > Subjects**
3. Create teachers and assign them to subjects/classes under **System > Teacher Assignments**
4. Manage students, payments, and reports from their respective sections

### Teacher Workflow

1. Login at `/teacher/login`
2. View assigned students under **Results > Enter Scores**
3. Enter or update scores for your assigned subject/class

## Project Structure

- `app/Filament/Resources/` — Filament admin and teacher resources
- `app/Models/` — Eloquent models (Student, Standard, Subject, TeacherSubject, User, etc.)
- `database/migrations/` — Laravel migrations
- `database/seeders/` — Seeders for initial data
- `resources/views/` — Blade views (reports, welcome)

## Contributing

1. Fork the repo
2. Create a feature branch
3. Commit with clear messages
4. Push and open a pull request

## License

This project is open-sourced software licensed under the MIT license.
