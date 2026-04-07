<?php
/**
 * 页面模板：关于我们
 * 作用：展示企业概况、资质数据、贸易能力与公司展示/证书。
 * 变量：$site（站点设置，包含公司信息与展示图片JSON）。
 * 注意：展示数据来自后台设置的 company_* 与 company_show_json/company_certificates_json。
 */
?>
<!-- Hero Section -->
<section class="bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100 pt-16 pb-12">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-3">Company Profile</p>
                <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-3 font-serif"><?= h($site['name'] ?? 'Company') ?></h1>
                <p class="text-xl text-gray-600 mb-6"><?= h($site['tagline'] ?? 'General Information') ?></p>
                
                <!-- KPIs -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Rating</span>
                        <span class="text-lg font-bold text-gray-900"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Avg. Response</span>
                        <span class="text-lg font-bold text-gray-900"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
                        <span class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Established</span>
                        <span class="text-lg font-bold text-gray-900"><?= h($site['company_year_established'] ?? '-') ?></span>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <a href="<?= url('/contact') ?>" class="px-8 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors shadow-md">
                        Send My Inquiry
                    </a>
                    <a href="#company-show" class="px-8 py-3 border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:border-gray-400 transition-colors">
                        Book a Factory Tour
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="rounded-2xl overflow-hidden shadow-2xl">
                    <img src="<?= ($site['og_image'] ? $site['og_image'] : 'https://devtool.tech/api/placeholder/600/400') ?>" 
                         alt="Company Overview" 
                         class="w-full h-auto">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 lg:py-16">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Main Content -->
            <div class="lg:w-9/12 space-y-8">
                <!-- Company Profile -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8" id="company-profile">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Company Profile</h2>
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
                        <div class="lg:col-span-2">
                            <figure class="rounded-xl overflow-hidden shadow-md aspect-[4/3]">
                                <img src="<?= ($site['og_image'] ? $site['og_image'] : 'https://devtool.tech/api/placeholder/400/300') ?>" 
                                     alt="Company Profile" 
                                     class="w-full h-full object-cover">
                            </figure>
                            <a href="#company-show" class="mt-4 block w-full text-center px-4 py-2 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 transition-colors">
                                Book a Factory Tour
                            </a>
                        </div>
                        <div class="lg:col-span-3">
                            <table class="w-full text-sm">
                                <tbody class="divide-y divide-gray-100">
                                    <tr>
                                        <td class="py-3 text-gray-500 w-2/5"><i class="fas fa-check text-green-500 mr-2"></i>Business Type:</td>
                                        <td class="py-3 font-medium text-gray-900"><?= h($site['company_business_type'] ?? 'Trading Company') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Main Products:</td>
                                        <td class="py-3 font-medium text-brand-600"><?= h($site['company_main_products'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Year Established:</td>
                                        <td class="py-3 font-medium text-gray-900"><?= h($site['company_year_established'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Employees:</td>
                                        <td class="py-3 font-medium text-gray-900"><?= h($site['company_employees'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-gray-500 align-top">Address:</td>
                                        <td class="py-3 font-medium text-gray-900"><?= h($site['company_address'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 text-gray-500">SGS Audit No.:</td>
                                        <td class="py-3 font-bold text-gray-900">
                                            <?= h($site['company_sgs_report'] ?? '-') ?> 
                                            <a href="#" class="text-brand-600 hover:underline ml-2">Verify Now</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr class="border-gray-100 my-4">
                            <div class="flex gap-8">
                                <div>
                                    <span class="text-gray-500">Rating:</span>
                                    <span class="font-bold text-gray-900 ml-2"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Avg. Response Time:</span>
                                    <span class="font-bold text-gray-900 ml-2"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="prose max-w-none text-gray-600 leading-relaxed">
                        <?= nl2br(h($site['company_bio'] ?? '')) ?>
                    </div>
                </div>

                <!-- General Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                    <div class="flex flex-wrap justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">General Information</h3>
                        <span class="text-sm text-gray-500">6 items verified by SGS <i class="fas fa-check-circle text-green-500 ml-1"></i></span>
                    </div>
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-3 text-gray-500 w-1/3"><i class="fas fa-check text-green-500 mr-2"></i>Business Type:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_business_type'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Main Products:</td>
                                <td class="py-3 font-medium text-brand-600"><?= h($site['company_main_products'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Year Established:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_year_established'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Employees:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_employees'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500">Address:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_address'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Plant Area:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_plant_area'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Registered Capital:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_registered_capital'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Trade Capacity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                    <div class="flex flex-wrap justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Trade Capacity</h3>
                        <span class="text-sm text-gray-500">7 items verified by SGS <i class="fas fa-check-circle text-green-500 ml-1"></i></span>
                    </div>
                    <table class="w-full">
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="py-3 text-gray-500 w-1/3"><i class="fas fa-check text-green-500 mr-2"></i>Main Markets:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_main_markets'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Trade Staff:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_trade_staff'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Incoterms:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_incoterms'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Payment Terms:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_payment_terms'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Avg. Lead Time:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_lead_time'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Overseas Agent:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_overseas_agent'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500">Export Year:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_export_year'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="py-3 text-gray-500"><i class="fas fa-check text-green-500 mr-2"></i>Nearest Port:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_nearest_port'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- R&D Capacity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                    <div class="flex flex-wrap justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">R&D Capacity</h3>
                        <span class="text-sm text-gray-500">All information verified by SGS <i class="fas fa-check-circle text-green-500 ml-1"></i></span>
                    </div>
                    <table class="w-full">
                        <tbody>
                            <tr>
                                <td class="py-3 text-gray-500 w-1/3"><i class="fas fa-check text-green-500 mr-2"></i>R&D Engineers:</td>
                                <td class="py-3 font-medium text-gray-900"><?= h($site['company_rd_engineers'] ?? '-') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Company Show -->
                <?php 
                $showJson = $site['company_show_json'] ?? '[]';
                $showItems = is_array($showJson) ? $showJson : json_decode($showJson, true);
                if (!empty($showItems) && is_array($showItems)): 
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8" id="company-show">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Company Show</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($showItems as $item): 
                            if (empty($item['img'])) continue;
                        ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-3">
                                <figure class="aspect-[4/3] rounded-lg overflow-hidden mb-2">
                                    <img src="<?= asset_url(h($item['img'])) ?>" 
                                         alt="<?= h($item['title'] ?? '') ?>" 
                                         class="w-full h-full object-cover">
                                </figure>
                                <p class="text-sm text-gray-700 text-center line-clamp-1"><?= h($item['title'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Certificates -->
                <?php 
                $certJson = $site['company_certificates_json'] ?? '[]';
                $certItems = is_array($certJson) ? $certJson : json_decode($certJson, true);
                if (!empty($certItems) && is_array($certItems)): 
                ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Certificates</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php foreach ($certItems as $item): 
                            if (empty($item['img'])) continue;
                        ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-3">
                                <figure class="aspect-[4/3] rounded-lg overflow-hidden mb-2 bg-gray-50 border border-dashed border-gray-200">
                                    <img src="<?= asset_url(h($item['img'])) ?>" 
                                         alt="<?= h($item['title'] ?? '') ?>" 
                                         class="w-full h-full object-contain p-2">
                                </figure>
                                <p class="text-sm text-gray-700 text-center line-clamp-1"><?= h($item['title'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="lg:w-3/12">
                <div class="sticky top-24 space-y-6">
                    <!-- Contact Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Contact Provider</h3>
                        <a href="<?= url('/contact') ?>" 
                           class="block w-full text-center px-6 py-3 bg-brand-600 text-white font-semibold rounded-lg hover:bg-brand-700 transition-colors mb-3">
                            Send My Inquiry
                        </a>
                        <?php
                            $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                        ?>
                        <?php if (!empty($waDigits)): ?>
                            <a href="https://wa.me/<?= h($waDigits) ?>" target="_blank"
                               class="block w-full text-center px-6 py-3 border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 transition-colors">
                                <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                                Chat Now
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Company Meta -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                        <div class="space-y-4">
                            <div>
                                <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Business Type</span>
                                <span class="font-semibold text-gray-900"><?= h($site['company_business_type'] ?? '-') ?></span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Main Products</span>
                                <span class="font-semibold text-gray-900"><?= h($site['company_main_products'] ?? '-') ?></span>
                            </div>
                            <div>
                                <span class="block text-xs text-gray-400 uppercase tracking-wider mb-1">Main Markets</span>
                                <span class="font-semibold text-gray-900"><?= h($site['company_main_markets'] ?? '-') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
