# Hermes + Dropbox → SeatWeb Import

Auto-sync Excel trip/customer files from Dropbox into SeatWeb (Neon) via Hermes Agent.

## Architecture

```
Dropbox Excel  →  Hermes (read + map)  →  POST /api/imports/dropbox-excel  →  Neon
```

Hermes never receives `DATABASE_URL`. It only gets `IMPORT_API_TOKEN`.

## Chatbot + CRUD API

Same Bearer token. Base URL: `/api/hermes`

| Action | Endpoint |
|---|---|
| Chat | `POST /api/hermes/chat` `{"message":"list package"}` |
| Read | `GET /api/hermes/packages` `departures` `registrations` `overview` |
| Create | `POST` same paths |
| Edit | `PUT /api/hermes/packages/{id}` (and departures / registrations) |
| Archive package | `DELETE /api/hermes/packages/{id}` |
| Cancel trip | `DELETE /api/hermes/departures/{id}` |
| Delete pax | `DELETE /api/hermes/registrations/{id}` |

Staff UI chat: `/hermes/chat` (login required, no token).

## API

- **URL:** `POST /api/imports/dropbox-excel`
- **Auth:** `Authorization: Bearer <IMPORT_API_TOKEN>`
  - Or header: `X-Import-Token: <IMPORT_API_TOKEN>`
- **Dry run:** `POST /api/imports/dropbox-excel?dry_run=1` (logs to `import_runs`, does not write packages/departures/registrations)

### Env (Render)

```
IMPORT_API_TOKEN=generate-a-long-random-string
```

Generate example:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

---

## Excel → JSON contract (v1)

Hermes should convert Excel rows into this JSON shape (see also `docs/samples/dropbox-excel-import.sample.json`).

### Suggested sheet / columns

You can use **one sheet** or **three sheets** (`packages`, `departures`, `registrations`). Column names are case-insensitive after Hermes normalizes them.

#### Packages

| Excel column | JSON field | Required |
|---|---|---|
| Package / Trip / Package Name | `name` | yes |
| Destination | `destination` | yes |
| Description / Notes | `description` | no |
| Status | `status` (`active` \| `archived`) | no (default `active`) |

#### Departures

| Excel column | JSON field | Required |
|---|---|---|
| Package / Package Name | `package_name` | yes |
| Destination | `destination` | recommended |
| Departure Date | `departure_date` | yes (`YYYY-MM-DD`) |
| Return Date | `return_date` | yes |
| Total Seats / Seats | `total_seats` | no (default 1) |
| Price | `price` | no |
| Airline | `airline` | no |
| Status | `status` (`open` \| `cancelled`) | no |
| Notes | `notes` | no |

#### Registrations (customers)

| Excel column | JSON field | Required |
|---|---|---|
| Name / Customer Name | `name` | yes |
| Phone | `phone` | no (used for upsert match) |
| Pax / Pax Count | `pax` | no (default 1) |
| Need Partner | `need_partner` | no (`yes`/`no`/`true`/`false`) |
| Partner Gender | `partner_gender` | required if need partner (`male`/`female`) |
| Package / Package Name | `package_name` | yes (unless `departure_id`) |
| Destination | `destination` | recommended |
| Departure Date | `departure_date` | yes (unless `departure_id`) |
| Notes | `notes` | no |

### Upsert keys (no duplicates on re-sync)

| Entity | Match on |
|---|---|
| Package | `name` + `destination` (case-insensitive) |
| Departure | package + `departure_date` |
| Registration | departure + `name` + normalized phone digits |

Seat capacity rules still apply; over-capacity rows are **skipped** and listed in `errors`.

---

## Hermes setup (outside this repo)

1. Install and run [Hermes Agent](https://github.com/NousResearch/hermes-agent/) on a machine that stays on (VPS recommended; laptop off = agent off).
2. Connect Dropbox (ClawLink / Composio / OAuth).
3. Create folders:
   - `/SeatWeb/Incoming/` — drop new `.xlsx` / `.csv` here
   - `/SeatWeb/Processed/` — move files here after success
4. Set secrets in Hermes env (not in git):
   - `SEATWEB_IMPORT_URL=https://nusatravel-seat.onrender.com/api/imports/dropbox-excel`
   - `SEATWEB_IMPORT_TOKEN=<same as Render IMPORT_API_TOKEN>`
5. Cron / watch prompt for Hermes (adapt to your agent):

```
Every 5 minutes:
1. List new .xlsx/.csv files in Dropbox /SeatWeb/Incoming/
2. Download each file
3. Parse sheets into packages, departures, registrations JSON
   using the SeatWeb Excel contract in docs/hermes-dropbox-sync.md
4. First POST with ?dry_run=1 and check errors
5. If OK, POST without dry_run with header:
   Authorization: Bearer $SEATWEB_IMPORT_TOKEN
6. On HTTP 200, move file to /SeatWeb/Processed/
7. On failure, leave file in Incoming and report the JSON errors
```

### Example curl

```bash
curl -X POST "https://nusatravel-seat.onrender.com/api/imports/dropbox-excel?dry_run=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d @docs/samples/dropbox-excel-import.sample.json
```

### Render Free note

Free web services sleep after idle. For reliable Hermes auto-sync, use Render **Starter (~USD 7)** so the API is awake when Hermes posts.

---

## Response shape

```json
{
  "import_run_id": 1,
  "dry_run": false,
  "status": "completed",
  "counts": {
    "packages_created": 1,
    "packages_updated": 0,
    "departures_created": 1,
    "departures_updated": 0,
    "registrations_created": 2,
    "registrations_updated": 0,
    "skipped": 0
  },
  "errors": [],
  "summary": { "packages": [], "departures": [], "registrations": [] }
}
```

Runs are stored in the `import_runs` table.
