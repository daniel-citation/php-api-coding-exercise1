DC = docker compose
DCE = ${DC} exec -w
DCE_CLI = ${DC} exec inventory-api bash -c

ifndef RUN
override RUN = "-h"
endif

.PHONY: help db-create db-reset db-clean test run

# Default target
help:
	@echo "Available targets:"
	@echo "  db-create    - Create the SQLite database (requires running container)"
	@echo "  db-reset     - Reset the database (delete and recreate)"
	@echo "  db-clean     - Remove the database file"
	@echo "  test         - Run the concurrency test"
	@echo "  run          - Run a command in the container (e.g., make run RUN='sqlite3 database/inventory.db \"SELECT * FROM products;\"')"

# Database creation with proper permissions (run in container)
db-create:
	@echo "Creating database directory..."
	@mkdir -p database
	@echo "Creating SQLite database in container..."
	@${DCE_CLI} "mkdir -p /var/www/html/database && sqlite3 /var/www/html/database/inventory.db < /var/www/html/database/schema.sql"
	@echo "Database created successfully at database/inventory.db"


# Reset database (delete and recreate)
db-reset:
	@make db-clean --no-print-directory
	@make db-create --no-print-directory
	@echo "Database reset complete"

# Clean database (run in container)
db-clean:
	@echo "Removing database file..."
	@${DCE_CLI} "rm -f /var/www/html/database/inventory.db"
	@echo "Database cleaned"

# Docker targets removed - users can use docker compose directly

# Test target
test:
	@echo "Running concurrency test..."
	@./test_concurrency_docker.sh

# Run command in container
run:
	@${DCE_CLI} "${RUN}"

