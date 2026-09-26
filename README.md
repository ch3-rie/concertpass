# ConcertPass

ConcertPass is a concert ticket reservation system. Attendees browse upcoming events, choose seats or general admission, complete checkout, and receive digital tickets. Administrators manage venues, concerts, ticket inventory, users, transactions, and sales analytics from a separate dashboard.

The application is built with Laravel. The public site uses session authentication. A token-based API, powered by Laravel Sanctum, exposes the same administrative records for external clients.

## Features

### Attendees

- Browse upcoming concerts and filter by artist, title, location, or date.
- View event details, venue, date, and ticket prices.
- Select seated tickets on a live seat map, or request general admission tickets that the system assigns automatically.
- Review the order, enter card details, and confirm payment.
- View booking history and issued tickets, including seat assignments and a QR code.

Checkout validates the card number, expiry, CVV, and cardholder name, then confirms the booking inside the application. It does not charge a third-party payment processor. Card numbers are masked when displayed.

### Administrators

- Dashboard with tickets sold, revenue, users, concerts, bookings, and recent transactions.
- Analytics for monthly revenue, sales volume, new users, and payment method.
- Create and edit venues. Seat rows are generated from venue capacity and ticket allocations.
- Create and edit concerts, posters, seat plans, and per-event ticket types with price, color, and quantity.
- Block deletion of a concert after tickets have been sold.
- Manage user accounts and roles.
- Review transactions and an activity log of administrative and booking actions.
- Track allocated versus sold seats for each event.

Administrators are sent to the admin dashboard after login and cannot use the public booking flow.

### API

- Sanctum bearer tokens for login, logout, and the current user.
- Admin-only JSON endpoints for users, concerts, and venues.
- Session-authenticated booking endpoints used by the seat map (ticket options, available seats, and booking creation).

## Technology

| Layer | Stack |
| --- | --- |
| Backend | PHP 8.2, Laravel 12 |
| Auth | Laravel Breeze (session) and Laravel Sanctum (API tokens) |
| Database | MySQL |
| Frontend | Blade, Tailwind CSS, Alpine.js, Chart.js |
| Tests | PHPUnit 11 |

Styles and charts are loaded from a CDN in the layouts, so a frontend build step is not required to run the application.

## Requirements

- PHP 8.2 or newer, with the extensions Laravel needs (`openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`)
- [Composer](https://getcomposer.org/)
- MySQL 8 (or MariaDB) — on Windows, XAMPP is a typical setup
- A web browser

Node.js and npm are optional. They are only needed if you compile Tailwind locally.

## Installation

From the project root:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

On macOS or Linux, use `cp .env.example .env` instead of `copy`.

Create a MySQL database named `concert_ticket_reservation_system`, then set the connection in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=concert_ticket_reservation_system
DB_USERNAME=root
DB_PASSWORD=
```

Start MySQL, then migrate and seed:

```bash
php artisan migrate --seed
php artisan storage:link
```

`storage:link` publishes uploaded concert posters and seat-plan images under `public/storage`.

Start the application:

```bash
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

On Windows with XAMPP, start Apache and MySQL from the XAMPP control panel before `php artisan serve`. Apache is not required when you use the built-in PHP server.

## Demo accounts

Seeding creates two local accounts:

| Role | Email | Password |
| --- | --- | --- |
| Administrator | `admin@gmail.com` | `admin_1234` |
| Attendee | `user@gmail.com` | `user_1234` |

Change these passwords before any shared or production deployment. Seed data also includes venues (including Philippine arenas), seat sections scaled to each venue’s capacity, and sample concerts with ticket prices.

## Roles

| Role | Access |
| --- | --- |
| Guest | Home page, concert listing, and concert details |
| `user` | Booking, checkout, tickets, booking history, and profile |
| `admin` | `/admin` dashboard, concerts, venues, users, transactions, analytics, ticket inventory, and activity logs |

Registration creates an attendee account. Only an administrator can assign the admin role.

## Booking flow

1. Open a concert and choose **Book**.
2. Select ticket types. Seated types (VIP Seated, Lower Box, Upper Box, and similar) require a specific seat. General admission and VIP standing are assigned from the remaining pool.
3. Review quantities and the total.
4. Enter card details and accept the terms.
5. The system locks inventory, creates a confirmed booking and paid payment, and issues tickets.

Seat assignment and inventory checks run inside a database transaction so two buyers cannot take the same seat.

## Project layout

```
app/
  Http/Controllers/          Web controllers (booking, concerts, admin)
  Http/Controllers/Api/      Sanctum auth and admin JSON controllers
  Models/                    User, Concert, Venue, Booking, Ticket, Payment, Seat
  Services/                  Inventory, seat availability, and booking persistence
database/
  migrations/                Schema
  seeders/                   Demo users, venues, seats, and concerts
resources/views/             Blade pages for the public site and admin panel
routes/
  web.php                    Public site, booking, and admin dashboard
  api.php                    Token-authenticated JSON API
```

## REST API

Base URL when using `php artisan serve`: `http://127.0.0.1:8000`.

### Token authentication

```http
POST /api/login
Content-Type: application/json

{ "email": "admin@gmail.com", "password": "admin_1234" }
```

The response is `{ "token": "..." }`. Send it on later requests:

```http
Authorization: Bearer {token}
```

| Method | Path | Access |
| --- | --- | --- |
| `POST` | `/api/login` | Public |
| `POST` | `/api/logout` | Authenticated |
| `GET` | `/api/me` | Authenticated |
| `GET`, `POST` | `/api/admin/users` | Admin |
| `GET`, `PUT`, `DELETE` | `/api/admin/users/{user}` | Admin |
| `GET`, `POST` | `/api/admin/concerts` | Admin |
| `GET`, `PUT`, `DELETE` | `/api/admin/concerts/{concert}` | Admin |
| `GET`, `POST` | `/api/admin/venues` | Admin |
| `GET`, `PUT`, `DELETE` | `/api/admin/venues/{venue}` | Admin |

A concert that already has sold tickets cannot be deleted. The API returns `422` in that case.

Dashboard charts (`/admin/api/metrics`, `/admin/api/analytics`, `/admin/api/activity-logs`) use the admin browser session, not a bearer token.

### Seat map (browser session)

These routes require a logged-in attendee. The booking pages call them with the session cookie.

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/concerts/{concert}/ticket-options` | Prices and remaining quantity |
| `GET` | `/api/concerts/{concert}/seats` | Seats for one ticket type |
| `POST` | `/api/concerts/{concert}/bookings` | Create a booking from selected items |

More request examples are in [API_AUTHENTICATION.md](API_AUTHENTICATION.md).

## Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database. They cover authentication, profile updates, booking validation, ticket availability, and admin validation.

## Troubleshooting

| Symptom | What to check |
| --- | --- |
| Database connection error | MySQL is running, `.env` credentials match the database, then `php artisan config:clear` |
| Missing application key | `php artisan key:generate` |
| Posters or seat plans do not load | `php artisan storage:link` and confirm files exist under `storage/app/public` |
| Blank page or 500 error | `storage/logs/laravel.log` |
| Stale config after a code change | `php artisan optimize:clear` |
| `401` on `/api/admin/*` | Send `Authorization: Bearer {token}` from a fresh login |
| `403` on `/api/admin/*` | The token belongs to a user whose `role` is not `admin` |
