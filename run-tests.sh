#!/bin/bash

# Truffle Package Testing Script
# This script runs all the tests and quality checks locally

set -e

echo "🧪 Running Truffle Package Tests"
echo "================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    print_error "Composer is not installed. Please install composer first."
    exit 1
fi

# Install dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
    print_status "Dependencies installed"
fi

# Run PHPUnit tests
echo ""
echo "🧪 Running PHPUnit tests..."
if composer test; then
    print_status "All tests passed"
else
    print_error "Tests failed"
    exit 1
fi

# Run PHPStan static analysis
echo ""
echo "🔍 Running PHPStan static analysis..."
if composer stan; then
    print_status "Static analysis passed"
else
    print_error "Static analysis failed"
    exit 1
fi

# Run PHP CS Fixer to check code style
echo ""
echo "🎨 Checking code style..."
if vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run --diff; then
    print_status "Code style is good"
else
    print_warning "Code style issues found. Run 'composer format' to fix them."
fi

# Run security audit
echo ""
echo "🔒 Running security audit..."
if composer audit; then
    print_status "No security vulnerabilities found"
else
    print_warning "Security vulnerabilities found. Please review and update dependencies."
fi

echo ""
echo "🎉 All checks completed!"
echo ""
echo "Available commands:"
echo "  composer test     - Run tests"
echo "  composer stan     - Run static analysis"
echo "  composer format   - Fix code style"
echo "  composer cs       - Run all checks"
echo ""
