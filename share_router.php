<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$appPath = '/HospitalManagementSystem';

if ($path === $appPath || $path === "$appPath/" || $path === "$appPath/index.php") {
    require __DIR__ . '/index.php';
    return true;
}

if ($path === "$appPath/css/style.css") {
    header('Content-Type: text/css; charset=utf-8');
    readfile(__DIR__ . '/css/style.css');
    return true;
}

http_response_code(404);
echo 'Not Found';
return true;
