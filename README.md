# Finances
System for keeping track of departmental expenses and overview of situation on departmental account

## Overview
This project is built using:
- PHP 8.1.12
- Composer 6.3
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
4. (Alternatively)Verify phpMyAdmin is working by visiting `http://localhost:8080/phpmyadmin`.

### Step 2: PHP Configuration
1. Verify your PHP installation by running the following command in your terminal:
    ```bash
    php --ini
    ```
2. Edit the php.ini file listed in the output of the previous command.
3. Uncomment the following extensions by removing the leading `;`:
    ```ini
    extension=fileinfo
    extension=gd
    ```

### Step 3: Clone the Repository
Clone the repository using the following command:
```bash
git clone https://github.com/TIS2024-FMFI/finances.git
```

### Step 4: Install Composer Packages
Navigate to the project directory:
```bash
cd finances/src
```
Install the necessary Composer packages:
```bash
composer install
```
If you encounter issues, try:
```bash
composer update --ignore-platform-req=ext-gd --ignore-platform-req=ext-fileinfo -W
```

### Step 5: Configure Environment Variables
Copy the .env.example file to create a new .env file:
```bash
cp .env.example .env
```
Generate the application key:
```bash
php artisan key:generate
```

### Step 6: Run Migrations
Run the migrations to set up the database structure:
```bash
php artisan migrate
```

###(Optional) Step 7: Seed the Database
If you want to seed the database with sample data, run:
```bash
php artisan db:seed
```
(Optional-custom) To customize the seeded data, edit the file:
```php
src/database/seeders/DatabaseSeeder.php
```
(Optional-custom) Specify the data to populate:
```php
$this->call([
    UserSeeder::class,
    AccountSeeder::class,
    RealOperationTypeSeeder::class,
    FinancialOperationSeeder::class,
]);
```

### Step 8: Start the Server
Start the development server:
```bash
php artisan serve
```
Your application should now be accessible at:
- http://localhost:8000 or http://localhost:8080.
- Manage the database directly using phpMyAdmin at http://localhost/phpmyadmin.

