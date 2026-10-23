# Gilas — Restaurant Website & Table Ordering

Gilas is a Laravel restaurant application with public pages for the restaurant story, menu, menu-item details, table QR ordering, order tracking, reservations and an administration area.

## Stack

- PHP ^8.2, Laravel ^12.0
- Vite 7, Tailwind CSS 4 and Laravel Vite integration
- Blade, Eloquent, migrations and seeders
- PHPUnit

## Local setup (Windows / XAMPP)

    git clone https://github.com/MREZA-MJDi/gilas.git
    cd gilas
    composer install
    copy .env.example .env

Create a MySQL database named gilas in phpMyAdmin. Update .env with the restaurant's actual details. To create the first owner during seeding, fill in GILAS_OWNER_NAME, GILAS_OWNER_EMAIL and a strong GILAS_OWNER_PASSWORD in the private .env file.

    php artisan key:generate
    php artisan config:clear
    php artisan migrate --seed
    npm install
    npm run build
    php artisan storage:link
    php artisan serve

Open http://127.0.0.1:8000. Run npm run dev separately during frontend development.

## Initial data policy

The default seeder is production-oriented and idempotent. It provisions the restaurant record, its settings and editable catalog categories. It does not create fake menu items/prices/photos, fake customers, sample orders/payments, invented table layouts, fake opening hours, or accounts with a known password.

Enter the restaurant's real menu, prices, images, table layout and opening hours through the dashboard. Alternatively, extend the seeder with the restaurant's confirmed source data. Optional owner creation requires all three GILAS_OWNER_* values in .env; existing user passwords are not overwritten on re-seed. Re-running the seeder preserves details already edited in the dashboard.

For an already-populated database, php artisan migrate --seed does not delete existing records. Back up important data before any manual cleanup; never run migrate:fresh on a database that must be preserved.

## Tests

    php artisan test

## Operational notes

Treat QR/table tokens as access-bearing values. Verify order creation, validation, authorization, payment provider settings and order tracking before production launch. Never commit .env, credentials, or real customer/order data.

- Repository: https://github.com/MREZA-MJDi/gilas
