# Self-Hosted TLS Manager Development Guidelines

This document provides essential information for developers working on the Self-Hosted TLS Manager project.

## Build and Configuration Instructions

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- OpenSSL PHP extension

### Initial Setup

1. Clone the repository
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Install JavaScript dependencies:
   ```bash
   npm install
   ```
4. Create environment file:
   ```bash
   cp .env.example .env
   ```
5. Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Create SQLite database:
   ```bash
   touch database/database.sqlite
   ```
7. Run migrations:
   ```bash
   php artisan migrate
   ```

### Development Server

Start the development server with:

```bash
composer dev
```

This command runs:
- Laravel development server
- Queue worker
- Laravel Pail (log viewer)
- Vite development server

For server-side rendering (SSR), use:

```bash
composer dev:ssr
```

## Testing Information

### Testing Framework

The project uses Pest PHP for testing, which is a modern testing framework built on top of PHPUnit. Tests are organized into:

- **Feature Tests**: Test the application's HTTP endpoints and features
- **Unit Tests**: Test individual components in isolation

### Running Tests

Run all tests with:

```bash
composer test
```

This command clears the configuration cache and runs all tests.

### Creating Tests

#### Unit Test Example

Here's an example of a unit test for the OpenSslService:

```php
<?php

use App\Services\OpenSslService;

test('can generate root CA certificate', function () {
    $service = new OpenSslService();
    
    $result = $service->generateRootCa(
        'Test Root CA',
        'example.com',
        'test-passphrase'
    );
    
    // Check that the result contains the expected keys
    expect($result)->toHaveKeys(['private_key', 'public_key', 'certificate']);
    
    // Check that the values are non-empty strings
    expect($result['private_key'])->toBeString()->not->toBeEmpty();
    expect($result['public_key'])->toBeString()->not->toBeEmpty();
    expect($result['certificate'])->toBeString()->not->toBeEmpty();
    
    // Verify that the certificate is in PEM format
    expect($result['certificate'])->toContain('-----BEGIN CERTIFICATE-----')
                                  ->toContain('-----END CERTIFICATE-----');
                                  
    // Convert the certificate to check its details
    $certInfo = openssl_x509_parse($result['certificate']);
    expect($certInfo)->toBeArray();
    expect($certInfo['subject']['CN'])->toBe('Test Root CA');
});
```

#### Feature Test Example

Feature tests typically test HTTP endpoints. Here's an example structure:

```php
<?php

use App\Models\User;
use App\Models\RootCa;

test('user can view root CA list', function () {
    $user = User::factory()->create();
    
    $response = $this
        ->actingAs($user)
        ->get('/settings/root-cas');
    
    $response->assertOk();
});
```

### Test Database

Tests use an in-memory SQLite database with the `RefreshDatabase` trait, which ensures a clean database state for each test.

## Additional Development Information

### Code Style

#### PHP

- The project uses Laravel Pint for PHP code formatting (PSR-12 based)
- Run Pint with: `./vendor/bin/pint`

#### JavaScript/TypeScript/Vue

- The project uses ESLint and Prettier for JavaScript/TypeScript/Vue code formatting
- Configuration:
  - 4 spaces for indentation
  - Single quotes
  - Semicolons
  - 150 character line length
- Run linting with: `npm run lint`
- Run formatting with: `npm run format`

### Architecture

- **Backend**: Laravel 12
- **Frontend**: Vue.js 3 with TypeScript
- **API Integration**: Inertia.js
- **Styling**: Tailwind CSS
- **Build Tool**: Vite

### Key Components

- **OpenSslService**: Core service for generating and managing certificates
- **RootCa Model**: Represents a root certificate authority
- **Controllers**: Follow Laravel's resource controller pattern

### Debugging

- **Laravel Pail**: Real-time log viewer, available when running `composer dev`
- **Laravel Tinker**: Interactive REPL for the application
  ```bash
  php artisan tinker
  ```

### Frontend Development

- The project uses Vue.js 3 with TypeScript
- Components are in the `resources/js` directory
- Pages are in the `resources/js/pages` directory
- Inertia.js is used for server-side rendering and API integration
- Ziggy is used for route handling between Laravel and JavaScript
