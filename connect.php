<?php

declare(strict_types=1);

/**
 * Simple CLI DB connection check.
 *
 * Usage:
 *   DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=my_db DB_USER=root DB_PASS=secret php connect.php
 */
function envOrDefault(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}

$host = envOrDefault('DB_HOST');
$port = envOrDefault('DB_PORT', '3306');
$dbName = envOrDefault('DB_NAME');
$user = envOrDefault('DB_USER');
$pass = envOrDefault('DB_PASS', '');

$missing = [];
foreach (['DB_HOST' => $host, 'DB_NAME' => $dbName, 'DB_USER' => $user] as $key => $value) {
    if ($value === null || $value === '') {
        $missing[] = $key;
    }
}

if ($missing !== []) {
    fwrite(STDERR, "Missing required environment variables: " . implode(', ', $missing) . PHP_EOL);
    fwrite(STDERR, "Example:" . PHP_EOL);
    fwrite(STDERR, "DB_HOST=127.0.0.1 DB_PORT=3306 DB_NAME=my_db DB_USER=root DB_PASS=secret php connect.php" . PHP_EOL);
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbName);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    fwrite(STDOUT, "Connection successful." . PHP_EOL);
    fwrite(STDOUT, "MySQL version: {$version}" . PHP_EOL);
} catch (PDOException $exception) {
    fwrite(STDERR, "Connection failed: {$exception->getMessage()}" . PHP_EOL);
    exit(2);
}
