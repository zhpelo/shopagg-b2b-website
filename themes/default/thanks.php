<?php
/**
 * 页面模板：感谢页面
 * 作用：展示表单提交成功的确认提示与后续引导。
 * 用途：用于联系表单/询盘提交后的反馈页。
 * 变量：无特殊变量依赖。
 */
?>
<section class="py-12 lg:py-20">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="max-w-xl mx-auto text-center">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8 lg:p-12">
                <div class="w-20 h-20 mx-auto mb-6 flex items-center justify-center rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-4xl"></i>
                </div>
                
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-4">Inquiry Sent Successfully!</h1>
                <p class="text-gray-600 mb-6">
                    Thank you for your interest. Our team will review your requirements and get back to you soon.
                </p>
                
                <div class="bg-blue-50 rounded-xl p-4 mb-8">
                    <p class="text-blue-800 font-medium">
                        Expected Response Time: We usually reply within 24 hours during business days.
                    </p>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?= url('/') ?>" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition-colors shadow-md">
                        <i class="fas fa-home mr-2"></i>
                        Back to Home
                    </a>
                    <a href="<?= url('/products') ?>" 
                       class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition-colors">
                        <i class="fas fa-box mr-2"></i>
                        View More Products
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
