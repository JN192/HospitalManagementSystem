<?php

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');
$hasDatabaseConfig = $dbHost !== false || $dbPort !== false || $dbName !== false || $dbUser !== false || $dbPass !== false;

if ($hasDatabaseConfig && (!$dbHost || !$dbName || !$dbUser || $dbPass === false)) {
    throw new RuntimeException('Set DB_HOST, DB_NAME, DB_USER, and DB_PASS to configure the database.');
}

define('DB_HOST', $hasDatabaseConfig ? $dbHost : 'localhost');
define('DB_PORT', $hasDatabaseConfig && $dbPort ? $dbPort : '3306');
define('DB_NAME', $hasDatabaseConfig ? $dbName : 'hms_db');
define('DB_USER', $hasDatabaseConfig ? $dbUser : 'root');
define('DB_PASS', $hasDatabaseConfig ? $dbPass : '');
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];

    if (!in_array(DB_HOST, ['localhost', '127.0.0.1'], true)) {
        if (!defined('PDO::MYSQL_ATTR_SSL_CA') || !defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
            throw new RuntimeException('The PDO MySQL driver does not support verified TLS connections.');
        }

        $caPath = getenv('DB_SSL_CA');
        $caPath = $caPath !== false && $caPath !== '' ? $caPath : '/etc/ssl/certs/ca-certificates.crt';
        if (!is_file($caPath)) {
            throw new RuntimeException('A trusted CA bundle is required for the remote database connection. Set DB_SSL_CA to its file path.');
        }

        $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}
