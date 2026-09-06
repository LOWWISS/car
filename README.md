# Car Auction System

A full-stack car selling system with a real-time bidding/auction feature, built
with **native PHP (strict MVC)**, **Tailwind CSS**, **vanilla JavaScript
(Fetch API)**, and **MySQL (PDO)**. Designed as an Information Security course
project: every security control below is implemented and documented with inline
comments explaining the mechanism.

---

## 1. Tech Stack

| Layer       | Technology                                                        |
|-------------|-------------------------------------------------------------------|
| Backend     | PHP 8.3 (native, no framework), strict MVC                        |
| Frontend    | Tailwind CSS (Play CDN), vanilla JS + Fetch API, Poppins font     |
| Database    | MySQL 8 / MariaDB via PDO prepared statements                     |
| Mail        | PHPMailer (if installed) or PHP `mail()` fallback                 |
| Entry point | `public/index.php` (front controller + custom Router)             |

No Composer/Node build is required to run the app. The Tailwind Play CDN
delivers styling; `tailwind.config.js` is included for reference if you later
adopt the Tailwind CLI.

---

## 2. Folder Structure

```
car/
├── .env                    # DB creds, encryption key, rate limits (NEVER commit)
├── .env.example
├── .htaccess               # forwards / to /public
├── tailwind.config.js      # reference config (Poppins default font)
├── config/
│   └── config.php          # Config loader (parses .env)
├── app/
│   ├── Core/               # Router, Database, Auth, Session, Csrf,
│   │                       # RateLimiter, Middleware, SecurityHeaders,
│   │                       # Validator, Mailer, Logger, Encryption,
│   │                       # ErrorHandler, View, Request, Response,
│   │                       # BaseController, BaseModel
│   ├── Controllers/        # Home, Auth, Car, Bid, Watchlist,
│   │                       # Notification, Dashboard, Admin
│   ├── Models/             # User, Car, CarImage, Bid, Watchlist,
│   │                       # Notification, AuditLog
│   └── Views/              # layouts/ partials/ home/ auth/ cars/
│                           # dashboard/ admin/ errors/
├── public/                 # web root (only this is served)
│   ├── index.php           # front controller + route table
│   ├── .htaccess           # rewrites to index.php, security headers
│   ├── assets/css/app.css  # reusable component classes
│   ├── assets/js/app.js    # countdowns, AJAX bidding, toasts
│   └── uploads/            # car images (non-executable via .htaccess)
├── database/
│   └── schema.sql          # tables + seed data
└── storage/
    └── logs/               # audit.log, error.log
```

---

## 3. Installation

1. **Copy the project** into your web server's document root
   (e.g. `C:\wamp64\www\car`).
2. **Create the database** and tables:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   This creates the `car_auction` database, all tables, and three demo users
   (password: `password`):
   - `admin@car.local`  (admin)
   - `jane@car.local`   (seller)
   - `bob@car.local`    (buyer)
3. **Configure environment**:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` and set `DB_PASS`, `APP_URL`, and a real `ENCRYPTION_KEY`
   (generate one with):
   ```bash
   php -r "echo base64_encode(random_bytes(32));"
   ```
4. **Visit the app** at the URL matching `APP_URL`
   (e.g. `http://localhost/car`).

> WAMP note: the root `.htaccess` rewrites everything into `public/`, so
> `http://localhost/car` serves `public/index.php`. Ensure Apache's
> `mod_rewrite` is enabled.

---

## 4. Architecture

### Strict MVC separation
- **Controllers** (`app/Controllers`) receive the request, validate input,
  call models, and load views. No SQL here.
- **Models** (`app/Models`) own all DB access via PDO prepared statements.
  No HTML here.
- **Views** (`app/Views`) are PHP templates using Tailwind classes only.
  No SQL here; all dynamic output is escaped with `e()` (htmlspecialchars).

### Front controller + router
All requests flow through `public/index.php`, which autoloads classes, loads
config, starts the session, sends security headers, and dispatches to a
controller method via the `Router`. Clean URLs map to handlers like
`CarController@show` for `/cars/view/{id}`.

### Reusable UI
- `partials/car_card.php` is used on the home page, catalog, dashboard, and
  watchlist.
- `app/assets/css/app.css` defines `.btn-primary`, `.btn-outline`, `.card`,
  `.form-input`, `.form-label`, `.countdown`, `.toast`, `.skeleton` so view
  markup stays clean.

---

## 5. Functional Features

- **Roles**: Guest, Buyer/Bidder, Seller, Admin (RBAC enforced in middleware).
- **Auth**: register, login, logout, email verification, password reset.
- **Seller**: create/edit/delete listings with multiple photos, starting
  price, reserve price, Buy Now price, auction start/end datetime.
- **Catalog**: search, filter (make, body type, year, location, price range),
  sort (whitelisted columns), pagination.
- **Bidding**: place a bid (must beat current highest + 1% minimum increment),
  live countdown timers, AJAX bid submission with instant feedback, live bid
  history polling (5s), Buy Now option, auto-close expired auctions.
- **Notifications**: in-app bell with unread badge + email (outbid, won,
  listing approved/rejected).
- **Dashboards**: buyer (bids, won, watchlist), seller (listings, revenue),
  admin (analytics, approvals, users, audit logs).
- **Watchlist/favorites**: toggle via AJAX.

---

## 6. Security Features

Each control is implemented and commented inline. Below is a summary plus how
to **test** each one for the infosec review.

### 6.1 SQL Injection Prevention
- **Mechanism**: every query uses PDO prepared statements with bound
  parameters (`app/Core/BaseModel.php`, all models). Dynamic identifiers
  (sort columns) are whitelisted via `Validator::whitelist()` in
  `CarController::catalog()` before interpolation. `PDO::ATTR_EMULATE_PREPARES`
  is `false` to force real server-side prepared statements.
- **Test**: try `http://localhost/car/cars?q=' OR 1=1--` and
  `?sort=price; DROP TABLE users--`. The first is bound as a literal string
  (returns no rows); the second falls back to the default sort because
  `; DROP TABLE...` is not in the whitelist.

### 6.2 Password & Data Encryption
- **Mechanism**: passwords hashed with `password_hash()` (bcrypt, cost 12) and
  verified with `password_verify()` (`app/Models/User.php`). Sensitive PII
  (user phone) is encrypted at rest with AES-256-CBC + HMAC
  (`app/Core/Encryption.php`); the key lives in `.env`. HTTPS is enforced via
  `.htaccess` (uncomment the rewrite when deploying with TLS).
- **Test**: inspect the `users` table — `password_hash` is a bcrypt string,
  `phone` is base64 ciphertext. Decrypt by logging in and viewing your profile
  (the app decrypts on read).

### 6.3 Authentication & Session Security
- **Mechanism** (`app/Core/Session.php`, `Auth.php`): cookies are
  `HttpOnly`, `SameSite=Lax`, `Secure` over HTTPS; `session.use_strict_mode=1`;
  `session_regenerate_id(true)` on login (session fixation defense);
  inactivity timeout auto-logout; account lockout after
  `LOGIN_MAX_ATTEMPTS` failed attempts.
- **Test**: log in, then in DevTools → Application → Cookies confirm
  `HttpOnly` and `SameSite` on `CARAUCTION_SESS`. Try 5 wrong passwords —
  the account locks for 15 minutes (`locked_until` column).

### 6.4 Rate Limiting
- **Mechanism** (`app/Core/RateLimiter.php`): fixed-window counter in the
  `rate_limits` table keyed by `(action, ip, user_id)`. Throttles login (10/60s),
  register (5/5min), forgot-password (5/5min), bid submission
  (`BID_RATE_LIMIT`/`RATE_LIMIT_WINDOW_SECONDS`), admin actions (20/60s).
- **Test**: submit 11 login attempts within 60s — the 11th returns HTTP 429.
  Inspect the `rate_limits` table to see the counter.

### 6.5 CSRF Protection
- **Mechanism** (`app/Core/Csrf.php`): a per-session token is embedded in
  every form via `Csrf::field()` and validated on every state-changing POST
  via `Middleware::requireCsrf()` / `BaseController::guardPost()`. Comparison
  uses `hash_equals()` (timing-safe).
- **Test**: open a listing's bid form, delete the hidden `_csrf_token` field
  via DevTools, and submit — you get redirected with `?error=csrf`. Sending a
  POST from an external site/curl without the token fails.

### 6.6 XSS Prevention
- **Mechanism**: all user-generated output in Views is escaped with `e()`
  (htmlspecialchars, `ENT_QUOTES`, UTF-8). A strict
  `Content-Security-Policy` header restricts script sources to `self` + the
  Tailwind CDN + a per-request nonce for our inline config/scripts
  (`app/Core/SecurityHeaders.php`). Server-side input validation
  (`app/Core/Validator.php`) rejects malformed input.
- **Test**: register a user named `<script>alert(1)</script>` — the name is
  rendered as text, never executed. Check the CSP header in DevTools →
  Network → response headers.

### 6.7 File Upload Security
- **Mechanism** (`CarController::handleUploads()`): server-side MIME detection
  via `finfo`, extension whitelist (`jpg/jpeg/png/webp`), 5MB size cap, random
  rename (`bin2hex(random_bytes(16))`) to prevent path traversal/overwrite,
  stored in `public/uploads/` which has a `.htaccess` disabling PHP execution.
- **Test**: try uploading a `.php` file renamed to `.jpg` — `finfo` detects the
  real MIME and rejects it. Upload a real JPG — it's saved with a random name.
  Try accessing `public/uploads/evil.php` — Apache refuses to execute it.

### 6.8 Input Validation & Sanitization
- **Mechanism** (`app/Core/Validator.php`): every form is validated server-side
  (type, length, format, whitelist). Client-side HTML attributes (`required`,
  `minlength`) are UX only.
- **Test**: POST a registration with `password=abc` (under 8 chars) using curl
  bypassing the browser — the server rejects it.

### 6.9 Authorization / Access Control (RBAC + IDOR)
- **Mechanism** (`app/Core/Middleware.php`): `requireAuth()` blocks guests;
  `requireRole('seller','admin')` enforces RBAC on listing CRUD and admin
  pages; `requireOwnerOrAdmin()` verifies `seller_id === Auth::id()` before
  edit/delete (IDOR prevention). Notifications are scoped by `user_id`.
- **Test**: log in as `bob@car.local`, then visit `/cars/edit/1` (Jane's
  listing) — you get HTTP 403. Try `POST /cars/delete/1` — same. Visit
  `/admin` as a buyer — 403.

### 6.10 Security Headers
- **Mechanism** (`app/Core/SecurityHeaders.php`, `public/.htaccess`):
  `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`,
  `Strict-Transport-Security` (over HTTPS), `Referrer-Policy`,
  `Permissions-Policy`, and a strict `Content-Security-Policy`.
- **Test**: `curl -I http://localhost/car/` and inspect the response headers.

### 6.11 Logging & Auditing
- **Mechanism** (`app/Core/Logger.php`, `app/Models/AuditLog.php`): auth
  attempts (success/failure), bid placements, listing CRUD, admin actions,
  and errors are written to `storage/logs/audit.log` (file) and the
  `audit_logs` table. Sensitive fields (passwords, tokens) are stripped.
- **Test**: log in, place a bid, then view `/admin/logs` — each action
  appears with user, IP, and timestamp.

### 6.12 Error Handling
- **Mechanism** (`app/Core/ErrorHandler.php`): centralized
  `set_exception_handler` / `set_error_handler` / `register_shutdown_function`.
  In production (`APP_DEBUG=false` in `.env`) detailed traces are suppressed
  and a friendly 500 page is shown; full details go to
  `storage/logs/error.log`. Custom 403/404/500 views exist.
- **Test**: set `APP_DEBUG=false`, then trigger an error (e.g. stop the DB)
  — the user sees a generic message, the log file has the full trace.

---

## 7. Routes

| Method | Path                              | Handler                       | Auth          |
|--------|-----------------------------------|-------------------------------|---------------|
| GET    | `/`                               | HomeController@index          | public        |
| GET    | `/cars`                           | CarController@catalog         | public        |
| GET    | `/cars/view/{id}`                 | CarController@show            | public        |
| GET    | `/cars/create`                    | CarController@createForm      | seller/admin  |
| POST   | `/cars/create`                    | CarController@create          | seller/admin  |
| GET    | `/cars/edit/{id}`                 | CarController@editForm        | owner/admin   |
| POST   | `/cars/edit/{id}`                 | CarController@update          | owner/admin   |
| POST   | `/cars/delete/{id}`               | CarController@delete          | owner/admin   |
| POST   | `/cars/delete-image/{imageId}`    | CarController@deleteImage     | owner/admin   |
| POST   | `/bids/place/{carId}`             | BidController@place           | auth          |
| POST   | `/bids/ajax/{carId}`              | BidController@placeAjax       | auth          |
| POST   | `/bids/buy-now/{carId}`           | BidController@buyNow          | auth          |
| GET    | `/bids/history/{carId}`           | BidController@history         | public        |
| POST   | `/watchlist/toggle`               | WatchlistController@toggle    | auth          |
| GET    | `/watchlist`                      | WatchlistController@list      | auth          |
| GET    | `/notifications`                  | NotificationController@index  | auth          |
| POST   | `/notifications/mark-read`        | NotificationController@markRead | auth        |
| GET    | `/notifications/unread-count`     | NotificationController@unreadCount | auth     |
| GET    | `/dashboard`                      | DashboardController@index     | auth          |
| GET    | `/admin`                          | AdminController@index         | admin         |
| POST   | `/admin/approve/{id}`             | AdminController@approve       | admin         |
| POST   | `/admin/reject/{id}`              | AdminController@reject        | admin         |
| GET    | `/admin/users`                    | AdminController@users         | admin         |
| POST   | `/admin/users/role/{id}`          | AdminController@updateRole    | admin         |
| POST   | `/admin/users/toggle/{id}`        | AdminController@toggleUser    | admin         |
| GET    | `/admin/logs`                     | AdminController@logs          | admin         |
| GET    | `/login` `/register`              | AuthController@show*          | guest         |
| POST   | `/login` `/register`              | AuthController@*              | guest         |
| GET    | `/verify-email/{token}`           | AuthController@verifyEmail    | public        |
| GET    | `/logout`                         | AuthController@logout         | auth (CSRF)   |
| GET    | `/forgot-password`                | AuthController@showForgot     | public        |
| POST   | `/forgot-password`                | AuthController@sendReset      | public        |
| GET    | `/reset-password/{token}`         | AuthController@showReset      | public        |
| POST   | `/reset-password`                 | AuthController@resetPassword  | public        |

---

## 8. Demo Accounts

All seeded accounts use the password **`password`**:

| Email             | Role    | Use for                       |
|-------------------|---------|-------------------------------|
| admin@car.local   | admin   | approvals, users, audit logs  |
| jane@car.local    | seller  | create/edit listings          |
| bob@car.local     | buyer   | place bids, watchlist         |

---

## 9. Notes & Caveats

- **Tailwind Play CDN** is used for zero-build delivery. For production,
  switch to the Tailwind CLI build (see `tailwind.config.js`) for smaller CSS
  and no runtime JIT.
- **Email** uses `mail()` by default. For reliable delivery, install
  PHPMailer via Composer and set SMTP creds in `.env`.
- **Auction closing** runs opportunistically on each homepage load
  (`Car::closeExpired()`). For production, wire a cron job to hit `/`
  periodically or add a dedicated CLI command.
- **Encryption key**: the default `ENCRYPTION_KEY` in `.env` is a placeholder
  for local dev only. **Generate and set a real one** before any real use.
