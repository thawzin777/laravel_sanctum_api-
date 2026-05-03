# 🔐 Laravel Sanctum API - Inventory & Order System

This is a Laravel REST API project using Laravel Sanctum for authentication.
It provides secure endpoints for managing users, products, and orders.

---

## 🚀 Features

* 🔐 Authentication using Laravel Sanctum (Token-based)
* 👤 User Login & Logout
* 📦 Product Management (CRUD)
* 🛒 Order Management
* 🔒 Protected API Routes
* 🌐 RESTful API Structure

---

## 🛠️ Tech Stack

* Laravel (Backend Framework)
* Laravel Sanctum (Authentication)
* MySQL (Database)
* REST API
* Eloquent ORM

---

## 📦 Installation

Clone the repository:

```bash
git clone https://github.com/thawzin777/laravel_sanctum_api.git
```

Go to project folder:

```bash id="6mtg6y"
cd laravel_sanctum_api
```

Install dependencies:

```bash id="g8y7y1"
composer install
```

---

## ⚙️ Environment Setup

Copy `.env` file:

```bash id="9v7vyo"
cp .env.example .env
```

Generate app key:

```bash id="b2d3nx"
php artisan key:generate
```

---

## 🗄️ Database Setup

Update `.env` with your database config:

```env id="3t9q1p"
DB_DATABASE=your_db
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash id="2z3l8x"
php artisan migrate
```

---

## 🔑 Sanctum Setup

Install Sanctum (if not installed):

```bash id="m8y2kz"
php artisan install:api
```

👉 Sanctum allows issuing API tokens and securing routes.

---

## ▶️ Run Server

```bash id="k3v8d1"
php artisan serve
```

Server will run at:
http://localhost:8000

---

## 🔌 API Endpoints (Example)

| Method | Endpoint      | Description      |
| ------ | ------------- | ---------------- |
| POST   | /api/login    | User login       |
| POST   | /api/logout   | User logout      |
| GET    | /api/products | Get all products |
| POST   | /api/orders   | Create order     |

---

## 🔐 Authentication

Use Bearer Token in headers:

```id="xk8n2d"
Authorization: Bearer YOUR_TOKEN
```

---

## 📸 Screenshots

(Add Postman or API response screenshots here)

---

## 🎥 Demo Video

(Add your demo video link here)

Example:
https://youtu.be/your-demo-link

---

## 🧪 API Testing

You can test API using:

* Postman
* Thunder Client (VS Code Extension)

---

## 📁 Project Structure (Simplified)

```id="y3n8kp"
app/
 ├── Models/
 ├── Http/Controllers/
 ├── Http/Middleware/
routes/
 ├── api.php
database/
 ├── migrations/
```

---

## 👨‍💻 Author

* Thaw Zin

---

## 📌 Notes

* Make sure database is running
* Do not commit `.env`
* Use correct API base URL in frontend

---

## 🌐 Frontend Repo

Frontend (Vue):
https://github.com/thawzin777/frontend-vue

---

## 📄 License

This project is for learning and portfolio purposes.
