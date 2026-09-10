# Mini GraphQL App

A minimal, full-stack GraphQL API project built with native PHP and SQLite, featuring JWT authentication and a vanilla JavaScript frontend.

## Features
- **GraphQL API:** Built using the `webonyx/graphql-php` library.
- **Authentication:** Secure endpoints using JSON Web Tokens (JWT).
- **Database:** Zero-configuration embedded SQLite database.
- **Frontend UI:** A simple HTML/CSS/JS interface to interact with the API seamlessly using `fetch`.

## Prerequisites
- PHP >= 8.1
- Composer

## Installation & Setup

1. **Clone the repository:**
    ```bash
    git clone [https://github.com/javamohammed/mini-graphql-app.git](https://github.com/javamohammed/mini-graphql-app.git)
    cd mini-graphql-app

2. **Install dependencies:**
    ```bash
    composer install
    ```
3. **Set up the database:**
    ```bash
    php init_db.php

4. **Start the PHP built-in server:**
    ```bash
    php -S localhost:8000 -t public

## Usage

**-Once the server is running, you can test the API by opening the index.html file in your browser.**

    **Test Credentials:**
    Username: admin
    Password: 123456
**-You can also use tools like Postman to send queries directly to http://localhost:8000/index.php**

## Project Structure
    **src/ - Contains core PHP classes (Database.php, Auth.php, Schema.php).**
    **public/index.php - The main GraphQL endpoint.**
    **index.html - The vanilla JavaScript frontend.**
    **init_db.php - Database initialization script.**
