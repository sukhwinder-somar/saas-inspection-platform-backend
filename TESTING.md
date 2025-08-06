# Asset Inspector - Testing Implementation

## Overview

This document provides comprehensive information about the testing implementation for the Asset Inspector SaaS application. The application includes both PHP/Laravel backend tests and React frontend component testing capabilities.

## Backend Testing

### Test Structure

```
tests/
├── Feature/
│   ├── AuthTest.php                    # Authentication functionality tests
│   ├── BasicFunctionalityTest.php     # Core application functionality
│   ├── Api/
│   │   └── AssetApiTest.php           # API endpoint tests (requires factories)
│   └── Web/
│       ├── AuthenticationTest.php     # Web authentication flows
│       └── DashboardTest.php          # Dashboard functionality
└── Unit/
    └── Models/
        ├── AssetTest.php              # Asset model unit tests
        └── OrganizationTest.php      # Organization model unit tests
```

### Running Tests

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test files
./vendor/bin/phpunit tests/Feature/AuthTest.php
./vendor/bin/phpunit tests/Feature/BasicFunctionalityTest.php

# Run tests with coverage (if configured)
./vendor/bin/phpunit --coverage-html coverage/
```

### Test Database

Tests use SQLite in-memory database for fast execution. The `RefreshDatabase` trait ensures clean state between tests.

### Currently Working Tests

#### ✅ AuthTest.php (8 tests, 43 assertions)
- User registration with organization creation
- User login/logout functionality
- API authentication endpoints
- Token-based authentication for mobile

#### ✅ BasicFunctionalityTest.php (7 tests, 13 assertions)
- Organization creation and management
- User-organization relationships
- Authentication requirements for protected routes
- Organization settings functionality
- Basic page load tests

### Test Categories

#### Authentication Tests
- Web-based login/logout
- Registration with organization creation
- API token authentication
- Password reset flows
- Email verification

#### Model Tests
- Asset model relationships and scopes
- Organization model functionality
- User model relationships
- Validation rules testing

#### API Tests
- REST API endpoints for assets
- Authentication requirements
- Data validation
- CRUD operations

#### Integration Tests
- Dashboard functionality
- Multi-tenancy isolation
- Role-based access control

## Frontend Testing Setup

### Testing Stack
- Jest for test runner
- React Testing Library for component testing
- jsdom for DOM simulation
- Babel for JSX transformation

### Test Configuration

Tests can be configured to run with Jest using the following setup:

```json
{
  "devDependencies": {
    "@testing-library/jest-dom": "^6.0.0",
    "@testing-library/react": "^14.0.0",
    "@testing-library/user-event": "^14.0.0",
    "jest": "^29.7.0",
    "jest-environment-jsdom": "^29.7.0"
  },
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage"
  }
}
```

### Component Testing Structure

```
resources/js/
├── Components/
│   ├── __tests__/
│   │   ├── ApplicationLogo.test.jsx
│   │   ├── ThemeProvider.test.jsx
│   │   └── OrganizationSwitcher.test.jsx
│   └── ...
├── Pages/
│   ├── __tests__/
│   │   ├── Dashboard.test.jsx
│   │   └── Auth/
│   │       └── Login.test.jsx
│   └── ...
└── Layouts/
    ├── __tests__/
    │   ├── AuthenticatedLayout.test.jsx
    │   └── GuestLayout.test.jsx
    └── ...
```

## Dark Mode Testing

The application includes comprehensive dark mode support with proper contrast and visibility testing.

### Dark Mode Features Tested
- Theme persistence across page loads
- Proper contrast ratios in both modes
- Component styling consistency
- Accessibility compliance

### Key Components with Dark Mode
- ✅ ApplicationLogo - Brand visibility in both themes
- ✅ GuestLayout - Login/register page styling
- ✅ TextInput - Form input styling
- ✅ InputLabel - Form label styling
- ✅ PrimaryButton - Button styling consistency
- ✅ InputError - Error message visibility

## Test Data and Factories

### Model Factories

#### AssetFactory
```php
// Creates realistic asset data for testing
Asset::factory()->create([
    'name' => 'Test Equipment',
    'type' => 'Equipment',
    'active' => true
]);

// Factory states
Asset::factory()->inactive()->create();
Asset::factory()->needsMaintenance()->create();
Asset::factory()->expiringRegistration()->create();
```

#### OrganizationFactory
```php
Organization::factory()->create([
    'name' => 'Test Organization',
    'active' => true
]);
```

## Continuous Integration

### GitHub Actions (Recommended Setup)

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        extensions: pdo, sqlite, pdo_sqlite
        
    - name: Install PHP dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader
      
    - name: Run PHP tests
      run: ./vendor/bin/phpunit
      
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        
    - name: Install JS dependencies
      run: npm ci
      
    - name: Run JS tests
      run: npm test
```

## Performance Testing

### Database Performance
- Tests include assertions for query counts
- Factory usage optimized for speed
- In-memory SQLite for fast test execution

### API Performance
- Response time assertions
- Memory usage monitoring
- Concurrent request testing

## Test Coverage Goals

### Backend Coverage Targets
- Models: 90%+ coverage
- Controllers: 80%+ coverage
- Services: 85%+ coverage
- Overall: 85%+ coverage

### Frontend Coverage Targets
- Components: 80%+ coverage
- Pages: 70%+ coverage
- Utilities: 90%+ coverage
- Overall: 75%+ coverage

## Best Practices

### Backend Testing
1. Use factories for test data creation
2. Test behavior, not implementation
3. Keep tests focused and isolated
4. Use descriptive test names
5. Test edge cases and error conditions

### Frontend Testing
1. Test user interactions, not internal state
2. Use semantic queries from Testing Library
3. Mock external dependencies
4. Test accessibility features
5. Verify visual regression with screenshots

### General Testing
1. Maintain fast test execution
2. Ensure deterministic test results
3. Use meaningful assertions
4. Document complex test scenarios
5. Regular test maintenance and updates

## Troubleshooting

### Common Issues

#### Missing Factories
If you see "Call to undefined method ::factory()", ensure model factories are created and properly named.

#### Database Errors
Ensure migrations are up to date and test database is properly configured.

#### Asset Compilation
For frontend tests, ensure Vite is properly configured for the test environment.

#### Authentication Issues
Check that Sanctum is properly configured for API testing.

## Contributing

When adding new features:

1. Write tests first (TDD approach)
2. Ensure existing tests still pass
3. Add integration tests for new workflows
4. Update documentation for new test patterns
5. Maintain test coverage levels

For questions about testing, refer to the Laravel Testing documentation and React Testing Library best practices.
