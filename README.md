# Translation Management API

A Laravel-based REST API for managing and exporting translations with locales, tags, and version control.  
Designed for scalability and performance testing.

---

## Setup

**Requirements**
- PHP 8.2+
- Composer
- MySQL
- Laravel 11

**Steps**

1. Install and configure:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate

2. Update .env

`DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_test
DB_USERNAME=root
DB_PASSWORD=root`

3. Run migrations and seeders
`php artisan migrate --seed`

4. Start the app
`php artisan serve`

## About Application

**Authentication**
Sanctum is used for token-based authentication.

**API Endpoints**
| Method | Endpoint                           | Description                                 |
| ------ | ---------------------------------- | ------------------------------------------- |
| POST   | `/api/login`                       | Get access token                            |
| GET    | `/api/translations`                | List or search by tag, key, content, locale |
| POST   | `/api/translations`                | Create translation                          |
| GET    | `/api/translations/{id}`           | View single translation                     |
| PUT    | `/api/translations/{id}`           | Update translation                          |
| GET    | `/api/export?locale=en&tags[]=web` | Export JSON translations                    |


## Design Notes
- Sanctum for API security
- Normalized schema: keys, values, tags, locales
- Efficient joined query for export
- Eloquent scopes for clean search filters
- ETag headers for caching
- Indexes for locale, updated_at, and tag name
- Seeder generates 100k+ records for performance tests
- Export query averages ~260 ms for a 4.6 MB JSON payload
- CRUD query averages ~150 ms
