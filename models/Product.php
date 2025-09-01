<?php
declare(strict_types=1);

namespace InventoryApi\Models;

use DateTimeImmutable;
use Exception;
use PDO;

require_once __DIR__ . '/../config/database.php';

use function InventoryApi\Config\getConnection;

class Product
{
    public static function getAll(): array
    {
        $pdo = getConnection();
        $sql = "SELECT id, name, price, stock_quantity, created_at, updated_at FROM products WHERE name NOT LIKE '[DELETED] %' ORDER BY id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array
    {
        $pdo = getConnection();
        $sql = "SELECT id, name, price, stock_quantity, created_at, updated_at FROM products WHERE id = ? AND name NOT LIKE '[DELETED] %'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $name, ?float $price, int $stockQuantity): int
    {
        $pdo = getConnection();
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $sql = "INSERT INTO products (name, price, stock_quantity, created_at, updated_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute([$name, $price, $stockQuantity, $now, $now]);
        if (!$ok) {
            $info = $stmt->errorInfo();
            throw new Exception('Insert failed: ' . json_encode($info));
        }
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $fields): bool
    {
        $pdo = getConnection();
        $assignments = [];
        $params = [];
        foreach ($fields as $key => $value) {
            $assignments[] = $key . ' = ?';
            $params[] = $value;
        }
        $assignments[] = 'updated_at = ?';
        $params[] = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $params[] = $id;
        $sql = 'UPDATE products SET ' . implode(', ', $assignments) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $ok = $stmt->execute($params);
        if (!$ok) {
            $info = $stmt->errorInfo();
            throw new Exception('Update failed: ' . json_encode($info));
        }
        return $stmt->rowCount() > 0;
    }

    public static function reserve(int $id, int $quantity): array
    {
        $pdo1 = getConnection();
        $stmt1 = $pdo1->prepare('SELECT stock_quantity FROM products WHERE id = ?');
        $stmt1->execute([$id]);
        $row = $stmt1->fetch();
        if (!$row) {
            return ['success' => false, 'error' => 'Product not found'];
        }

        $current = (int)$row['stock_quantity'];
        if ($current < $quantity) {
            return ['success' => false, 'error' => 'Insufficient stock', 'available' => $current];
        }

        $newQuantity = $current - $quantity;
        $pdo2 = getConnection();
        $stmt2 = $pdo2->prepare('UPDATE products SET stock_quantity = ?, updated_at = ? WHERE id = ?');
        $ok = $stmt2->execute([$newQuantity, (new DateTimeImmutable())->format('Y-m-d H:i:s'), $id]);
        if (!$ok) {
            $info = $stmt2->errorInfo();
            return ['success' => false, 'error' => 'Reservation failed', 'details' => $info];
        }
        return ['success' => true, 'remaining_stock' => $newQuantity];
    }

    public static function softDelete(int $id): bool
    {
        $pdo = getConnection();
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $stmt = $pdo->prepare("UPDATE products SET name = '[DELETED] ' || name, stock_quantity = 0, updated_at = ? WHERE id = ? AND name NOT LIKE '[DELETED] %'");
        $ok = $stmt->execute([$now, $id]);
        if (!$ok) {
            $info = $stmt->errorInfo();
            throw new Exception('Delete failed: ' . json_encode($info));
        }
        return $stmt->rowCount() > 0;
    }
}


