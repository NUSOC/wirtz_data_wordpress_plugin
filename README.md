# StackWirtz WordPress Plugin

A WordPress plugin to retrieve data from a CSV file using a shortcode.

## Running Tests

This plugin includes unit tests that can be run using WP-CLI. Follow these steps to set up and run the tests:

### 1. Install the WordPress Test Environment

Run the following command to install the WordPress test environment:

```bash
./bin/install-wp-tests.sh wordpress_test root password localhost latest
```

Replace `wordpress_test` with your test database name, `root` with your database username, and `password` with your database password.

### 2. Run Tests Using WP-CLI

You can run the tests using WP-CLI with the following command:

```bash
wp eval-file tests/run-wp-tests.php
```

Or using the Composer script:

```bash
composer test-wp
```

### 3. Run Tests Using PHPUnit Directly

You can also run the tests using PHPUnit directly:

```bash
./vendor/bin/phpunit
```

Or using the Composer script:

```bash
composer test
```

## Test Structure

- `tests/bootstrap.php`: Sets up the WordPress testing environment
- `tests/test-wirtz-data.php`: Tests for the WirtzData class
- `tests/test-wirtz-show.php`: Tests for the WirtzShow class
- `tests/fixtures/`: Contains test data files

## Adding New Tests

To add new tests, create a new file in the `tests` directory with the prefix `test-`. For example, `test-new-feature.php`. The file should contain a class that extends `WP_UnitTestCase`.