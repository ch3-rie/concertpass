
# PDC Concert Ticketing - System Run Guide

Follow these steps to set up and run the system on Windows using XAMPP.

## 1. Requirements

Install the following:
- XAMPP (Apache & MySQL)
- PHP (compatible with Laravel version)
- Composer
- Node.js & npm

## 2. Project Location

Place the project in:
`C:\xampp\htdocs\PDC-3-FINALPROJ-VER3`

## 3. First-Time Setup (One-Time Only)

Open a terminal in the project folder and run:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Edit `.env` to set your database credentials (see next step).

## 4. Database Setup

1. Start **Apache** and **MySQL** in XAMPP Control Panel.
2. Create a database in phpMyAdmin (e.g. `concert_ticket_reservation_system`).
3. In `.env`, set:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=concert_ticket_reservation_system
DB_USERNAME=root
DB_PASSWORD=
```

## 5. Database Migration & Seeding

**Option A (recommended):**

```bash
php artisan migrate --seed
```

**Option B (if you have a SQL dump):**
- Import the `.sql` file in phpMyAdmin
- Skip `migrate --seed` if the database is already complete

## 6. Build Frontend Assets

Compile CSS/JS assets (run after npm install):

```bash
npm run build
```

## 7. Storage Link (for images)

Run this once:

```bash
php artisan storage:link
```

## 8. Running the System (Daily Use)

Each time you want to use the system:

1. Start **Apache** and **MySQL** in XAMPP
2. In the project folder, run:
   ```bash
   php artisan serve
   ```
3. Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser

**Note:** If you change CSS/JS, re-run `npm run build`.

## 9. Troubleshooting

- **CSS not loading:**
  - Run `npm run build` and refresh the browser
  - Clear browser cache
  - Check `public/build/` for compiled assets
- **Blank page:**
  - Check `storage/logs/laravel.log`
  - Verify `.env` database credentials
  - Run `php artisan migrate` if needed
- **Database connection error:**
  - Check `.env` DB values
  - Ensure MySQL is running
  - Run `php artisan config:clear`
- **Application key missing:**
  - Run `php artisan key:generate`
- **Images not showing:**
  - Run `php artisan storage:link`
- **Config/class changes not updating:**
  - Run `php artisan optimize:clear`

