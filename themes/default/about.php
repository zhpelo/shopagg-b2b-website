<?php
/**
 * 页面模板：关于我们
 * 作用：展示企业概况、资质数据、贸易能力与公司展示/证书。
 * 变量：$site（站点设置，包含公司信息与展示图片JSON）。
 * 注意：展示数据来自后台设置的 company_* 与 company_show_json/company_certificates_json。
 */
?>
<section class="section">
    <div class="container">
        <section class="section about-hero">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-6">
                        <p class="about-eyebrow">Company Profile</p>
                        <h1 class="about-title"><?= h($site['name'] ?? 'Company') ?></h1>
                        <p class="about-subtitle"><?= h($site['tagline'] ?? 'General Information') ?></p>
                        <div class="about-kpis">
                            <div class="about-kpi">
                                <span class="about-kpi-label">Rating</span>
                                <span class="about-kpi-value"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                            </div>
                            <div class="about-kpi">
                                <span class="about-kpi-label">Avg. Response Time</span>
                                <span class="about-kpi-value"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                            </div>
                            <div class="about-kpi">
                                <span class="about-kpi-label">Year of Establishment</span>
                                <span class="about-kpi-value"><?= h($site['company_year_established'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="buttons">
                            <a class="button is-link is-medium" href="<?= url('/contact') ?>">Send My Inquiry</a>
                            <a class="button is-white is-medium" href="#company-profile">Book a Factory Tour</a>
                        </div>
                    </div>
                    <div class="column is-6">
                        <div class="about-hero-media">
                            <img src="<?= ($site['og_image'] ? $site['og_image'] : 'https://devtool.tech/api/placeholder/900/600') ?>" alt="Company Overview">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section about-main">
            <div class="container">
                <div class="columns">
                    <!-- 左侧内容 -->
                    <div class="column is-9">
                        <!-- Company Profile -->
                        <div class="mb-6 about-panel" id="company-profile">
                            <h2 class="title is-4 mb-4">Company Profile</h2>
                            <div class="columns">
                                <div class="column is-5">
                                    <figure class="image is-4by3 mb-3 about-media">
                                        <img src="<?= ( $site['og_image'] ? $site['og_image'] : 'https://devtool.tech/api/placeholder/400/300') ?>" alt="Company Profile">
                                    </figure>
                                    <a class="button is-white is-fullwidth is-outlined" href="#company-show">Book a Factory Tour</a>
                                </div>
                                <div class="column is-7">
                                    <table class="table is-fullwidth is-narrow is-borderless mt-0 about-table">
                                        <tbody>
                                            <tr>
                                                <td class="has-text-grey p-2" width="40%"><i class="fas fa-check has-text-success mr-2"></i>Business Type:</td>
                                                <td><?= h($site['company_business_type'] ?? 'Trading Company') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey p-2"><i class="fas fa-check has-text-success mr-2"></i>Main Products:</td>
                                                <td class="has-text-link"><?= h($site['company_main_products'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey p-2"><i class="fas fa-check has-text-success mr-2"></i>Year of Establishment:</td>
                                                <td><?= h($site['company_year_established'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Number of Employees:</td>
                                                <td><?= h($site['company_employees'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey" valign="top">Address:</td>
                                                <td><?= h($site['company_address'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey">SGS Audit Report No.:</td>
                                                <td><span class="has-text-weight-bold"><?= h($site['company_sgs_report'] ?? '-') ?></span> <a href="#" class="has-text-link ml-2">Verify Now</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="about-divider"></div>
                                    <div class="columns is-mobile">
                                        <div class="column">
                                            <span class="has-text-grey">Rating:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                                        </div>
                                        <div class="column">
                                            <span class="has-text-grey">Avg. Response Time:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="content mt-4 has-text-justified about-bio">
                                <?= nl2br(h($site['company_bio'] ?? '')) ?>
                            </div>
                        </div>

                        <!-- General Information -->
                        <div class="mb-6 about-panel about-panel-divider">
                            <div class="level mb-4">
                                <div class="level-left"><h3 class="title is-5">General Information</h3></div>
                                <div class="level-right"><span class="has-text-grey">6 items verified by SGS <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Business Type:</td><td><?= h($site['company_business_type'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Main Products:</td><td class="has-text-link"><?= h($site['company_main_products'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Year of Establishment:</td><td><?= h($site['company_year_established'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Number of Employees:</td><td><?= h($site['company_employees'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey">Address:</td><td><?= h($site['company_address'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Plant Area:</td><td><?= h($site['company_plant_area'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Registered Capital:</td><td><?= h($site['company_registered_capital'] ?? '-') ?></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Trade Capacity -->
                        <div class="mb-6 about-panel about-panel-divider">
                            <div class="level mb-4">
                                <div class="level-left"><h3 class="title is-5">Trade Capacity</h3></div>
                                <div class="level-right"><span class="has-text-grey">7 items verified by SGS <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Main Markets:</td><td><?= h($site['company_main_markets'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Number of Trade Staff:</td><td><?= h($site['company_trade_staff'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Incoterms:</td><td><?= h($site['company_incoterms'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Terms of Payment:</td><td><?= h($site['company_payment_terms'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Average Lead Time:</td><td><?= h($site['company_lead_time'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Overseas Agent/Branch:</td><td><?= h($site['company_overseas_agent'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey">Export Year:</td><td><?= h($site['company_export_year'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Nearest Port:</td><td><?= h($site['company_nearest_port'] ?? '-') ?></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- R&D Capacity -->
                        <div class="mb-6 about-panel about-panel-divider">
                            <div class="level mb-4">
                                <div class="level-left"><h3 class="title is-5">R&D Capacity</h3></div>
                                <div class="level-right"><span class="has-text-grey">All information verified by SGS <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>R&D Engineers:</td><td><?= h($site['company_rd_engineers'] ?? '-') ?></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Company Show -->
                        <?php 
                        $showJson = $site['company_show_json'] ?? '[]';
                        $showItems = is_array($showJson) ? $showJson : json_decode($showJson, true);
                        if (!empty($showItems) && is_array($showItems)): 
                        ?>
                        <div class="mb-6 about-panel about-panel-divider" id="company-show">
                            <h3 class="title is-5 mb-4">Company Show</h3>
                            <div class="columns is-multiline">
                                <?php foreach ($showItems as $item): 
                                    if (empty($item['img'])) continue;
                                ?>
                                    <div class="column is-3">
                                        <div class="about-gallery-card">
                                            <figure class="image is-4by3">
                                                <img src="<?= asset_url(h($item['img'])) ?>" alt="<?= h($item['title'] ?? '') ?>">
                                            </figure>
                                            <p class="has-text-centered line-clamp-1"><?= h($item['title'] ?? '') ?></p>
                                        </div>
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
                        <div class="mb-6 about-panel about-panel-divider">
                            <h3 class="title is-5 mb-4">Certificates</h3>
                            <div class="columns is-multiline">
                                <?php foreach ($certItems as $item): 
                                    if (empty($item['img'])) continue;
                                ?>
                                    <div class="column is-3">
                                        <div class="about-gallery-card is-certificate">
                                            <figure class="image is-4by3">
                                                <img src="<?= asset_url(h($item['img'])) ?>" alt="<?= h($item['title'] ?? '') ?>">
                                            </figure>
                                            <p class="has-text-centered line-clamp-1"><?= h($item['title'] ?? '') ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- 右侧边栏 (可选) -->
                    <div class="column is-3">
                        <div class="about-sidebar">
                            <div class="about-sidebar-card">
                                <p class="about-sidebar-title">Contact Provider</p>
                                <button class="button is-link is-fullwidth mb-3" onclick="location.href='<?= url('/contact') ?>'">
                                    Send My Inquiry
                                </button>
                                <?php
                                    $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                                ?>
                                <?php if (!empty($waDigits)): ?>
                                    <a class="button is-white is-outlined is-fullwidth" href="https://wa.me/<?= h($waDigits) ?>" target="_blank">
                                        <span class="icon has-text-success"><i class="fab fa-whatsapp"></i></span>
                                        <span>Chat Now</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="about-sidebar-card about-sidebar-meta">
                                <p class="about-sidebar-label">Business Type</p>
                                <p class="about-sidebar-value"><?= h($site['company_business_type'] ?? '-') ?></p>
                                <p class="about-sidebar-label">Main Products</p>
                                <p class="about-sidebar-value"><?= h($site['company_main_products'] ?? '-') ?></p>
                                <p class="about-sidebar-label">Main Markets</p>
                                <p class="about-sidebar-value"><?= h($site['company_main_markets'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
