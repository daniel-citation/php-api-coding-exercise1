<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ProductController.php';

use InventoryApi\Controllers\ProductController;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

$controller = new ProductController();

// Simple router for built-in PHP server
if ($path === '/api/products' && $method === 'GET') {
    $controller->index();
    exit;
}

if ($path === '/api/products' && $method === 'POST') {
    $controller->create();
    exit;
}

// Match /api/products/{id}
if (preg_match('#^/api/products/(\d+)$#', $path, $matches)) {
    $id = (int)$matches[1];
    if ($method === 'GET') {
        $controller->show($id);
        exit;
    }
    if ($method === 'PUT' || ($method === 'POST' && ($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '') === 'PUT')) {
        $controller->update($id);
        exit;
    }
    if ($method === 'DELETE') {
        $controller->delete($id);
        exit;
    }
}

// Match /api/products/{id}/reserve
if (preg_match('#^/api/products/(\d+)/reserve$#', $path, $matches) && $method === 'POST') {
    $id = (int)$matches[1];
    $controller->reserve($id);
    exit;
}

http_response_code(404);
echo json_encode([
    'error' => 'Route not found',
    'path' => $path,
    'script' => __FILE__,
]);
exit;


