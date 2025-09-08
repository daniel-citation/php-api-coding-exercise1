#!/bin/bash

# Concurrency test script for the inventory API running in Docker
# This script demonstrates race conditions in the reserve endpoint

echo "=== Concurrency Test for Inventory API (Docker) ==="
echo "This script will attempt to reserve more stock than available"
echo "by sending concurrent requests to demonstrate race conditions."
echo

# API URL for Docker container (use service name when running inside Docker network)
if [ -n "$DOCKER_CONTAINER" ]; then
    API_URL="http://inventory-api"
else
    API_URL="http://localhost:8080"
fi

# Check if server is running
if ! curl -s $API_URL/api/products/1 >/dev/null 2>&1; then
    echo "Error: API server not running. Start it with:"
    echo "  docker-compose up"
    exit 1
fi

# Reset product 1 to known state
echo "Resetting Product 1 to 10 units of stock..."
curl -s -X PUT $API_URL/api/products/1 \
    -H 'Content-Type: application/json' \
    -d '{"stock_quantity":10}' >/dev/null

# Check initial stock
echo "Initial stock:"
curl -s $API_URL/api/products/1 | jq '.stock_quantity'

echo
echo "Sending 8 concurrent requests to reserve 2 units each (16 total, but only 10 available)..."
echo "If race conditions exist, some requests may succeed when they shouldn't."
echo

# Create a temporary directory for results
TMPDIR=$(mktemp -d)
echo "Results will be stored in: $TMPDIR"

# Launch 8 concurrent requests to reserve 2 units each
for i in {1..8}; do
    (
        result=$(curl -s -X POST $API_URL/api/products/1/reserve \
            -H 'Content-Type: application/json' \
            -d '{"quantity":2}')
        echo "Request $i: $result" > "$TMPDIR/result_$i.json"
    ) &
done

# Wait for all background jobs to complete
wait

echo "=== Results ==="
successful=0
failed=0

for i in {1..8}; do
    result=$(cat "$TMPDIR/result_$i.json")
    echo "Request $i: $result"
    if echo "$result" | grep -q '"success":true'; then
        ((successful++))
    else
        ((failed++))
    fi
done

echo
echo "Summary:"
echo "  Successful reservations: $successful"
echo "  Failed reservations: $failed"
echo "  Total units attempted: $((successful * 2))"

echo
echo "Final stock:"
curl -s $API_URL/api/products/1 | jq '.stock_quantity'

echo
if [ $successful -gt 5 ]; then
    echo "⚠️  RACE CONDITION DETECTED!"
    echo "More than 5 requests succeeded (would reserve >10 units from 10 available)"
    echo "This indicates the reserve operation is not atomic."
else
    echo "✅ No obvious race condition detected in this run."
    echo "Try running the script multiple times or increase concurrency."
fi

# Cleanup
rm -rf "$TMPDIR"

echo
echo "=== Test Complete ==="
