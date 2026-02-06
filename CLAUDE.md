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

## Project Structure

```
/
├── connect.php              # Database connection (MySQLi)
├── functions.php            # Core business logic (handleStock, updateStock, logError)
├── header.php / footer.php  # Shared HTML layout (user-facing)
├── admin_header.php / admin_footer.php  # Admin layout
├── navbar.php               # Sidebar navigation component
├── style.css                # Global styles
├── .htaccess                # IP-based access restriction
│
├── index.php                # Main stock management page (restock/destock)
├── login.php                # User login
├── registration.php         # User registration
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
├── barcode_functions.php    # Barcode CRUD functions
├── barcodeprint.php         # PDF barcode label generation
│
├── count.php                # Stock history charts (Chart.js)
├── stock_history.php        # Detailed stock history with date filters
├── export_stock_history.php # JSON/CSV export of history
├── download_csv.php         # CSV export from session data
│
├── category_functions.php   # Category CRUD functions
├── contact_functions.php    # Contact CRUD functions
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
├── src/                     # Frontend libraries (Bootstrap, jQuery, jQuery-UI)
├── system/                  # Backend API handler and logging
│   ├── api_handler.php      # SQL query execution endpoint
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

**Connection**: Configured in `connect.php` using MySQLi with hardcoded credentials.

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

Both go through `handleStock()` / `handleStock2()` -> `updateStock()` in `functions.php`.

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
- **Function files** group related CRUD operations (`*_functions.php`)
- **Layout includes** via `require`/`include` for header, footer, navbar

### Session Management
- `session_start()` at top of protected pages
- Auth check: `if (!isset($_SESSION['username'])) { header("Location: login.php"); }`
- Passwords hashed with `password_hash()` / `password_verify()`

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

## Build & Development

### No Build System
This project has no build tools, bundlers, or package managers. PHP files are served directly.

### No Testing Framework
There are no automated tests (no PHPUnit, no Jest, no test directories).

### No Linting/Formatting
No configured linting or formatting tools.

### No CI/CD
No CI/CD pipeline configured.

### Running Locally
The application requires:
1. A PHP-capable web server (Apache with `.htaccess` support)
2. MySQL database with the schema tables created
3. PHP extensions: `mysqli`, `gd` (for QR code generation)

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
- **No `.env`**: Database credentials are hardcoded in `connect.php`. Do not commit real credentials.
- **Global state**: The `$con` database connection is accessed via `global $con` in functions.
- **File organization**: All PHP pages are in the root directory. There is no MVC framework.
- **Security caution**: `system/api_handler.php` accepts arbitrary SQL queries. Avoid extending this pattern.
- **Prepared statements**: Always use MySQLi prepared statements with `bind_param()` for new queries.
- **No composer autoload**: Libraries are included manually with `require`/`require_once`.
- **Audio files**: Stock operations play audio feedback (`.wav` files in root). These are referenced in `functions.php`.
- **Third-party libraries** (`src/`, `phpqrcode/`, `phpbarcode/`, `tcpdf/`): Avoid modifying vendored code.
