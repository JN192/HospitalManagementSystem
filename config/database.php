<?php

$dbHost = getenv('DB_HOST');
$dbPort = getenv('DB_PORT');
$dbName = getenv('DB_NAME');
$dbUser = getenv('DB_USER');
$dbPass = getenv('DB_PASS');

define('DB_HOST', $dbHost === false || $dbHost === '' ? 'localhost' : $dbHost);
define('DB_PORT', $dbPort === false || $dbPort === '' ? '3306' : $dbPort);
define('DB_NAME', $dbName === false || $dbName === '' ? 'hms_db' : $dbName);
define('DB_USER', $dbUser === false || $dbUser === '' ? 'root' : $dbUser);
define('DB_PASS', $dbPass === false ? '' : $dbPass);
define('DB_CHARSET', 'utf8mb4');

function getDBConnection(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}
