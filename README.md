# Grocery POS System

A web-based Point of Sale (POS) system designed for grocery store management. This system helps manage products, inventory, sales transactions, barcode scanning, and bulk product uploads efficiently.

## About the Project

The Grocery POS System is developed to simplify grocery store operations by providing an organized way to manage products, monitor inventory, process sales transactions, and speed up checkout through barcode scanning.

This system also includes a bulk upload feature that allows users to add multiple products efficiently instead of entering products manually one by one.

## Features

## Product Management
- Add new products
- Update product information
- Delete products
- View product lists
- Manage product prices and details

## Barcode Scanner
- Built-in barcode scanner functionality
- Quickly search and identify products using barcode
- Faster checkout process
- Reduces manual product selection errors

## Bulk Upload Products
- Upload multiple products at once
- Reduce manual product entry
- Faster product registration process
- Validate product information before saving

## Inventory Management
- Monitor product stocks
- Manage available quantities
- Keep inventory records updated

## Sales Management
- Process customer transactions
- Automatically retrieve product information using barcode scanning
- Record purchased products
- Track sales transactions

# Technologies Used

- Laravel
- PHP
- MySQL
- Blade Template Engine
- Bootstrap
- JavaScript

# Installation Guide

## 1. Clone the Repository

```bash
git clone https://github.com/Minthowltwopointzero/grocery-pos.git
```

## 2. Go to Project Directory

```bash
cd grocery-pos
```

## 3. Install Dependencies

Install PHP dependencies:

```bash
composer install
```

Install frontend dependencies:

```bash
npm install
```

## 4. Setup Environment File

Create a copy of the environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

## 5. Database Configuration

Configure your database connection inside the `.env` file.

Example:

```env
DB_DATABASE=grocery_pos
DB_USERNAME=root
DB_PASSWORD=
```

## 6. Run Database Migration

```bash
php artisan migrate
```

## 7. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

## 8. Run the Application

Start Laravel server:

```bash
php artisan serve
```

Open the application:

```bash
http://127.0.0.1:8000
```

# Latest Updates

## Bulk Upload Feature Added

Added a bulk upload functionality for products.

Changes made:

- Added bulk upload page
- Added product upload route
- Updated product controller logic
- Improved product management workflow

Updated files:

```bash
app/Http/Controllers/ProductController.php
routes/web.php
resources/views/products/index.blade.php
resources/views/products/bulk-upload.blade.php
```

# Project Structure

```bash
grocery-pos/
│
├── app/
│   └── Http/
│       └── Controllers/
│           └── ProductController.php
│
├── resources/
│   └── views/
│       └── products/
│           ├── index.blade.php
│           └── bulk-upload.blade.php
│
├── routes/
│   └── web.php
│
├── database/
│
└── README.md
```

# Developer

Developed by:

**Minthowltwopointzero**

# License
This project is developed and maintained as a Point of Sale system solution.