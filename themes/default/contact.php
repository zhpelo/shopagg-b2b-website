<?php
/**
 * 页面模板：联系我们
 * 作用：展示联系信息与询盘表单，便于客户快速提交需求。
 * 变量：$site（站点设置，包含联系方式与公司简介）。
 * 注意：表单提交到 /contact 并包含 CSRF 校验。
 */
?>
<section class="py-12 lg:py-16 bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-100 min-h-screen">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Contact Info -->
            <div class="lg:w-5/12">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 lg:p-8">
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2"><?= h(block('page_contact', 'label')) ?></p>
                    <h1 class="text-3xl font-bold text-gray-900 mb-3"><?= h(block('page_contact', 'heading')) ?></h1>
                    <p class="text-gray-600 mb-8"><?= h($site['company_bio'] ?? '') ?></p>
                    
                    <div class="space-y-4 mb-8">
                        <?php if (!empty($site['company_address'])): ?>
                            <a href="https://goo.gl/maps/<?= h($site['company_address']) ?>" target="_blank" 
                               class="flex items-start text-gray-600 hover:text-brand-600 transition-colors group">
                                <span class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-50 text-brand-600 mr-4 group-hover:bg-brand-100">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <div>
                                    <span class="block text-sm text-gray-500">Address</span>
                                    <span class="font-medium"><?= h($site['company_address']) ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($site['company_email'])): ?>
                            <a href="mailto:<?= h($site['company_email']) ?>" 
                               class="flex items-start text-gray-600 hover:text-brand-600 transition-colors group">
                                <span class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-50 text-brand-600 mr-4 group-hover:bg-brand-100">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <div>
                                    <span class="block text-sm text-gray-500">Email</span>
                                    <span class="font-medium"><?= h($site['company_email']) ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                        
                        <?php if (!empty($site['company_phone'])): ?>
                            <a href="tel:<?= h($site['company_phone']) ?>" 
                               class="flex items-start text-gray-600 hover:text-brand-600 transition-colors group">
                                <span class="w-10 h-10 flex items-center justify-center rounded-full bg-brand-50 text-brand-600 mr-4 group-hover:bg-brand-100">
                                    <i class="fas fa-phone"></i>
                                </span>
                                <div>
                                    <span class="block text-sm text-gray-500">Phone</span>
                                    <span class="font-medium"><?= h($site['company_phone']) ?></span>
                                </div>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Highlights -->
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1"><?= h(block('page_contact', 'response_label')) ?></span>
                            <span class="font-bold text-gray-900"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1"><?= h(block('page_contact', 'markets_label')) ?></span>
                            <span class="font-bold text-gray-900"><?= h($site['company_main_markets'] ?? '-') ?></span>
                        </div>
                    </div>

                    <?php
                        $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                    ?>
                    <?php if (!empty($waDigits)): ?>
                        <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank" 
                           class="block w-full text-center px-6 py-3 bg-green-500 text-white font-semibold rounded-xl hover:bg-green-600 transition-colors shadow-md">
                            <i class="fab fa-whatsapp mr-2"></i>
                            <?= h(block('page_contact', 'chat_btn')) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:w-7/12">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 lg:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-2"><?= h(block('page_contact', 'form_title')) ?></h2>
                    <p class="text-gray-500 mb-8"><?= h(block('page_contact', 'form_subtitle')) ?></p>
                    
                    <form method="post" action="<?= url('/contact') ?>">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                                <input type="text" name="name" required placeholder="Full Name"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" required placeholder="example@email.com"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                                <input type="text" name="company" placeholder="Company Ltd."
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                <input type="tel" name="phone"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                            <textarea name="message" rows="6" required placeholder="Project requirements, customization, etc."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-colors resize-none"></textarea>
                        </div>
                        
                        <button type="submit" 
                                class="px-8 py-3 bg-brand-600 text-white font-semibold rounded-xl hover:bg-brand-700 transition-colors shadow-md">
                            <?= h(block('page_contact', 'form_btn')) ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
