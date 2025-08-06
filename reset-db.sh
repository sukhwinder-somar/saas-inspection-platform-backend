#!/bin/bash

# Reset Database Script
# This script drops all tables and re-runs migrations to fix schema issues

echo "🔄 Resetting database and re-running migrations..."

# Drop all tables by deleting and recreating the SQLite database
if [ -f "database/database.sqlite" ]; then
    rm -f database/database.sqlite
    echo "🗑️  Removed existing SQLite database"
fi

touch database/database.sqlite
echo "✅ SQLite database file recreated"

# Clear all caches before migration
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run migrations again
echo "🔄 Running central migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Central migrations completed successfully"
else
    echo "❌ Error running central migrations"
    exit 1
fi

# Run tenant migrations if any tenants exist
echo "🏢 Checking for tenant migrations..."
php artisan tenants:migrate --force

if [ $? -eq 0 ]; then
    echo "✅ Tenant migrations completed successfully"
else
    echo "⚠️  No tenant migrations or tenants found"
fi

echo "🎉 Database reset complete!"
echo ""
echo "📋 Next Steps:"
echo "1. Create admin user: php artisan make:filament-user"
echo "2. Seed demo data: php artisan db:seed --class=DemoDataSeeder"
echo "3. Start server: php artisan serve"
echo ""
echo "💡 Tip: Run './setup.sh' for complete setup with user prompts"
