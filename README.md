# Price Configurator - Setup Guide

## Prerequisites

- PHP (version 8.2 or higher)
- Node.js (version 20 or higher)
- npm package manager
- MySQL
- Composer
- Git

## Setup Options

Choose one of the following setup methods:

### Option 1: Automatic Setup (Recommended)

Run the automatic setup script that handles everything for you:

```bash
# Clone the repository
git clone git@github.com:iammursal/price-configurator.git
cd price-configurator

# Run the automatic setup script
bash ./scripts/deploy.sh
```

The script will automatically:
- Install PHP dependencies via Composer
- Install Node.js dependencies via npm
- Copy and configure environment file
- Generate application key
- Run migrations and seeders
- Build frontend assets

### Option 2: Manual Setup

For those who prefer manual control over each step:

#### 1. Clone the repository
```bash
git clone git@github.com:iammursal/price-configurator.git
cd price-configurator
```

#### 2. Install PHP dependencies
```bash
cp .env.example .env
```
Edit the `.env` file with your configuration values:

#### 3. Install Node.js dependencies
```bash
npm install
```

#### 4. Environment configuration
```bash
composer install
```

#### 5. Generate application key
```bash
php artisan key:generate
```

#### 6. Database setup
```bash
# Run migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

#### 7. Build frontend assets
```bash
npm run build
# Or for development with hot reload:
# npm run dev
```

#### 8. Start the development server
```bash
php artisan serve
```

Or if using Laravel Herd:
```bash
herd link
```

## Post-Setup

After successful setup, you can access the application at:
- **Local development**: http://localhost:8000
- **Laravel Herd**: http://price-configurator.test

### Sample Data

The seeder creates:
- 10 sample products with configurable attributes
- 4 attribute types: Delivery Method, Speed, Color, Size
- Various discount rules for testing
- Each product has 1-2 randomly assigned attributes

### Development Commands


# Reset database with fresh data
```bash
php artisan migrate:fresh --seed
```
