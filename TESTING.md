# Testing Guide for Truffle Package

This document describes the testing workflows and quality assurance processes for the Truffle Laravel package.

## Overview

The Truffle package uses a comprehensive testing strategy that includes:

- **Unit Tests**: PHPUnit tests to verify functionality
- **Static Analysis**: PHPStan for code quality
- **Code Style**: PHP CS Fixer for consistent formatting
- **Compatibility Testing**: Multiple PHP and Laravel versions
- **Security Auditing**: Composer security audit
- **Continuous Integration**: GitHub Actions workflows

## Quick Start

### Local Testing

Run all tests and quality checks locally:

```bash
./run-tests.sh
```

Or use individual composer commands:

```bash
composer test      # Run PHPUnit tests
composer stan      # Run PHPStan static analysis
composer format    # Fix code style issues
composer cs        # Run all quality checks
```

### Manual Testing Commands

```bash
# Install dependencies
composer install

# Run tests with coverage
vendor/bin/phpunit --coverage-html=build/coverage

# Run static analysis
vendor/bin/phpstan analyse src --memory-limit=1G

# Check code style
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --dry-run --diff

# Fix code style
vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php

# Security audit
composer audit
```

## GitHub Actions Workflows

### 1. Tests Workflow (`.github/workflows/tests.yml`)

**Triggers**: Push to main/develop, Pull requests
**Purpose**: Run comprehensive tests across multiple environments

**Matrix Testing**:
- **PHP Versions**: 7.4, 8.0, 8.1, 8.2, 8.3
- **Laravel Versions**: 8.x, 9.x, 10.x, 11.x
- **Dependency Strategies**: prefer-lowest, prefer-stable
- **OS**: Ubuntu Latest

**Features**:
- Automatic version compatibility exclusions
- Code coverage reporting to Codecov
- PHPUnit problem matchers
- Comprehensive environment setup

### 2. Code Quality Workflow (`.github/workflows/code-quality.yml`)

**Triggers**: Push to main/develop, Pull requests
**Purpose**: Ensure code quality and standards

**Checks**:
- **PHP CS Fixer**: Code style validation
- **PHPStan**: Static analysis (Level 8)
- **Security Audit**: Dependency vulnerability scanning
- **Composer Validation**: composer.json and composer.lock validation

### 3. Laravel Compatibility Workflow (`.github/workflows/compatibility.yml`)

**Triggers**: Push to main/develop, Pull requests, Weekly schedule
**Purpose**: Test compatibility with specific Laravel/Testbench versions

**Test Matrix**:
- Laravel 8.x with PHP 7.4, 8.0, 8.1
- Laravel 9.x with PHP 8.0, 8.1, 8.2
- Laravel 10.x with PHP 8.1, 8.2, 8.3
- Laravel 11.x with PHP 8.2, 8.3

### 4. Release Workflow (`.github/workflows/release.yml`)

**Triggers**: Git tags matching `v*.*.*`
**Purpose**: Automated release process

**Steps**:
1. Run full test suite
2. Validate code quality
3. Create GitHub release
4. Update Packagist (requires secrets)

## Configuration Files

### PHPUnit Configuration (`phpunit.xml`)

```xml
<phpunit>
    <testsuites>
        <testsuite name="Package Test Suite">
            <directory suffix="Test.php">./tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </source>
    <!-- Coverage and logging configuration -->
</phpunit>
```

### PHPStan Configuration (`phpstan.neon`)

- **Level**: 8 (strictest)
- **Paths**: `src/` directory
- **Ignores**: Common Laravel/Eloquent magic methods
- **Excludes**: `tests/` and `vendor/` directories

### PHP CS Fixer Configuration (`.php-cs-fixer.php`)

Uses existing configuration file for consistent code style.

## Testing Best Practices

### Writing Tests

1. **Follow PSR-4**: Tests should mirror the `src/` structure
2. **Use TestCase**: Extend the base `TestCase` class
3. **Data Providers**: Use data providers for multiple test scenarios
4. **Assertions**: Use descriptive assertion messages
5. **Setup/Teardown**: Clean up resources in `tearDown()`

### Test Organization

```
tests/
├── Models/           # Test models
├── migrations/       # Test migrations
├── TestCase.php     # Base test case
└── TruffleTest.php  # Main feature tests
```

### Coverage Goals

- **Minimum**: 80% line coverage
- **Target**: 90% line coverage
- **Critical Paths**: 100% coverage for core functionality

## Continuous Integration

### Required Checks

All pull requests must pass:
1. ✅ Tests across all supported PHP/Laravel versions
2. ✅ PHPStan static analysis
3. ✅ PHP CS Fixer code style check
4. ✅ Security audit
5. ✅ Composer validation

### Optional Checks

- **Code Coverage**: Reported but not enforcing
- **Performance**: Benchmarks for critical operations
- **Documentation**: Ensure docs are up to date

## Environment Setup

### Requirements

- **PHP**: 7.4+ with extensions: `pdo_sqlite`, `sqlite3`
- **Composer**: Latest stable version
- **Git**: For version control

### Local Development

```bash
# Clone repository
git clone https://github.com/waad/truffle.git
cd truffle

# Install dependencies
composer install

# Run tests
./run-tests.sh
```

### IDE Configuration

**PHPStorm**:
- Enable PHPStan integration
- Configure PHP CS Fixer
- Set up PHPUnit test runner

**VS Code**:
- Install PHP extensions
- Configure test explorer
- Set up code formatting

## Troubleshooting

### Common Issues

1. **SQLite Extension Missing**
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-sqlite3
   
   # macOS
   brew install php
   ```

2. **Memory Limit for PHPStan**
   ```bash
   php -d memory_limit=1G vendor/bin/phpstan analyse
   ```

3. **Permission Issues**
   ```bash
   chmod +x run-tests.sh
   ```

### Test Failures

1. **Check Environment**: Ensure all extensions are installed
2. **Clear Cache**: Delete `vendor/` and reinstall
3. **Update Dependencies**: Run `composer update`
4. **Check Logs**: Review PHPUnit output for details

## Contributing

1. **Fork** the repository
2. **Create** a feature branch
3. **Write** tests for new functionality
4. **Run** the test suite: `./run-tests.sh`
5. **Submit** a pull request

All contributions must:
- Include tests
- Pass all quality checks
- Follow coding standards
- Update documentation

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPStan Documentation](https://phpstan.org/)
- [PHP CS Fixer Documentation](https://cs.symfony.com/)
- [Orchestra Testbench](https://github.com/orchestral/testbench)
- [Laravel Testing](https://laravel.com/docs/testing)
