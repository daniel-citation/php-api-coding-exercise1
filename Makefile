DC = docker compose
DCE = ${DC} exec -w
DCE_CLI = ${DC} exec inventory-api bash -c

ifndef RUN
override RUN = "-h"
endif

# specify the default target, if not "all"
default: help

.PHONY: help
help:
	@echo "Available targets:"
	@echo "  db          - Create the SQLite database (requires running container)"
	@echo "  test         - Run the concurrency test"
	@echo "  run          - Run a command in the container (e.g., make run RUN='sqlite3 database/inventory.db \"SELECT * FROM products;\"')"

.PHONY: db
db:
	@echo "Creating database directory..."
	@mkdir -p database
	@echo "Removing existing database file..."
	@${DCE_CLI} "rm -f /var/www/html/database/inventory.db"
	@echo "Creating SQLite database in container..."
	@${DCE_CLI} "mkdir -p /var/www/html/database && sqlite3 /var/www/html/database/inventory.db < /var/www/html/database/schema.sql"
	@echo "Database created successfully at database/inventory.db"

.PHONY: test
test:
	@echo "Running concurrency test..."
	@./test_concurrency_docker.sh

.PHONY: run
run:
	@${DCE_CLI} "${RUN}"

