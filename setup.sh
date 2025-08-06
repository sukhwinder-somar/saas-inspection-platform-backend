#!/bin/bash

# SaaS AI Inspection Platform Setup Script
# This script automates the setup process for the multi-tenant SaaS application

echo "🚀 Setting up SaaS AI Inspection Platform..."
echo "================================================"

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the project root directory"
    exit 1
fi

# Step 1: Create .env file if it doesn't exist
if [ ! -f ".env" ]; then
    echo "📄 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Step 2: Install PHP dependencies
echo "📦 Installing PHP dependencies..."
if command -v composer &> /dev/null; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
    if [ $? -eq 0 ]; then
        echo "✅ PHP dependencies installed successfully"
    else
        echo "❌ Error installing PHP dependencies"
        exit 1
    fi
else
    echo "❌ Composer not found. Please install Composer first."
    exit 1
fi

# Step 3: Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
if command -v npm &> /dev/null; then
    npm install
    if [ $? -eq 0 ]; then
        echo "✅ Node.js dependencies installed successfully"
    else
        echo "❌ Error installing Node.js dependencies"
        exit 1
    fi
else
    echo "⚠️  npm not found. Skipping Node.js dependencies."
fi

# Step 4: Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force
if [ $? -eq 0 ]; then
    echo "✅ Application key generated successfully"
else
    echo "❌ Error generating application key"
    exit 1
fi

# Step 5: Reset and setup database
echo "🗄️  Setting up database..."
# Remove old database file if exists
if [ -f "database/database.sqlite" ]; then
    rm database/database.sqlite
    echo "🗑️  Removed old database file"
fi

# Create new database file
touch database/database.sqlite
echo "✅ SQLite database file created"

# Step 6: Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo "✅ Caches cleared"

# Step 7: Run central database migrations
echo "🔄 Running central database migrations..."
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo "✅ Central database migrations completed"
else
    echo "❌ Error running central database migrations"
    exit 1
fi

# Step 8: Publish tenancy assets
echo "🏢 Publishing tenancy assets..."
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider" --force
if [ $? -eq 0 ]; then
    echo "✅ Tenancy assets published successfully"
else
    echo "❌ Error publishing tenancy assets"
    exit 1
fi

# Step 9: Publish Filament assets
echo "🎨 Publishing Filament assets..."
php artisan filament:upgrade --force
if [ $? -eq 0 ]; then
    echo "✅ Filament assets published successfully"
else
    echo "⚠️  Filament upgrade completed with warnings"
fi

# Step 10: Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link
if [ $? -eq 0 ]; then
    echo "✅ Storage symlink created successfully"
else
    echo "⚠️  Storage symlink may already exist"
fi

# Step 11: Create admin user (interactive)
echo "👤 Creating admin user..."
echo "Please enter the admin user details:"
php artisan make:filament-user

# Step 12: Setup Valet (if available)
if command -v valet &> /dev/null; then
    echo "🚗 Setting up Laravel Valet..."
    valet link filament-starter
    echo "✅ Valet linked as 'filament-starter.test'"
    echo "📝 You can access the app at: https://filament-starter.test"
else
    echo "⚠️  Valet not found. You can install it with:"
    echo "   composer global require laravel/valet"
    echo "   valet install"
fi

# Step 13: Seed demo data (optional)
echo ""
read -p "🌱 Would you like to seed demo data? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan db:seed --class=DemoDataSeeder
    if [ $? -eq 0 ]; then
        echo "✅ Demo data seeded successfully"
        echo ""
        echo "📋 Demo Tenant Information:"
        echo "Check the command output above for the generated domain"
        echo "Follow the Valet linking instructions provided"
    else
        echo "❌ Error seeding demo data"
    fi
fi

# Step 14: Build frontend assets (if npm is available)
if command -v npm &> /dev/null; then
    echo "🎨 Building frontend assets..."
    npm run build
    if [ $? -eq 0 ]; then
        echo "✅ Frontend assets built successfully"
    else
        echo "⚠️  Error building frontend assets"
    fi
fi

echo ""
echo "🎉 Setup completed successfully!"
echo "================================================"
echo ""
if command -v valet &> /dev/null; then
    echo "📋 Next Steps (Valet):"
    echo "1. Visit https://filament-starter.test for the main site"
    echo "2. Visit https://filament-starter.test/admin for the admin panel"
    echo "3. For tenant access, use the domain from the seeder output"
    echo ""
    echo "📚 Important URLs:"
    echo "- Main Site: https://filament-starter.test"
    echo "- Admin Panel: https://filament-starter.test/admin"
    echo "- Health Check: https://filament-starter.test/health"
else
    echo "📋 Next Steps:"
    echo "1. Start the development server: php artisan serve"
    echo "2. Visit http://localhost:8000 for the main site"
    echo "3. Visit http://localhost:8000/admin for the admin panel"
    echo ""
    echo "� Important URLs:"
    echo "- Main Site: http://localhost:8000"
    echo "- Admin Panel: http://localhost:8000/admin"
    echo "- Health Check: http://localhost:8000/health"
fi
echo ""
echo "🔧 Useful Commands:"
echo "- php artisan tenants:migrate          # Run tenant migrations"
echo "- php artisan tenant:list              # List all tenants"
echo "- php artisan queue:work               # Start queue worker"
echo "- ./reset-db.sh                       # Reset database completely"
if command -v valet &> /dev/null; then
    echo "- valet link [name]                   # Link new tenant domain"
    echo "- valet links                         # List all linked sites"
fi
echo ""
echo "Happy coding! 🚀"
