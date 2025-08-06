# SaaS Inspection Platform - Backend

A comprehensive multi-tenant SaaS platform built with Laravel 11 and Filament v3 for asset inspection management.

## Features

### Core Functionality
- **Multi-Tenancy**: Full tenant isolation with subdomain-based routing
- **Filament Admin Panels**: Separate central and tenant admin interfaces
- **Dynamic Content Management**: ElementalArea-like page builder with reusable blocks
- **Asset Management**: Comprehensive asset tracking and inspection workflows
- **User Management**: Role-based access control with Spatie Laravel Permission
- **Subscription Management**: Built-in billing and subscription handling
- **Notification System**: Email and SMS notifications with AWS SNS integration

### Technical Stack
- Laravel 11.45.1
- PHP 8.3.21
- Filament v3 (Admin Panels)
- SQLite (Development) / MySQL (Production)
- Multi-tenant architecture with stancl/tenancy
- shadcn/ui design system
- Alpine.js & Livewire for interactivity

## Quick Start

### Development with Docker
```bash
# Start the development environment
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Run migrations and seed data
docker-compose exec app php artisan migrate --seed

# Build frontend assets
docker-compose exec app npm run build
```

### Local Development (Laravel Valet)
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Build assets
npm run build

# Start development server
php artisan serve
```

## Architecture

### Multi-Tenancy
- **Central Domain**: Main SaaS platform management
- **Tenant Domains**: Individual customer instances
- **Database Isolation**: Separate databases per tenant
- **File Storage**: Tenant-aware file organization

### Admin Panels
- **Central Admin** (`/admin`): Platform management, organizations, subscriptions
- **Tenant Admin** (`/admin` on tenant domains): Customer-specific management

### Content Management
- **Dynamic Pages**: Database-driven pages with SEO optimization
- **Content Blocks**: Reusable components (Hero, Features, CTA, Text, etc.)
- **Template System**: Flexible page templates with fallback support

## API Documentation

The platform provides RESTful APIs for mobile application integration:

- **Authentication**: Token-based API authentication
- **Assets**: CRUD operations for asset management
- **Inspections**: Mobile-friendly inspection workflows
- **Notifications**: Real-time notification delivery
- **Sync**: Offline-first data synchronization

## Testing

```bash
# Run PHP tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
```

## License

This project is licensed under the MIT License.
EOF < /dev/null