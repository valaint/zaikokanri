# CLAUDE.md - AI Assistant Guide for Zaikokanri

## Project Overview

**Zaikokanri** (在庫管理) is a Japanese-language web-based inventory management system. It tracks stock levels for articles/items, supports barcode scanning for quick stock operations, generates PDF reports with barcodes/QR codes, and provides analytics and stock history.

## Tech Stack

- **Backend**: PHP (procedural, not OOP) with MySQLi (prepared statements)
- **Frontend**: HTML, CSS, JavaScript with Bootstrap 5, jQuery 3, jQuery-UI
- **Database**: MySQL
- **PDF Generation**: TCPDF v6.6.2
- **Barcode/QR**: phpbarcode, phpqrcode libraries
- **Charts**: Chart.js (loaded via CDN in `count.php`)
- **Package Manager**: Composer (for dev tools and phpdotenv)

## Project Structure

```
/
├── connect.php              # Database connection (MySQLi, uses .env)
├── functions.php            # Core business logic (handleStock, updateStock, logError)
├── csrf.php                 # CSRF token generation and validation
├── health.php               # Health check endpoint (JSON)
├── header.php / footer.php  # Shared HTML layout (user-facing, includes CSP headers)
├── admin_header.php / admin_footer.php  # Admin layout (includes CSP headers)
├── navbar.php               # Sidebar navigation component
├── style.css                # Global styles (including layout extracted from headers)
├── .htaccess                # IP-based access restriction
│
├── index.php                # Main stock management page (restock/destock)
├── login.php                # User login (with CSRF)
├── registration.php         # User registration (with CSRF, prepared statements)
├── logout.php               # Session destroy
│
├── admin.php                # Admin dashboard (KPIs, alerts, logs)
├── admin_stock.php          # Article CRUD with drag-drop ordering
├── admin_barcodelist.php    # Barcode-to-article mapping management
├── admin_category.php       # Category CRUD
├── admin_contact.php        # Contact/responsible party CRUD
├── admin_stocktaking.php    # Physical inventory count
├── admin_restore.php        # Restore stock taking data
│
├── barcode.html             # Barcode scanner interface
├── barcode.php              # Barcode endpoint
├── barcode.js               # Barcode scanner input handler
├── api_barcode.php          # JSON API for barcode scanning
├── barcode_functions.php    # Barcode CRUD endpoint (AJAX)
├── barcodeprint.php         # PDF barcode label generation
│
├── count.php                # Stock history charts (Chart.js)
├── stock_history.php        # Detailed stock history with date filters
├── export_stock_history.php # JSON/CSV export of history
├── download_csv.php         # CSV export from session data
│
├── category_functions.php   # Category CRUD endpoint (AJAX)
├── contact_functions.php    # Contact CRUD endpoint (AJAX, has function definitions)
├── delete_article.php       # Article deletion endpoint
├── update_article.php       # Bulk article update endpoint
├── update_order.php         # AJAX article display order update
├── print.php / print2.php   # PDF report generation (TCPDF)
├── restore.php              # Stock restoration logic
├── stocktaking.php          # Stock taking page
│
├── filter.js                # Category-based table filtering
├── hiddenclick.js           # Hidden submit form handler
├── popper.js                # Tooltip positioning library
│
├── composer.json             # Composer config (PHPUnit, PHP_CodeSniffer, phpdotenv)
├── Makefile                  # Convenience commands (make test, make lint, etc.)
├── phpunit.xml               # PHPUnit configuration
├── phpcs.xml                 # PHP_CodeSniffer rules (PSR-12 base)
├── .editorconfig             # Editor coding style consistency
├── .gitignore                # Git ignore rules
├── .env.example              # Environment variable template
├── .github/workflows/ci.yml  # GitHub Actions CI pipeline
│
├── tests/                   # PHPUnit test suite
│   ├── bootstrap.php        # Test bootstrap (DB setup, function loading)
│   ├── schema.sql           # Test database schema
│   └── UpdateStockTest.php  # Tests for core stock operations
│
├── src/                     # Frontend libraries (Bootstrap, jQuery, jQuery-UI)
├── system/                  # Backend API handler and logging
│   ├── api_handler.php      # Read-only SQL query endpoint (SELECT only)
│   ├── log_functions.php    # API request/response logging with UUID
│   └── index.php            # System router
├── phpqrcode/               # QR code generation library
├── phpbarcode/              # Barcode generation library
├── tcpdf/                   # TCPDF PDF library (v6.6.2)
├── qrcodes/                 # Generated QR code images
│
├── *.wav                    # Audio feedback files (入庫しました, 出庫しました, etc.)
└── response_log.txt         # API response log file
```

## Database

**Connection**: Configured in `connect.php` using MySQLi. Credentials are loaded from `.env` (via phpdotenv) with hardcoded fallback defaults.

### Key Tables

| Table | Purpose |
|-------|---------|
| `article_info` | Items with stock levels, thresholds, category, display order |
| `history` | Stock transaction audit log (article_id, type, original/updated values, timestamp, from_barcode) |
| `barcode_list` | Barcode-to-article mappings (supports multi-item barcodes, destock_count, is_prompt) |
| `category` | Item categories (category_id, category_name) |
| `contact` | Responsible parties (contact_id, name, email) |
| `users` | User accounts (username, password hash) |
| `error_log` | Application error log (error_message, query, timestamp) |
| `api_requests` | API call tracking (uuid, method, url, headers, body, timestamp) |
| `api_responses` | API response tracking (request_uuid, status_code, headers, body, timestamp) |
| `api_exceptions` | API exception log (request_uuid, exception_message, trace, timestamp) |
| `stock_log` | Stock taking history (article_id, original_stock, updated_stock, date) |
| `ArticleContactView` | Database view joining articles with contacts (used in barcode printing) |

### Stock Operations

Two types of stock operations, tracked in `history`:
- **入庫 (restock)**: Increases stock
- **出庫 (destock)**: Decreases stock

Both go through `handleStock()` -> `updateStock()` in `functions.php`.

## Security

### CSRF Protection
Forms in `login.php`, `registration.php` include CSRF tokens via `csrf.php`. Use `<?= csrfField() ?>` in forms and `validateCsrfToken()` on POST handlers.

### Content-Security-Policy
`header.php` and `admin_header.php` send CSP, X-Content-Type-Options, and X-Frame-Options headers.

### API Handler
`system/api_handler.php` is restricted to SELECT queries only, with blocked patterns for dangerous SQL keywords (SLEEP, BENCHMARK, INTO OUTFILE, etc.).

### Prepared Statements
All database queries use MySQLi prepared statements with `bind_param()`.

## Code Conventions

### Naming
- **PHP functions**: camelCase (`handleStock`, `updateStock`, `logError`)
- **Database columns**: snake_case (`article_id`, `contact_id`, `category_name`)
- **PHP files**: snake_case (`admin_stock.php`, `barcode_functions.php`)
- **UI labels**: Japanese (在庫管理, 入庫, 出庫, バーコード, etc.)

### Architecture Patterns
- **Procedural PHP** with global `$con` database connection
- **Prepared statements** with `bind_param()` for all database queries
- **Server-side rendering** - PHP generates HTML directly
- **AJAX** via jQuery `$.post()` for dynamic operations (e.g., `update_order.php`)
- **Function files** (`*_functions.php`) are AJAX endpoints that use shared `logError()` from `functions.php`
- **Layout includes** via `require`/`include` for header, footer, navbar

### Session Management
- `session_start()` at top of protected pages
- Auth check: `if (!isset($_SESSION['username'])) { header("Location: login.php"); }`
- Passwords hashed with `password_hash()` / `password_verify()`
- CSRF tokens validated on form submissions via `csrf.php`

### Error Handling
- Database errors logged to `error_log` table via `logError()` in `functions.php`
- API logging in `system/log_functions.php` with UUID-based request/response tracking
- File-based fallback logging (`request_log.txt`, `response_log.txt`, `exception_log.txt`)

### Frontend Patterns
- Bootstrap 5 grid layout with sidebar navigation
- jQuery for DOM manipulation and AJAX calls
- jQuery-UI for drag-and-drop sortable tables (article ordering in `admin_stock.php`)
- Chart.js for stock history visualization
- Audio feedback on stock operations (`.wav` files)
- All shared layout styles in `style.css` (no inline `<style>` blocks in headers)

## Build & Development

### Prerequisites
- PHP >= 8.0 with extensions: `mysqli`, `gd`
- MySQL database
- Composer
- Apache web server with `.htaccess` support

### Setup

```bash
# Full setup (install deps + create .env)
make setup

# Or manually:
composer install
cp .env.example .env
# Edit .env with your database credentials
```

### Makefile Commands

| Command | Description |
|---------|-------------|
| `make setup` | Install dependencies + create `.env` |
| `make install` | Install Composer dependencies |
| `make lint` | Run PHP_CodeSniffer |
| `make lint-fix` | Auto-fix linting issues |
| `make test` | Run PHPUnit tests |
| `make ci` | Run full CI pipeline (lint + test) |

### Linting (PHP_CodeSniffer)

```bash
composer lint       # Check for code style issues (PSR-12 base)
composer lint:fix   # Auto-fix fixable issues
```

Configuration: `phpcs.xml` (PSR-12 with relaxed rules for procedural PHP). Third-party libraries are excluded.

### Testing (PHPUnit)

```bash
composer test
```

Tests require a MySQL test database. Configure credentials in `phpunit.xml` or via environment variables. Load the schema with:

```bash
mysql -u root -p zaikokanri_test < tests/schema.sql
```

Test files live in `tests/`. Current coverage:
- `UpdateStockTest.php` - Core stock operations (restock, destock, history logging, barcode flag, batch operations)

### CI/CD (GitHub Actions)

The CI pipeline (`.github/workflows/ci.yml`) runs on pushes and PRs to `main`:
- **Lint job**: Installs dependencies, runs `composer lint`
- **Test job**: Spins up MySQL 8.0 service, loads schema, runs `composer test`

### Health Check

`health.php` returns JSON with application and database status:
```bash
curl http://localhost/health.php
```

### Environment Variables

Database credentials are configured via `.env` file (loaded by phpdotenv in `connect.php`):

| Variable | Description | Default fallback |
|----------|-------------|-----------------|
| `DB_HOST` | MySQL host | `localhost` |
| `DB_USER` | MySQL username | `eeismzak` |
| `DB_PASSWORD` | MySQL password | `zaikokanrimysql` |
| `DB_NAME` | MySQL database name | `eeismzak` |

Copy `.env.example` to `.env` and fill in your values. The `.env` file is gitignored.

### Access Control
`.htaccess` restricts access by IP (`133.1.0.0/16`).

## Key Navigation (User-Facing Pages)

The sidebar (`navbar.php`) links to:
1. **在庫管理** (`index.php`) - Main stock management
2. **バーコード** (`barcode.html`) - Barcode scanner
3. **バーコードリストの表** (`barcodeprint.php`) - Barcode label printing
4. **物品情報** (`item.php`) - Item information
5. **集計** (`count.php`) - Analytics/charts
6. **在庫管理委員用** (`admin.php`) - Admin panel

## Important Notes for AI Assistants

- **Language**: UI text and database values are in Japanese. Preserve Japanese strings when editing.
- **Environment**: Database credentials come from `.env` (with hardcoded fallbacks in `connect.php`). Never commit `.env` files.
- **Global state**: The `$con` database connection is accessed via `global $con` in functions.
- **File organization**: All PHP pages are in the root directory. There is no MVC framework.
- **Security**: Use prepared statements, CSRF tokens on forms, and never extend `system/api_handler.php` beyond SELECT.
- **Testing**: Add tests to `tests/` for new business logic. Run `composer test` to verify.
- **Linting**: Run `composer lint` before committing. Fix issues with `composer lint:fix`.
- **CSRF**: Include `<?= csrfField() ?>` in new forms and call `validateCsrfToken()` in POST handlers.
- **handleStock()**: The consolidated function supports both UI (with audio) and API (without audio) use via the `$play_audio` parameter.
- **Audio files**: Stock operations play audio feedback (`.wav` files in root). These are referenced in `functions.php`.
- **Third-party libraries** (`src/`, `phpqrcode/`, `phpbarcode/`, `tcpdf/`): Avoid modifying vendored code.
