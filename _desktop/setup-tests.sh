#!/bin/bash

# Setup script for Top 5 Tião Carreiro Test Suite
# This script prepares the environment for running tests

echo "🎵 Top 5 Tião Carreiro - Test Setup"
echo "=================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print status
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

# Function to print warning
print_warning() {
    echo -e "${YELLOW}⚠️ $1${NC}"
}

echo "Checking PHP version..."
PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "PHP Version: $PHP_VERSION"

echo ""
echo "Checking PHP extensions..."

# Check for required extensions
REQUIRED_EXTENSIONS=("pdo" "json" "mbstring" "tokenizer" "xml" "ctype")
OPTIONAL_EXTENSIONS=("sqlite3" "pdo_sqlite" "xdebug")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if php -m | grep -qi "$ext"; then
        print_status 0 "$ext extension"
    else
        print_status 1 "$ext extension (REQUIRED)"
        MISSING_REQUIRED=1
    fi
done

echo ""
echo "Checking optional extensions..."

for ext in "${OPTIONAL_EXTENSIONS[@]}"; do
    if php -m | grep -qi "$ext"; then
        print_status 0 "$ext extension"
    else
        print_warning "$ext extension not found (optional but recommended)"
    fi
done

echo ""
echo "Checking Laravel setup..."

# Check if vendor directory exists
if [ -d "vendor" ]; then
    print_status 0 "Composer dependencies installed"
else
    print_status 1 "Composer dependencies missing"
    echo "Run: composer install"
    exit 1
fi

# Check if .env exists
if [ -f ".env" ]; then
    print_status 0 ".env file exists"
else
    print_warning ".env file not found"
    if [ -f ".env.example" ]; then
        echo "Creating .env from .env.example..."
        cp .env.example .env
        print_status 0 ".env created from example"
    fi
fi

# Generate app key if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating application key..."
    php artisan key:generate
    print_status 0 "Application key generated"
fi

echo ""
echo "Testing database configuration..."

# Test database connection for testing
echo "Checking test database configuration..."

# Try to run a simple test
if php artisan config:cache > /dev/null 2>&1; then
    print_status 0 "Configuration cached successfully"
else
    print_warning "Could not cache configuration"
fi

echo ""
echo "Running a sample test..."

# Try to run one simple test to check if setup works
TEST_RESULT=$(php artisan test tests/Unit/AuthServiceTest.php --stop-on-failure --filter="test_authenticate_with_valid_credentials" 2>&1)
TEST_EXIT_CODE=$?

if [ $TEST_EXIT_CODE -eq 0 ]; then
    print_status 0 "Sample test passed - environment ready!"
else
    print_warning "Sample test failed. This might be due to missing database setup."
    echo "Test output:"
    echo "$TEST_RESULT"
    echo ""
    echo "To fix database issues:"
    echo "1. For SQLite: sudo apt-get install php8.3-sqlite3"
    echo "2. For MySQL: Create a 'testing' database"
    echo "3. Or modify phpunit.xml to use your preferred database"
fi

echo ""
echo "📊 Test Suite Overview:"
echo "======================"
echo "Feature Tests:  49 tests (API endpoints)"
echo "Unit Tests:     82+ tests (Services, Repositories)"
echo "Model Tests:    72+ tests (Models and relationships)"
echo "Total:          150+ tests"

echo ""
echo "🚀 Quick Commands:"
echo "=================="
echo "Run all tests:           php artisan test"
echo "Run feature tests:       php artisan test --testsuite=Feature"
echo "Run unit tests:          php artisan test --testsuite=Unit"
echo "Run with coverage:       php artisan test --coverage"
echo "Run specific test:       php artisan test tests/Feature/AuthTest.php"

echo ""
echo "📚 Documentation:"
echo "================="
echo "- Full guide: TESTING.md"
echo "- Laravel Testing: https://laravel.com/docs/testing"
echo "- PHPUnit Docs: https://phpunit.de/documentation.html"

echo ""
if [ -z "$MISSING_REQUIRED" ]; then
    echo -e "${GREEN}🎉 Setup complete! You can now run the test suite.${NC}"
else
    echo -e "${RED}⚠️ Please install missing required extensions before running tests.${NC}"
fi