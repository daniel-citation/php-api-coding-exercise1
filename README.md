# Inventory API (PHP + SQLite)

This API handles product inventory. Please review for production readiness, focusing on concurrent request handling and error management.

## Requirements
- PHP 8.1+
- SQLite3 CLI (for initialisation)

## Setup

1. Install dependencies (none required beyond PHP extensions bundled by default).
2. Initialise the SQLite database:

```bash
sqlite3 database/inventory.db < database/schema.sql
```

If the `database/inventory.db` file already exists, you can reset it by deleting and re-running the command above.

## Run

From the project root directory:

```bash
php -S localhost:8000 index.php
```

## Endpoints

Base URL: `http://localhost:8000`

### GET /api/products
Return all products.

```bash
curl -s http://localhost:8000/api/products | jq
```

### GET /api/products/{id}
Return a single product by id.

```bash
curl -s http://localhost:8000/api/products/1 | jq
```

### POST /api/products
Create a product.

Body fields: `name` (string, required), `price` (number, optional), `stock_quantity` (integer, optional)

```bash
curl -s -X POST http://localhost:8000/api/products \
  -H 'Content-Type: application/json' \
  -d '{"name":"New Widget","price":12.50,"stock_quantity":30}' | jq
```

### PUT /api/products/{id}
Update any subset of fields.

```bash
curl -s -X PUT http://localhost:8000/api/products/1 \
  -H 'Content-Type: application/json' \
  -d '{"price":15.00,"stock_quantity":120}' | jq
```

Alternatively, you may use method override:

```bash
curl -s -X POST http://localhost:8000/api/products/1 \
  -H 'X-HTTP-Method-Override: PUT' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Updated Name"}' | jq
```

### POST /api/products/{id}/reserve
Reserve quantity from stock.

Body: `{ "quantity": n }`

```bash
curl -s -X POST http://localhost:8000/api/products/1/reserve \
  -H 'Content-Type: application/json' \
  -d '{"quantity":5}' | jq
```

### DELETE /api/products/{id}
Soft delete a product.

```bash
curl -s -X DELETE http://localhost:8000/api/products/1 | jq
```

## Testing

### Concurrency Testing
A test script is provided to demonstrate potential race conditions:

```bash
./test_concurrency.sh
```

This script sends concurrent reservation requests to test the atomicity of stock operations. Run it multiple times to observe behaviour under concurrent load.

## Notes
- This is a compact API intended for technical review. Assess design, correctness, and production readiness, including concurrency behaviour and error handling.
