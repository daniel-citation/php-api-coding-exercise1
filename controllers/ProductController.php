<?php
declare(strict_types=1);

namespace InventoryApi\Controllers;

use Exception;
use InventoryApi\Models\Product;

require_once __DIR__ . '/../models/Product.php';

class ProductController
{
    public function index(): void
    {
        try {
            $products = Product::getAll();
            echo json_encode($products);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
    {
        try {
            $product = Product::getById($id);
            if (!$product) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found', 'id' => $id]);
                return;
            }
            echo json_encode($product);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function create(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
            $name = isset($input['name']) ? trim((string)$input['name']) : '';
            $price = isset($input['price']) ? (float)$input['price'] : null;
            $stock = isset($input['stock_quantity']) ? (int)$input['stock_quantity'] : 0;

            if ($name === '') {
                http_response_code(422);
                echo json_encode(['error' => 'Name is required']);
                return;
            }

            $id = Product::create($name, $price, $stock);
            http_response_code(201);
            echo json_encode(['id' => $id]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update(int $id): void
    {
        try {
            $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
            $fields = [];
            if (isset($input['name'])) {
                $fields['name'] = trim((string)$input['name']);
            }
            if (array_key_exists('price', $input)) {
                $fields['price'] = $input['price'] !== null ? (float)$input['price'] : null;
            }
            if (array_key_exists('stock_quantity', $input)) {
                $fields['stock_quantity'] = (int)$input['stock_quantity'];
            }

            if (empty($fields)) {
                http_response_code(422);
                echo json_encode(['error' => 'No fields to update']);
                return;
            }

            $updated = Product::update($id, $fields);
            if (!$updated) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found or not updated', 'id' => $id]);
                return;
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function reserve(int $id): void
    {
        try {
            $input = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
            $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
            if ($quantity <= 0) {
                http_response_code(422);
                echo json_encode(['error' => 'Quantity must be a positive integer']);
                return;
            }

            $result = Product::reserve($id, $quantity);
            if (!$result['success']) {
                http_response_code(409);
            }
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        try {
            $deleted = Product::softDelete($id);
            if (!$deleted) {
                http_response_code(404);
                echo json_encode(['error' => 'Product not found', 'id' => $id]);
                return;
            }
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}


