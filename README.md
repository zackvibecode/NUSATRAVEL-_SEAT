# SeatWeb — Trip Seat Availability

Internal web app for NusaTravel staff to track tour packages, departures, seat capacity, and customer registrations. Data is synced automatically from Excel files via a Hermes agent.

## What it does

- **Dashboard** — upcoming trips, capacity summary, nearly-full alerts, recent Hermes seat changes
- **Trips / Departures** — one departure = one travel date with its own seat capacity
- **Packages** — group multiple departure dates under one package
- **Participants** — registered customers per trip (one row can be a family of N pax)
- **Hermes Update** — every seat change Hermes made: package, seat delta, travel date
- **Hermes Chat** — ask questions / make changes via chatbot
- **Reports** — monthly totals + CSV export
- **Calendar** — month view of departures with seat status

Core rules:

- Available seats are **always calculated** (`total_seats` − `SUM(registrations.pax)`), never stored
- One registration row can represent multiple pax
- Status is derived: `cancelled` → `departed` → `full` (0 left) → `almost_full` (1–5) → `open`

## Tech stack

- Laravel 13 (PHP 8.3), Blade, Tailwind CSS v4
- PostgreSQL (Neon) in production, SQLite in tests
- spatie/laravel-backup for nightly DB backups
- Render for hosting

## Local setup

```bash
git clone https://github.com/zackvibecode/NUSATRAVEL-_SEAT.git
cd NUSATRAVEL-_SEAT
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

Create the first staff user with `php artisan tinker`:

```php
App\Models\User::create([
    'name' => 'Your Name',
    'email' => 'you@company.com',
    'password' => bcrypt('a-strong-password'),
]);
```

## Environment variables

| Variable | Purpose |
|---|---|
| `APP_URL` | Public URL, e.g. `https://nusatravel-seat.onrender.com` |
| `DB_CONNECTION` / `DATABASE_URL` | `pgsql` + Neon connection string in production |
| `IMPORT_API_TOKEN` | Bearer token for the Hermes import/CRUD API — generate with `php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"` |
| `MAIL_MAILER` + SMTP vars | Needed for password reset, capacity alerts, backup notifications |
| `MAIL_FROM_ADDRESS` | Sender for all app email |
| `SEATWEB_ALERT_THRESHOLD` | Seats-left count that triggers the daily capacity email (default 5) |
| `SEATWEB_ALERT_DAYS_AHEAD` | Only alert on trips departing within N days (default 30) |
| `SEATWEB_BACKUP_MAIL_TO` | Recipient for backup success/failure emails |
| `BACKUP_ARCHIVE_PASSWORD` | Optional AES encryption for backup zips |

## Scheduled tasks

Registered in `routes/console.php` (run `php artisan schedule:work` locally; Render cron uses `php artisan schedule:run`):

| Task | Schedule (Asia/Kuala_Lumpur) |
|---|---|
| `backup:run` | Daily 02:00 |
| `backup:clean` | Daily 02:30 |
| `seatweb:send-capacity-alerts` | Weekdays 09:00 |

## Hermes sync

Hermes reads Excel from Dropbox `/SeatWeb/Incoming/`, converts rows to JSON, and POSTs to this app. Full contract: [docs/hermes-dropbox-sync.md](docs/hermes-dropbox-sync.md).

```
POST /api/imports/dropbox-excel        batch upsert (packages, departures, registrations)
GET/POST/PUT/DELETE /api/hermes/*      CRUD + chat for the agent
```

All Hermes endpoints require `Authorization: Bearer <IMPORT_API_TOKEN>`.

Every seat change is recorded in `hermes_seat_activities` (package name, signed seat delta, travel departure date) and shown on the dashboard and **Hermes Update** page.

## Deploying to Render

1. Push to `main` — Render auto-deploys
2. Env vars are set in the Render dashboard; never commit real secrets
3. After the first deploy of a new migration, run `php artisan migrate --force` (Render shell or a deploy hook)

## Tests

```bash
php artisan test
```

CI runs the suite on every push via GitHub Actions (`.github/workflows/ci.yml`).

## Project layout

```
app/
  Console/Commands/   capacity alerts, keep-alive commands
  Http/Controllers/   web + API (Hermes import, CRUD)
  Mail/               capacity alert, password reset
  Models/             Package, Departure, Registration, HermesSeatActivity, ImportRun
  Services/           DropboxExcelImportService, HermesDataService, SeatMetricsService, HermesSeatActivityLogger
database/migrations/  schema
docs/                 Hermes sync contract + samples
resources/views/      Blade UI
routes/               web.php, api.php, console.php (schedule)
```
