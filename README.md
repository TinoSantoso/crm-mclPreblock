# CRM Microapp

A lightweight CRM application built with Lumen PHP framework for efficient customer relationship management.

![CRM Microapp](https://via.placeholder.com/800x400?text=CRM+Microapp)

## Overview

This CRM Microapp provides a streamlined solution for managing customer relationships, sales reporting, and visit tracking. Built on the lightweight Lumen PHP framework, it offers excellent performance while maintaining robust functionality.

## Features

- **Sales Reporting**: Generate comprehensive sales reports by district
- **Visit Management**: Track and manage customer visits
- **Master Customer List (MCL)**: Entry and export functionality
- **Data Export**: Export sales data to various formats
- **Responsive Dashboard**: Monitor key metrics at a glance

## Requirements

- PHP >= 8.1
- Composer
- MySQL/MariaDB
- Node.js and NPM (for frontend assets)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/crm-microapp-lumen.git
   cd crm-microapp-lumen
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Set up environment variables:
   ```bash
   cp .env.example .env
   # Edit .env file with your database credentials and other settings
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Run migrations:
   ```bash
   php artisan migrate
   ```

6. Seed the database (optional):
   ```bash
   php artisan db:seed
   ```

7. Start the development server:
   ```bash
   php -S localhost:8000 -t public
   ```

## Project Structure

```
├── app/                  # Application core code
│   ├── Console/          # Console commands
│   ├── Events/           # Event classes
│   ├── Exceptions/       # Exception handlers
│   ├── Exports/          # Data export functionality
│   ├── Http/             # HTTP layer
│   │   ├── Controllers/  # Request controllers
│   │   └── Middleware/   # HTTP middleware
│   ├── Jobs/             # Queue jobs
│   ├── Listeners/        # Event listeners
│   ├── Models/           # Database models
│   └── Providers/        # Service providers
├── asset/                # Frontend assets
│   ├── css/              # Stylesheets
│   ├── fonts/            # Font files
│   └── js/               # JavaScript files
├── bootstrap/            # App bootstrapping
├── config/               # Configuration files
├── database/             # Database migrations and seeds
├── public/               # Public directory
├── resources/            # Application resources
│   └── views/            # View templates
├── routes/               # Route definitions
├── storage/              # Storage directory
└── tests/                # Test cases
```

## Usage

After installation, access the application through your web browser at `http://localhost:8000` or your configured domain.

Login with your credentials to access the dashboard and start managing your customer relationships.

## Technologies

- **Backend**: Lumen PHP Framework
- **Frontend**: HTML, CSS, JavaScript
- **UI Components**: DevExtreme, Bootstrap
- **Icons**: Font Awesome
- **Database**: MySQL/MariaDB

## License

[MIT](LICENSE)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
