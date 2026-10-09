# Gilas — Restaurant Website & Table Ordering

Gilas is a restaurant-focused web application with public pages for its story, experience, location, reservation information, menus and menu items. The routes also include QR/token-based table ordering and customer order tracking, alongside an administration area. Verify the current implementation and provider configuration before relying on a workflow in production.

## Stack
- PHP `^8.2`, Laravel `^12.0`
- Vite 7, Tailwind CSS 4 and Laravel Vite integration
- Blade, Eloquent, migrations and seeders
- PHPUnit tests

## Requirements
PHP 8.2+, Composer, Node.js/npm, and a supported database.

## Install locally
```bash
git clone https://github.com/MREZA-MJDi/gilas.git
cd gilas
composer install
```

Copy `.env.example` to `.env` (`copy .env.example .env` on Windows CMD; `cp .env.example .env` on macOS/Linux), create a local database, and configure `DB_*` values.

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000`. Run `npm run dev` separately while developing frontend assets.

## Tests
```bash
php artisan test
```

## Operational notes
QR/table tokens should be treated as access-bearing values. Test order creation, validation, and order tracking before launch. Do not commit credentials or real customer/order data, and do not reset a database that contains data you need.

## Links
- Repository: https://github.com/MREZA-MJDi/gilas
- Laravel documentation: https://laravel.com/docs/12.x
