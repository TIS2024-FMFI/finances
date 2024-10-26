

# Finances
System for keeping track of departmental expenses and overview of situation on departmental account

## Overview
This project is built using:
- PHP 8.1.12
- Composer 6.3,
- MySQL database

The environment is configured with XAMPP 8.2.12, which includes phpMyAdmin, PHP, and MySQL, to simplify the setup and local development.
## Requirements

- **PHP**: 8.1.12 or higher
- **Composer**: 6.3
- **XAMPP**: (Optional) 8.2.12 (includes phpMyAdmin, PHP, and MySQL)

## Setup Instructions

### Step 1: Install Dependencies
1. Install PHP, MySQL, and phpMyAdmin using XAMPP:
    - [Download XAMPP](https://www.apachefriends.org/index.html) and follow the installation instructions.
2. Set up free ports:
    - Default: Apache (80) and MySQL (3306)
    - If conflicts arise, adjust ports:
        - **Apache**: `8080`
        - **MySQL**: `3307`
3. Verify phpMyAdmin is working by visiting `http://localhost/phpmyadmin`.

### Step 2: Clone the Repository
Clone the repository using the following command:
```bash
git clone https://github.com/TIS2024-FMFI/finances.git
```

### Step 3: Install Composer Packages
Run the following command to install the necessary Composer packages:
```bash
composer install
```
Note: If you encounter issues, try:
```bash
composer update --ignore-platform-req=ext-gd --ignore-platform-req=ext-fileinfo -W
```

### Step 4: Configure Environment Variables
Copy the .env.example file to create a new .env file:
```bash
cp .env.example .env
```
Generate the application key
```bash
php artisan key:generate
```

### Step 5: Run Migrations
Run the migrations to set up the database structure:
```bash
php artisan migrate
```

### Step 6: Seed the Database (Optional)
If you want to seed the database with sample data:
- In \src\database\seeders\DatabaseSeeder.php, choose data which to populate:
```php
$this->call([
    UserSeeder::class,
    AccountSeeder::class,
    RealOperationTypeSeeder::class,
    FinancialOperationSeeder::class,
]);
```

### Step 7: Start the Server
Start the development server:
```bash
php artisan serve
```

- Your application should now be accessible at http://localhost:8000 or http://localhost:8080.
- You can manage the database directly using phpMyAdmin at http://localhost/phpmyadmin.




