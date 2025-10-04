# BikeRental PHP API (XAMPP)

Minimal PHP + MySQL backend for the BikeRental Android app.

## Prerequisites
- XAMPP (Apache + MySQL)
- PHP 8.x
- Database: `bikerental`

## Directory
- Place this folder at `C:\xampp\htdocs\myproject\api`
- Android emulator base URL: `http://10.0.2.2/myproject/api/`
- Browser/Postman base URL: `http://localhost/myproject/api/`

## Configure Database
Edit `env.php`:
```php
<?php
const DB_HOST = 'localhost';
const DB_NAME = 'bikerental';
const DB_USER = 'root';
const DB_PASS = ''; // XAMPP default
// const DB_PORT = 3306; // optional if default
```

Create DB and tables (phpMyAdmin or MySQL console):
- Create database: `CREATE DATABASE bikerental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
- Import `schema.sql` into `bikerental`

## Files
- `index.php`        HTTP router
- `db.php`           PDO connection + JSON helper
- `env.php`          DB config
- `auth.php`         Login
- `bikes.php`        Bikes CRUD
- `bookings.php`     Bookings create/list/cancel
- `schema.sql`       Tables (roles, users, bikes, bookings, etc.)
- `ping_db.php`      DB connectivity test
- `phpinfo.php`      PHP info

## Health & Connectivity
- PHP info: `http://localhost/myproject/api/phpinfo.php`
- DB ping: `http://localhost/myproject/api/ping_db.php`

Expected DB ping JSON:
```json
{"ok":true,"message":"DB connection successful"}
```

## Endpoints

Base: `/myproject/api`

### Auth
- POST `/auth/login`
  - Body (JSON):
    ```json
    {"email":"admin@example.com","password":"admin123"}
    ```
  - Response:
    ```json
    {"ok":true,"token":"<hmac>", "user":{"id":1,"email":"...","role":"admin"}}
    ```

### Bikes
- GET `/bikes` (optional query: `owner_email`, `type`, `limit`)
- POST `/bikes`
  - Body (JSON):
    ```json
    {
      "name":"Pulsar 150",
      "type":"Sport",
      "price_hour":50,
      "price_day":400,
      "owner_email":"admin@demo.com",
      "registration_number":"TS09AB1234",
      "availability_status":"Inactive",
      "verification_status":"Pending",
      "location":"Campus"
    }
    ```
- PUT/PATCH `/bikes/{id}`
- DELETE `/bikes/{id}`

### Bookings
- GET `/bookings?bike_id={id}` (or by `user_email` if supported)
- POST `/bookings`
  - Body (JSON):
    ```json
    {
      "bike_id": 1,
      "user_email": "user1@demo.com",
      "start_time": "2025-10-03T10:00:00Z",
      "end_time": "2025-10-03T12:00:00Z",
      "status": "active"
    }
    ```
- PATCH `/bookings/{id}` (cancel)
  - Body (JSON):
    ```json
    {"status":"cancelled"}
    ```

Note: Some optional fields may be nullable; see table definitions in `schema.sql`.

## Sample cURL

Login:
```bash
curl -X POST http://localhost/myproject/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"admin@example.com\",\"password\":\"admin123\"}"
```

List bikes:
```bash
curl http://localhost/myproject/api/bikes
```

Create bike:
```bash
curl -X POST http://localhost/myproject/api/bikes ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"FZ\",\"type\":\"Street\",\"price_hour\":60,\"price_day\":480,\"owner_email\":\"admin@demo.com\",\"registration_number\":\"TS10CD5678\",\"availability_status\":\"Inactive\",\"verification_status\":\"Pending\",\"location\":\"Campus\"}"
```

Create booking:
```bash
curl -X POST http://localhost/myproject/api/bookings ^
  -H "Content-Type: application/json" ^
  -d "{\"bike_id\":1,\"user_email\":\"user1@demo.com\",\"start_time\":\"2025-10-03T10:00:00Z\",\"end_time\":\"2025-10-03T12:00:00Z\",\"status\":\"active\"}"
```

Cancel booking:
```bash
curl -X PATCH http://localhost/myproject/api/bookings/1 ^
  -H "Content-Type: application/json" ^
  -d "{\"status\":\"cancelled\"}"
```

## Android Integration Notes
- Emulator base URL must be `http://10.0.2.2/myproject/api/`
- Enable cleartext in `AndroidManifest.xml` and `res/xml/network_security_config.xml`
- Retrofit example:
  - GET `/bikes`, POST `/bookings`, POST `/auth/login`

## Troubleshooting
- 404 Not Found: Verify folder is under `C:\xampp\htdocs\myproject\api` and access via `/myproject/api/...`
- CLEARTEXT blocked: Allow cleartext in Android network security
- DB errors: Confirm `env.php` credentials and that `schema.sql` is imported
- PHP parse errors: Ensure files saved as UTF-8 without BOM; no PowerShell here-strings

## Security (Dev Only)
- Tokens are simple HMAC for demo purposes
- No HTTPS in dev; use reverse proxy or enable TLS for production
- No KYC per requirement
