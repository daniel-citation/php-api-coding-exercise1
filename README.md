# Inventory API (PHP + SQLite)

This API handles product inventory. Please review for production readiness, focusing on concurrent request handling and error management.

## Requirements
- PHP 8.1+
- SQLite3 CLI (for initialisation)

## Setup

1. Install dependencies (none required beyond PHP extensions bundled by default).
2. Start the application and create the SQLite database:

```bash
# Start the application
docker compose up

# Create the database (in another terminal)
make db
```

The database will be reset each time you run `make db`.

You can also run SQLite commands directly in the container:

```bash
make run RUN="sqlite3 database/inventory.db \"SELECT * FROM products;\""
```

## Run

### Using Docker

The project directory is mounted into the container for development:

```bash
# Start the application
docker compose up -d
```

The API will be available at `http://localhost:8080`

## Endpoints

Base URL: `http://localhost:8080`

### GET /api/products
Return all products.

```bash
curl -s http://localhost:8080/api/products | jq
```

### GET /api/products/{id}
Return a single product by id.

```bash
curl -s http://localhost:8080/api/products/1 | jq
```

### POST /api/products
Create a product.

Body fields: `name` (string, required), `price` (number, optional), `stock_quantity` (integer, optional)

```bash
curl -s -X POST http://localhost:8080/api/products \
  -H 'Content-Type: application/json' \
  -d '{"name":"New Widget","price":12.50,"stock_quantity":30}' | jq
```

### PUT /api/products/{id}
Update any subset of fields.

```bash
curl -s -X PUT http://localhost:8080/api/products/1 \
  -H 'Content-Type: application/json' \
  -d '{"price":15.00,"stock_quantity":120}' | jq
```

Alternatively, you may use method override:

```bash
curl -s -X POST http://localhost:8080/api/products/1 \
  -H 'X-HTTP-Method-Override: PUT' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Updated Name"}' | jq
```

### POST /api/products/{id}/reserve
Reserve quantity from stock.

Body: `{ "quantity": n }`

```bash
curl -s -X POST http://localhost:8080/api/products/1/reserve \
  -H 'Content-Type: application/json' \
  -d '{"quantity":5}' | jq
```

### DELETE /api/products/{id}
Soft delete a product.

```bash
curl -s -X DELETE http://localhost:8080/api/products/1 | jq
```

## Testing

### Concurrency Testing
A test script is provided to demonstrate potential race conditions:

```bash
# Using Makefile (recommended)
make test

# Or run directly
./test_concurrency.sh
```

This script sends concurrent reservation requests to test the atomicity of stock operations. Run it multiple times to observe behaviour under concurrent load.

## Available Make Targets

The project includes a Makefile with the following targets:

- `make help` - Show available targets
- `make db` - Create the SQLite database (in container, always resets existing database)
- `make test` - Run the concurrency test
- `make run` - Run a command in the container (e.g., `make run RUN="sqlite3 database/inventory.db \"SELECT * FROM products;\""`)

## Notes
- This is a compact API intended for technical review. Assess design, correctness, and production readiness, including concurrency behaviour and error handling.

