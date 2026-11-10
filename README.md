# BestWeb User Management📂


A Laravel-based User Management System developed for the BestWeb Technologies Laravel Developer Assessment.

The application includes a RESTful API, an admin web interface, role-based access control, user management, Excel export, API documentation, and automated tests.

## Tech Stack

- Laravel 13 / PHP
- MySQL
- Laravel Blade + Bootstrap 5
- Laravel Sanctum
- Laravel Excel
- L5-Swagger (OpenAPI)
- PHPUnit
- SQLite (in-memory testing database)

## Features

### Admin Web Interface

- Admin login and logout
- User listing with status filtering
- Pagination
- Create and edit users
- Unique email and phone number validation
- Soft delete users
- Bulk delete users
- Export users to Excel

### RESTful API

- Public user listing with limited fields
- Admin-only user creation, details, updates and deletion
- Status filtering and pagination
- Soft delete and bulk delete
- Excel export
- Form Request validation
- Sanctum authentication for protected endpoints

## Requirements

- PHP and Composer
- MySQL
- Required PHP extensions
- A supported local development environment

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/TanZeQuan/bestweb-laravel-assessment.git
cd bestweb-laravel-assessment
```

Replace `<repository-url>` with the actual GitHub repository URL.

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy `.env.example` to `.env`.

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

### 4. Configure MySQL

Create a new MySQL database named `bestweb_assessment`.

Update `.env` with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bestweb_assessment
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

Do not commit the `.env` file.

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Start the application

```bash
php artisan serve
```

The application will be available at:

http://127.0.0.1:8000

## Admin Account Setup

Create an admin account using Laravel Tinker:

```bash
php artisan tinker
```

Run these commands one at a time:

```php
$password = \Illuminate\Support\Str::password(18);

$admin = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'phone_number' => '0100000001', 'password' => $password, 'status' => 'active']);

$admin->forceFill(['role' => 'admin'])->save();

$password;
```

Save the generated password securely. It is displayed only for local setup and should not be committed to the repository.

Admin login:

http://127.0.0.1:8000/login

## API Endpoints

| Method | Endpoint | Access | Description |
|---|---|---|---|
| GET | `/api/users` | Public | List users |
| POST | `/api/users` | Admin | Create user |
| GET | `/api/users/{id}` | Admin | View user details |
| PATCH | `/api/users/{id}` | Admin | Update user |
| DELETE | `/api/users/{id}` | Admin | Soft delete user |
| POST | `/api/users/bulk-delete` | Admin | Bulk delete users |
| GET | `/api/users/export` | Admin | Download Excel file |

### Filtering and Pagination

Example:

```http
GET /api/users?status=active&per_page=10&page=1
```

Supported status values:

- `active`
- `inactive`

The API returns paginated results.

### Bulk Delete Request

```json
{
  "user_ids": [2, 3, 4]
}
```

Protected API endpoints require Sanctum bearer-token authentication.

## API Authentication

For local API testing, an admin API token can be generated using Tinker:

```php
$admin = \App\Models\User::where('email', 'admin@example.test')->firstOrFail();

$token = $admin->createToken('api-testing')->plainTextToken;

$token;
```

Use the generated token in the Authorization header:

```http
Authorization: Bearer YOUR_TOKEN
```

Keep tokens private. Revoke testing tokens when they are no longer needed.

The application does not provide a public API login or token-issuance endpoint.

## Swagger Documentation

Generate the OpenAPI documentation:

```bash
php artisan l5-swagger:generate
```

Open Swagger UI:

http://127.0.0.1:8000/api/documentation

Protected endpoints require an admin bearer token.

## Automated Testing

PHPUnit is used for automated Feature Tests.

The testing environment uses SQLite in-memory through `phpunit.xml`, separately from the MySQL development database.

Run all tests:

```bash
php artisan test
```

Run individual test suites:

```bash
php artisan test tests/Feature/Api/UserApiTest.php
php artisan test tests/Feature/Admin/AdminAuthTest.php
php artisan test tests/Feature/Admin/AdminUserTest.php
```

Run Laravel Pint:

```bash
./vendor/bin/pint --test
```

Latest verified local results:

- 33 tests passed
- 102 assertions

## Security and Design Decisions

- Admin web authentication uses Laravel sessions.
- Protected API routes use Laravel Sanctum.
- Only admins can manage users.
- The public user list returns limited fields and excludes email, phone number and password.
- Form Requests validate user input.
- Email addresses and phone numbers must be unique.
- Passwords are stored as hashes.
- SoftDeletes preserves deleted records.
- Admin self-deletion is blocked.
- Blade forms use CSRF protection.
- Deleted users are excluded from normal listings and Excel exports.

## Assumptions

- Public API access is limited to the minimal user listing endpoint.
- User management and Excel export require admin authentication.
- New users receive the default `user` role.
- Excel exports include non-deleted users and are not restricted by the current frontend status filter.
- Bulk deletion applies only to the submitted user IDs.
- Admin accounts are initialized manually for assessment purposes.

## Project Structure

```text
app/
  Exports/
  Http/
    Controllers/
      Admin/
      Api/
    Requests/
  Models/
  Swagger/

resources/
  views/
    admin/

routes/
  api.php
  web.php

tests/
  Feature/
    Admin/
    Api/
```

## Assessment

Developed as a technical assessment project for BestWeb Technologies.
