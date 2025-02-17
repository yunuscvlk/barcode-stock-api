# Barcode Sales and Stock Tracking API

This project is a REST API developed for barcode-based product sales and stock tracking.

## Features

- Product management (adding, listing, searching)
- Product lookup by barcode
- Stock tracking and updating
- Sales operations

## Requirements

- PHP 8.x
- MySQL 5.7+
- Apache HTTP Server
- Composer
- PDO PHP Extension
- MySQL PHP Extension

## Installation

1. Clone the project:
```bash
git clone https://github.com/yunuscvlk/barcode-stock-api.git
cd barcode-stock-api
```

2. Install Composer dependencies:
```bash
composer install
```

3. Create the `.env` file:
```bash
cp .env.example .env
```

4. Edit the `.env` file:
```env
DB_HOST=localhost
DB_NAME=barcode_stock_api
DB_USER=root
DB_PASS=<your_password>
```

5. Create the database and tables:
```bash
mysql -u root -p < static/Database.sql
```

6. Verify that the application is running by testing the API from the command line:
```bash
php test/api_test.php
```