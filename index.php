<?php

/**
 * SHOPAGG B2B Website - Entry Point
 * @copyright Copyright (c) 2015–2026 SHOPAGG. All rights reserved.
 * @license   MIT License
 */

declare(strict_types=1);

// 运行环境
session_start();
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// 根目录与二级目录（子目录部署）
define('APP_ROOT', rtrim(str_replace('\\', '/', realpath(__DIR__)), '/'));
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = rtrim(dirname($scriptName), '/');
define('APP_BASE_PATH', ($basePath === '' || $basePath === '/') ? '' : '/' . ltrim($basePath, '/'));

// PSR-4 自动加载
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});

require APP_ROOT . '/app/Helpers.php';

use App\Core\Router;
use App\Core\Database;

// 数据库（首次访问时初始化 schema）
Database::getInstance();

// 语言
$langs = get_languages();
if (!empty($_GET['lang']) && isset($langs[$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$current_lang = $_SESSION['lang'] ?? (new \App\Models\Setting())->get('default_lang', 'en');

// 路由
$router = new Router();
require APP_ROOT . '/app/routes.php';
register_routes($router);
$router->run();
