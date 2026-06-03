# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

PowerCabs **Dispatcher** — a server-rendered PHP web app for taxi/ride operators. Dispatchers create orders, assign drivers, track rides live on a map, and manage app/corporate rides. There is no build step and no framework: each top-level `.php` file is a directly-served page, `api/*.php` files are JSON endpoints, and all persistence is a hosted **Supabase** project reached over its PostgREST + Auth REST APIs via cURL. The driver and passenger mobile apps (Flutter, not in this repo) write to the same Supabase tables.

## Running locally

Requires PHP 8.3+ with the cURL extension. Composer is only used to vendor PHPMailer (`composer install` if `vendor/` is missing).

```powershell
php -S localhost:8000   # serves from repo root; visit http://localhost:8000/ (→ login)
```

Apache/Nginx in production serves the repo root as docroot. There is no test suite, linter, or CI configured.

## Architecture

### Request flow
- `index.php` → includes `login.php` (the public entry). Successful login posts to `auth/login.php`.
- Every protected page starts with `session_start()` and redirects to `/` if `$_SESSION['user']` / `$_SESSION['access_token']` are missing (see top of [home.php](home.php#L4-L7)).
- Every `api/*.php` endpoint repeats the same inline session guard and returns `401` JSON when unauthenticated. (`auth/require_auth.php` exists for this but most endpoints inline the check instead of including it.) Endpoints respond with a consistent shape: `{ success, data, error?, message?, pagination? }`.
- Pages are assembled from partials in `modules/`: `head.php` (CDN Bootstrap 5.3 + Bootstrap Icons), `sidebar.php` (nav + active-link highlighting by filename), `navbar.php` (page title map + session user), `bodyHeader.php`.

### Auth & data access — `auth/config.php`
This is the heart of the backend. It defines Supabase URL + keys and the `SupabaseDB` class, which wraps PostgREST with cURL: `fetchData` (select/order/pagination via `Range` header), `getCount` (exact count via `Content-Range`), `findData` (eq filters), `insertData`, `updateData`, `deleteData` (both keyed on `id`).

`new SupabaseDB(null, true)` uses the **service-role key** (bypasses RLS) — this is what nearly every endpoint does. `new SupabaseDB($userToken)` would use the logged-in user's JWT. Login (`auth/login.php`) hits Supabase's `/auth/v1/token?grant_type=password`; admin user creation hits `/auth/v1/admin/users` with the service-role key (see `createSupabaseUser` in [api/create_order.php](api/create_order.php#L29)).

> Secrets (Supabase service-role key, SMTP password) are currently hardcoded in `auth/config.php` and `lib/mail_helper.php`. Treat them as live credentials; do not echo them into logs or new files.

### Core domain: rides
The `rides` table is central. Key fields written by the dispatcher: `user_id` (passenger), `driver_id`, `status`, `ride_type`, fare/distance/duration, `scheduled_at`, and a JSON `meta` blob (seats, extras, special cost, `source: 'dispatcher'`). Statuses seen across the app: `searching`, `assigned`, `upcoming`, `scheduled`, `cancelled`, plus completed states from the driver app.

- **Order creation** ([api/create_order.php](api/create_order.php)): finds-or-creates the passenger (Supabase Auth user + `passengers` row) by phone, parses flexible date/time ("today"/"tomorrow"/"now"/"later"), computes fare. Fare logic: prefer the frontend `fare_eur`; otherwise fall back to `calcFareFromPassengerFormula`, which **must stay in sync with the Flutter passenger app's `ride_selection.dart`** (base + per-km + per-min with day/night rates 08:00–20:00, then a per-service-type multiplier). A ride is marked `scheduled` only if explicitly flagged AND pickup is >40 min out; otherwise `assigned` (if a driver) or `searching`.
- **Live tracking**: during an active ride the driver's GPS lives in `rides.driver_lat/driver_lng` (read by [api/get_ride_location.php](api/get_ride_location.php)), **not** in `drivers.current_lat/lng` (which only updates while the driver is idle). Respect this distinction when touching map/tracking code.

### Top-level pages
- `home.php` — dashboard KPIs + driver/passenger tables (driven by `js/app.js`).
- `order.php` / `orderassigned.php` — create order / assigned-order views.
- `preorder.php` + `preorder/` — "Live Orders". `preorder/.htaccess` rewrites `preorder/{rideId}` → `preorder/index.php` (ride id is read from the path).
- `application_rides.php`, `corporate_rides.php` — app vs. corporate ride management.
- `map.php` — live map. `fleetRegistry.php`, `profile.php` — fleet + account.

### Frontend (no bundler)
Plain ES, loaded via `<script>` tags. `js/app.js` (dashboard tables + client-side search + pagination), `js/pagination.js` (`PaginationManager`), `js/status-badge.js`, `js/beep-monitor.js` (audible alert that loops while rides sit in `searching`, persisted across pages via `localStorage`; audio asset `assets/ride_alert.mpeg`). Data loads happen client-side: `fetch('api/...php')`, and any `401` redirects to `/`.

### Email — `lib/mail_helper.php`
PHPMailer over SMTP (`mail.powercabs.ie`). HTML templates in `templates/`. `sendRideAssignedEmail` is called from order creation; it deliberately skips placeholder `@temp.passenger` addresses.

## Conventions to follow
- New API endpoints: copy the existing pattern — `header('Content-Type: application/json')`, `session_start()`, `require_once '../auth/config.php'`, inline 401 guard, `new SupabaseDB(null, true)`, wrap work in try/catch, return the `{ success, data, error }` JSON shape, `error_log()` on failure.
- Pagination uses `?page=&limit=` query params; `SupabaseDB::fetchData` translates them to Supabase `Range` headers, and `getCount` supplies the total.
- File references use `.php` filenames directly as links (the sidebar/navbar match the active page by `basename($_SERVER['PHP_SELF'])`), so renaming a page means updating `modules/sidebar.php` and the `$page_titles` maps in `modules/navbar.php` and `auth/config.php`.
- When changing fare math, mirror the change in both `create_order.php` and the corporate-ride creation path, and keep parity with the passenger app formula.
