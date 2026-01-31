<?php
declare(strict_types=1);

/**
 * 路由注册：集中管理前后台路由，便于维护
 */

use App\Core\Router;
use App\Controllers\SiteController;
use App\Controllers\AdminController;

function register_routes(Router $router): void {
    // 前台
    $router->add('GET', '/', [SiteController::class, 'home']);
    $router->add('GET', '/products', [SiteController::class, 'products']);
    $router->add('GET', '/product/:slug', [SiteController::class, 'productDetail']);
    $router->add('GET', '/cases', [SiteController::class, 'cases']);
    $router->add('GET', '/case/:slug', [SiteController::class, 'caseDetail']);
    $router->add('GET', '/blog', [SiteController::class, 'blog']);
    $router->add('GET', '/blog/:slug', [SiteController::class, 'blogDetail']);
    $router->add('GET', '/about', [SiteController::class, 'about']);
    $router->add('GET', '/contact', [SiteController::class, 'contact']);
    $router->add('POST', '/contact', [SiteController::class, 'contact']);
    $router->add('POST', '/inquiry', [SiteController::class, 'inquiry']);
    $router->add('GET', '/robots.txt', [SiteController::class, 'robots']);
    $router->add('GET', '/sitemap.xml', [SiteController::class, 'sitemap']);

    // 后台：登录
    $router->add('GET', '/admin/login', [AdminController::class, 'login']);
    $router->add('POST', '/admin/login', [AdminController::class, 'doLogin']);
    $router->add('GET', '/admin/logout', [AdminController::class, 'logout']);
    $router->add('GET', '/admin', [AdminController::class, 'dashboard']);
    $router->add('GET', '/admin/settings', [AdminController::class, 'settings']);
    $router->add('POST', '/admin/settings', [AdminController::class, 'saveSettings']);

    // 后台：产品
    $router->add('GET', '/admin/products', [AdminController::class, 'productList']);
    $router->add('GET', '/admin/products/create', [AdminController::class, 'productCreate']);
    $router->add('POST', '/admin/products/create', [AdminController::class, 'productStore']);
    $router->add('GET', '/admin/products/edit', [AdminController::class, 'productEdit']);
    $router->add('POST', '/admin/products/edit', [AdminController::class, 'productUpdate']);
    $router->add('GET', '/admin/products/delete', [AdminController::class, 'productDelete']);

    // 后台：产品分类
    $router->add('GET', '/admin/product-categories', [AdminController::class, 'productCategoryList']);
    $router->add('GET', '/admin/product-categories/create', [AdminController::class, 'productCategoryCreate']);
    $router->add('POST', '/admin/product-categories/create', [AdminController::class, 'productCategoryStore']);
    $router->add('GET', '/admin/product-categories/edit', [AdminController::class, 'productCategoryEdit']);
    $router->add('POST', '/admin/product-categories/edit', [AdminController::class, 'productCategoryUpdate']);
    $router->add('GET', '/admin/product-categories/delete', [AdminController::class, 'productCategoryDelete']);

    // 后台：文章分类
    $router->add('GET', '/admin/post-categories', [AdminController::class, 'postCategoryList']);
    $router->add('GET', '/admin/post-categories/create', [AdminController::class, 'postCategoryCreate']);
    $router->add('POST', '/admin/post-categories/create', [AdminController::class, 'postCategoryStore']);
    $router->add('GET', '/admin/post-categories/edit', [AdminController::class, 'postCategoryEdit']);
    $router->add('POST', '/admin/post-categories/edit', [AdminController::class, 'postCategoryUpdate']);
    $router->add('GET', '/admin/post-categories/delete', [AdminController::class, 'postCategoryDelete']);

    // 后台：案例
    $router->add('GET', '/admin/cases', [AdminController::class, 'caseList']);
    $router->add('GET', '/admin/cases/create', [AdminController::class, 'caseCreate']);
    $router->add('POST', '/admin/cases/create', [AdminController::class, 'caseStore']);
    $router->add('GET', '/admin/cases/edit', [AdminController::class, 'caseEdit']);
    $router->add('POST', '/admin/cases/edit', [AdminController::class, 'caseUpdate']);
    $router->add('GET', '/admin/cases/delete', [AdminController::class, 'caseDelete']);

    // 后台：文章
    $router->add('GET', '/admin/posts', [AdminController::class, 'postList']);
    $router->add('GET', '/admin/posts/create', [AdminController::class, 'postCreate']);
    $router->add('POST', '/admin/posts/create', [AdminController::class, 'postStore']);
    $router->add('GET', '/admin/posts/edit', [AdminController::class, 'postEdit']);
    $router->add('POST', '/admin/posts/edit', [AdminController::class, 'postUpdate']);
    $router->add('GET', '/admin/posts/delete', [AdminController::class, 'postDelete']);

    // 后台：留言与询单
    $router->add('GET', '/admin/messages', [AdminController::class, 'messageList']);
    $router->add('GET', '/admin/messages/detail', [AdminController::class, 'messageDetail']);
    $router->add('GET', '/admin/messages/delete', [AdminController::class, 'messageDelete']);
    $router->add('GET', '/admin/inquiries', [AdminController::class, 'inquiryList']);
    $router->add('GET', '/admin/inquiries/detail', [AdminController::class, 'inquiryDetail']);
    $router->add('GET', '/admin/inquiries/status', [AdminController::class, 'inquiryUpdateStatus']);
    $router->add('GET', '/admin/inquiries/delete', [AdminController::class, 'inquiryDelete']);
    $router->add('GET', '/admin/inquiries/export', [AdminController::class, 'inquiryExport']);

    // 后台：员工
    $router->add('GET', '/admin/staff', [AdminController::class, 'staffList']);
    $router->add('GET', '/admin/staff/create', [AdminController::class, 'staffCreate']);
    $router->add('POST', '/admin/staff/create', [AdminController::class, 'staffStore']);
    $router->add('GET', '/admin/staff/edit', [AdminController::class, 'staffEdit']);
    $router->add('POST', '/admin/staff/edit', [AdminController::class, 'staffUpdate']);
    $router->add('GET', '/admin/staff/delete', [AdminController::class, 'staffDelete']);

    // 后台：个人资料、媒体、AJAX
    $router->add('GET', '/admin/profile', [AdminController::class, 'profile']);
    $router->add('POST', '/admin/profile/update', [AdminController::class, 'profileUpdate']);
    $router->add('GET', '/admin/media', [AdminController::class, 'mediaList']);
    $router->add('GET', '/admin/media/delete', [AdminController::class, 'mediaDelete']);
    $router->add('POST', '/admin/upload-image', [AdminController::class, 'uploadImage']);
    $router->add('GET', '/admin/media-library', [AdminController::class, 'mediaLibrary']);
}
