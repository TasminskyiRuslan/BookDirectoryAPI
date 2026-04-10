# Book Directory API 📚

**Book Directory API** is a high-performance RESTful service built with Laravel 12 for managing a comprehensive library of books and authors. It features advanced filtering, caching strategies, and a robust Role-Based Access Control (RBAC) system.

-----

## 🛠 Tech Stack

* **Framework:** Laravel 12
* **Language:** PHP 8.4+
* **Database:** MySQL 8.0+ (Relational data)
* **Cache & Queue:** Redis (Cache tags & background processing)
* **API Docs:** L5-Swagger (OpenAPI 3.0)
* **Key Packages:**
    * `spatie/laravel-data`: For Type-safe DTOs and Resource transformations.
    * `spatie/laravel-query-builder`: For declarative filtering and sorting.
    * `spatie/laravel-permission`: For granular RBAC management.
    * `laravel/sanctum`: For secure API token authentication.

-----

## 🐳 Prerequisites

Ensure you have installed:

* Docker & Docker Compose
* Git

-----

## 🚀 Installation & Setup

### 1\. Clone the repository

```bash
git clone git@github.com:TasminskyiRuslan/BookDirectoryAPI.git
cd BookDirectoryAPI
```

### 2\. Configure environment

```bash
cp .env.example .env

# Sync your local user ID with Docker to avoid permission issues
echo "UID=$(id -u)" >> .env
echo "GID=$(id -g)" >> .env
```

### 3\. Start containers

```bash
docker compose up -d --build
```

### 4\. Install dependencies

```bash
docker compose exec app composer install
```

### 5\. Setup application

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan db:seed --class=DevSeeder # Optional: Seed demo data
docker compose exec app php artisan storage:link
```

### 5\. Generate Documentation

```bash
docker compose exec app php artisan l5-swagger:generate
```

-----

## 📚 API Documentation

Once the project is running, you can access the interactive Swagger UI to explore all endpoints, request bodies, and response schemas.

👉 **[View API Documentation](http://localhost:8080/api/documentation)**

-----

## ✨ Key Features

### 🔐 Security & RBAC

The system implements strict **Role-Based Access Control** via `spatie/laravel-permission`:

* **Guest:** Public read-only access.
* **Viewer:** Authenticated read-only access.
* **Editor:** Can manage content (Books/Authors) but not users.
* **Admin:** Full administrative control over content and partial over users.
* **SuperAdmin:** Full administrative control over content and users.

### ⚡ Performance & Caching

* **Redis-Powered:** Full response caching for index endpoints.
* **Tagging System:** Uses Cache Tags (`books`, `authors`) for granular invalidation.
* **Smart Invalidation:** Any mutation (Create/Update/Delete) automatically flushes the relevant tags via Model Observers and changes in relationships (e.g., attaching an author to a book) are captured via Custom Events and Listeners, ensuring that any update to the many-to-many links flushes both books and authors cache tags instantly.

### 🖼 Advanced Image Handling

* **UUID Renaming:** Uses `ramsey/uuid` to ensure unique, collision-free filenames.
* **Orphan Prevention:** Automatically deletes old physical files from storage when an image is updated or a model is deleted.

-----

## 📊 Database Design

### Core Entities

* **User:** Registered users.
* **Author:** Created authors.
* **Book:** Created books.

### Relationships

* **Author** `belongsToMany` **Book**
* **Book** `belongsToMany` **Author**

-----

## 📂 Project Structure

```text
app/
├── Actions/            # Business logic classes
├── Data/               # Spatie Data objects (DTOs + Validation Rules)
├── Http/
│   └── Controllers/    # Handles API requests and returns responses
├── Models/             # Eloquent models
├── Observers/          # Automated cache invalidation triggers
└── Swagger/            # Virtual schemas for OpenAPI documentation

database/
├── migrations/         # Table structures
└── seeders/            # Initial RBAC setup and demo data
```

-----

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/license/MIT).
