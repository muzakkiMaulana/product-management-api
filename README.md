# Product Management API

REST API untuk product management menggunakan Laravel.

## Run with Docker

```bash
cp .env.example .env
php scripts/setup.php
docker compose up -d --build
docker compose exec app php artisan migrate
```

Application:

```text
http://localhost:8000
```

Swagger Documentation:

```text
http://localhost:8000/docs/
```

## Run Locally

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di `.env`, lalu jalankan:

```bash
php artisan migrate
php artisan serve
```

Application:

```text
http://127.0.0.1:8000
```

Swagger Documentation:

```text
http://127.0.0.1:8000/docs/
```

## Main Endpoints

```text
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/refresh

GET    /api/products
GET    /api/products/{id}
POST   /api/products
PUT    /api/products/{id}
DELETE /api/products/{id}
```

Endpoint create, update, dan delete membutuhkan Bearer Token.