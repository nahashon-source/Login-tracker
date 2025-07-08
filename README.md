# 📊 Laravel Login Tracker

A Laravel-based application for tracking user sign-ins and activity across multiple systems. It allows administrators to monitor when users log in, view sign-in history, identify inactive users, and manage user data through an importable and editable interface.

---

## 🚀 Features

- ✅ Track user login activity for the last 30, 60, or 90 days
- ✅ View detailed sign-in records per user
- ✅ Identify users who haven't logged in during a selected period
- ✅ Import users and sign-ins via CSV files (using maatwebsite Excel)
- ✅ Manage users (create, view, delete)
- ✅ Filter activity by date range, system, and search queriesphp
- ✅ Generate user activity reports
- ✅ Responsive Bootstrap 5 UI

---

## 📦 Tech Stack

- **PHP 8.x**
- **Laravel 10.x**
- **MySQL**
- **Bootstrap 5**
- **Maatwebsite Excel** (for CSV imports)
- **Carbon** (for date manipulation)

---

## 📑 Requirements

- PHP 8.1+
- Composer
- MySQL / MariaDB
- Node.js & NPM (for frontend assets if needed)
- Laravel 10.x

---

## ⚙️ Setup Instructions

1. **Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/login-tracker.git
   cd login-tracker
2. **Install dependencies:

    composer install


3. ** Copy .env and set your database credentials:


4. Generate application key:

   php artisan key: generate

5. Run migrations
  
 php artisan migrate

6. Serve the application
    
  php artisan serve



   
