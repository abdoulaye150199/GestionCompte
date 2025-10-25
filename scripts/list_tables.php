<?php
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    echo "No .env found\n";
    exit(1);
}
$env = parse_ini_file($envFile, false, INI_SCANNER_RAW);
// fallback simple parser for lines with =
if ($env === false) {
    $env = [];
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $env[trim($k)] = trim(trim($v), "\"'");
    }
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '5432';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname='public' ORDER BY tablename;");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!$tables) {
        echo "No tables found\n";
    } else {
        echo "Tables in public schema:\n";
        foreach ($tables as $t) {
            echo " - $t\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
