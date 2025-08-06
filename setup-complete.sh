#!/bin/bash

# Complete Application Setup Script with Valet Support
# This script sets up the SaaS AI Inspection Platform from scratch

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

# Step 3: Generate application key
echo "🔑 Generating application key..."
php artisan key:generate --force
if [ $? -eq 0 ]; then
    echo "✅ Application key generated successfully"
else
    echo "❌ Error generating application key"
    exit 1
fi

# Step 4: Setup Valet first (if available)
if command -v valet &> /dev/null; then
    echo "🚗 Setting up Laravel Valet..."
    valet link filament-starter
    echo "✅ Valet linked as 'filament-starter.test'"
    echo "📝 Main app available at: https://filament-starter.test"
else
    echo "⚠️  Valet not found. You can install it with:"
    echo "   composer global require laravel/valet"
    echo "   valet install"
fi

# Step 5: Complete database reset and setup
echo "🗄️  Setting up database..."
./reset-db.sh

# Step 6: Create admin user (interactive)
echo "👤 Creating admin user..."
echo "Please enter the admin user details:"
php artisan make:filament-user

# Step 7: Create demo tenant and users
echo ""
read -p "🌱 Would you like to create demo tenant and users? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🏢 Creating demo tenant..."
    
    # Create demo organization without observer conflicts
    php artisan tinker --execute="
        \App\Models\Organization::withoutEvents(function() {
            \$timestamp = time();
            \$org = \App\Models\Organization::create([
                'id' => 'demo-' . \$timestamp,
                'name' => 'Demo Manufacturing Co.',
                'slug' => 'demo-manufacturing-co-' . \$timestamp,
                'subdomain' => 'demo' . \$timestamp,
                'data' => ['status' => 'active'],
                'settings' => ['timezone' => 'UTC', 'currency' => 'USD'],
            ]);
            
            // Create domain
            \Stancl\Tenancy\Database\Models\Domain::create([
                'domain' => 'demo' . \$timestamp . '.test',
                'tenant_id' => 'demo-' . \$timestamp,
            ]);
            
            echo '✅ Organization created: ' . \$org->name . PHP_EOL;
            echo '🌐 Domain: demo' . \$timestamp . '.test' . PHP_EOL;
            echo '🔗 Run: valet link demo' . \$timestamp . ' (from any directory)' . PHP_EOL;
        });
    "
    
    # Run tenant migrations
    echo "🔄 Running tenant migrations..."
    php artisan tenants:migrate
    
    # Seed tenant data
    echo "🌱 Seeding tenant data..."
    php artisan tenants:run db:seed --class=RolePermissionSeeder
    
    echo "✅ Demo tenant setup completed"
fi

# Step 8: Install Node.js dependencies and build assets
if command -v npm &> /dev/null; then
    echo "📦 Installing Node.js dependencies..."
    npm install
    echo "🎨 Building frontend assets..."
    npm run build
    echo "✅ Frontend assets built successfully"
else
    echo "⚠️  npm not found. Skipping frontend build."
fi

echo ""
echo "🎉 Setup completed successfully!"
echo "================================================"
echo ""
echo "📋 Access Your Application:"
if command -v valet &> /dev/null; then
    echo "🌐 Main Site: https://filament-starter.test"
    echo "👨‍💼 Admin Panel: https://filament-starter.test/admin"
    echo "🏢 Demo Tenant: https://demo[timestamp].test/admin (if created)"
    echo ""
    echo "💡 Valet Commands:"
    echo "- valet link [name]           # Link current directory"
    echo "- valet links                 # List all links"
    echo "- valet unlink               # Unlink current directory"
else
    echo "🌐 Main Site: http://localhost:8000"
    echo "👨‍💼 Admin Panel: http://localhost:8000/admin"
    echo ""
    echo "▶️  Start server: php artisan serve"
fi
echo ""
echo "🔧 Useful Commands:"
echo "- php artisan tenants:list            # List all tenants"
echo "- php artisan tenants:migrate         # Run tenant migrations"
echo "- php artisan queue:work              # Start queue worker"
echo "- ./reset-db.sh                      # Reset database completely"
echo ""
echo "📚 Development URLs:"
echo "- Health Check: /health"
echo "- Horizon (if running): /horizon"
echo ""
echo "Happy coding! 🚀"
