<?php
declare(strict_types=1);

namespace InventoryApi\Config;

use PDO;
use PDOException;

function databasePath(): string
{
    $path = __DIR__ . '/../database/inventory.db';
    return $path;
}

function getConnection(): PDO
{
    $dsn = 'sqlite:' . databasePath();
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}


