# Structura

A utility for reading and analyzing database schemas and converting them into
other formats.

## Status

This project is in active development. It is currently in a pre-alpha state. Please expect breaking changes and missing functionality.

## Running Tests

```sh
# Start the MySQL server
docker compose up -d mysql

# Run the test suite
docker compose run --rm php composer test
```

[Code coverage reports](https://docs.phpunit.de/en/11.5/code-coverage.html) are output to the `coverage` directory in the root project directory.
