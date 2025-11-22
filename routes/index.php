<?php
session_start();

// Xử lý autoload và routes
require_once __DIR__ . '/configs/env.php';
require_once __DIR__ . '/configs/helper.php';

spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/controllers/$class.php",
        __DIR__ . "/models/$class.php",
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// controller = c, action = a
$controllerName = isset($_GET['c']) ? $_GET['c'] . 'Controller' : 'DashboardController';
$action = $_GET['a'] ?? 'index';

if (!class_exists($controllerName)) {
    die("Controller $controllerName không tồn tại");
}

$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    die("Action $action không tồn tại trong $controllerName");
}

$controller->$action();
