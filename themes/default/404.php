<?php
/**
 * 页面模板：404 错误页面
 * 作用：展示页面未找到的错误提示与返回首页按钮。
 * 用途：当访问不存在的页面时显示。
 * 变量：无特殊变量依赖。
 */
?>
<section class="py-20 lg:py-32">
    <div class="container mx-auto px-4 lg:px-8 text-center">
        <div class="max-w-lg mx-auto">
            <h1 class="text-8xl lg:text-9xl font-bold text-gray-200 mb-4">404</h1>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-8">The page you are looking for does not exist or has been moved.</p>
            <a href="<?= url('/') ?>" 
               class="inline-flex items-center px-8 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition-colors shadow-md">
                <i class="fas fa-home mr-2"></i>
                Go Home
            </a>
        </div>
    </div>
</section>
