<?php

/**
 * SHOPAGG B2B Website - A Professional B2B Website Platform
 *
 * @copyright  Copyright (c) 2015–2026 SHOPAGG. All rights reserved.
 * @license    MIT License
 * @link       https://www.shopagg.com
 * @author     SHOPAGG
 * @version    1.0.0
 */


declare(strict_types=1);

/**
 * Entry point for MVC B2B Website
 */

session_start();
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// Simple PSR-4 Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load global helpers
require __DIR__ . '/app/Helpers.php';

use App\Core\Router;
use App\Core\Database;
use App\Controllers\SiteController;
use App\Controllers\AdminController;

// Init DB (Schema auto-init if not exists)
$db = Database::getInstance();

// Multi-language setup
$langs = get_languages();
if (isset($_GET['lang']) && isset($langs[$_GET['lang']])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$settingModel = new \App\Models\Setting();
$current_lang = $_SESSION['lang'] ?? $settingModel->get('default_lang', 'en');

// Routing
$router = new Router();

// Frontend Routes
$router->add('GET', '/', [SiteController::class, 'home']);
$router->add('GET', '/products', [SiteController::class, 'products']);
$router->add('GET', '/product/:slug', [SiteController::class, 'productDetail']);
$router->add('GET', '/cases', [SiteController::class, 'cases']);
$router->add('GET', '/case/:slug', [SiteController::class, 'caseDetail']);
$router->add('GET', '/blog', [SiteController::class, 'blog']);
$router->add('GET', '/blog/:slug', [SiteController::class, 'blogDetail']);
$router->add('GET', '/contact', [SiteController::class, 'contact']);
$router->add('POST', '/contact', [SiteController::class, 'contact']);
$router->add('POST', '/inquiry', [SiteController::class, 'inquiry']);
$router->add('GET', '/robots.txt', [SiteController::class, 'robots']);
$router->add('GET', '/sitemap.xml', [SiteController::class, 'sitemap']);

// Admin Routes
$router->add('GET', '/admin/login', [AdminController::class, 'login']);
$router->add('POST', '/admin/login', [AdminController::class, 'doLogin']);
$router->add('GET', '/admin/logout', [AdminController::class, 'logout']);
$router->add('GET', '/admin', [AdminController::class, 'dashboard']);
$router->add('GET', '/admin/settings', [AdminController::class, 'settings']);
$router->add('POST', '/admin/settings', [AdminController::class, 'saveSettings']);

// Admin CRUD for Products
$router->add('GET', '/admin/products', [AdminController::class, 'productList']);
$router->add('GET', '/admin/products/create', [AdminController::class, 'productCreate']);
$router->add('POST', '/admin/products/create', [AdminController::class, 'productStore']);
$router->add('GET', '/admin/products/edit', [AdminController::class, 'productEdit']);
$router->add('POST', '/admin/products/edit', [AdminController::class, 'productUpdate']);
$router->add('GET', '/admin/products/delete', [AdminController::class, 'productDelete']);

// Admin CRUD for Categories
$router->add('GET', '/admin/categories', [AdminController::class, 'categoryList']);
$router->add('GET', '/admin/categories/create', [AdminController::class, 'categoryCreate']);
$router->add('POST', '/admin/categories/create', [AdminController::class, 'categoryStore']);
$router->add('GET', '/admin/categories/edit', [AdminController::class, 'categoryEdit']);
$router->add('POST', '/admin/categories/edit', [AdminController::class, 'categoryUpdate']);
$router->add('GET', '/admin/categories/delete', [AdminController::class, 'categoryDelete']);

// Admin CRUD for Cases
$router->add('GET', '/admin/cases', [AdminController::class, 'caseList']);
$router->add('GET', '/admin/cases/create', [AdminController::class, 'caseCreate']);
$router->add('POST', '/admin/cases/create', [AdminController::class, 'caseStore']);
$router->add('GET', '/admin/cases/edit', [AdminController::class, 'caseEdit']);
$router->add('POST', '/admin/cases/edit', [AdminController::class, 'caseUpdate']);
$router->add('GET', '/admin/cases/delete', [AdminController::class, 'caseDelete']);

// Admin CRUD for Posts
$router->add('GET', '/admin/posts', [AdminController::class, 'postList']);
$router->add('GET', '/admin/posts/create', [AdminController::class, 'postCreate']);
$router->add('POST', '/admin/posts/create', [AdminController::class, 'postStore']);
$router->add('GET', '/admin/posts/edit', [AdminController::class, 'postEdit']);
$router->add('POST', '/admin/posts/edit', [AdminController::class, 'postUpdate']);
$router->add('GET', '/admin/posts/delete', [AdminController::class, 'postDelete']);

// Messages & Inquiries
$router->add('GET', '/admin/messages', [AdminController::class, 'messageList']);
$router->add('GET', '/admin/inquiries', [AdminController::class, 'inquiryList']);

// AJAX
$router->add('POST', '/admin/upload-image', [AdminController::class, 'uploadImage']);
$router->add('GET', '/admin/media-library', [AdminController::class, 'mediaLibrary']);

// Run the router
$router->run();
