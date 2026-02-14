<section class="section">
    <div class="container">
        <section class="section about-hero">
            <div class="container">
                <div class="columns is-vcentered">
                    <div class="column is-6">
                        <p class="about-eyebrow"><?= h(t('about_profile')) ?></p>
                        <h1 class="about-title"><?= h($site['name'] ?? 'Company') ?></h1>
                        <p class="about-subtitle"><?= h($site['tagline'] ?? t('about_gen_info')) ?></p>
                        <div class="about-kpis">
                            <div class="about-kpi">
                                <span class="about-kpi-label"><?= h(t('about_rating')) ?></span>
                                <span class="about-kpi-value"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                            </div>
                            <div class="about-kpi">
                                <span class="about-kpi-label"><?= h(t('about_resp_time')) ?></span>
                                <span class="about-kpi-value"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                            </div>
                            <div class="about-kpi">
                                <span class="about-kpi-label"><?= h(t('about_year')) ?></span>
                                <span class="about-kpi-value"><?= h($site['company_year_established'] ?? '-') ?></span>
                            </div>
                        </div>
                        <div class="buttons">
                            <a class="button is-link is-medium" href="<?= url('/contact') ?>"><?= h(t('btn_send_inquiry')) ?></a>
                            <a class="button is-white is-medium" href="#company-profile"><?= h(t('about_factory_tour')) ?></a>
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
                            <h2 class="title is-4 mb-4"><?= h(t('about_profile')) ?></h2>
                            <div class="columns">
                                <div class="column is-5">
                                    <figure class="image is-4by3 mb-3 about-media">
                                        <img src="<?= ( $site['og_image'] ? $site['og_image'] : 'https://devtool.tech/api/placeholder/400/300') ?>" alt="Company Profile">
                                    </figure>
                                    <a class="button is-white is-fullwidth is-outlined" href="#company-show"><?= h(t('about_factory_tour')) ?></a>
                                </div>
                                <div class="column is-7">
                                    <table class="table is-fullwidth is-narrow is-borderless mt-0 about-table">
                                        <tbody>
                                            <tr>
                                                <td class="has-text-grey p-2" width="40%"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_biz_type')) ?>:</td>
                                                <td><?= h($site['company_business_type'] ?? 'Trading Company') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey p-2"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_main_products')) ?>:</td>
                                                <td class="has-text-link"><?= h($site['company_main_products'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey p-2"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_year')) ?>:</td>
                                                <td><?= h($site['company_year_established'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_employees')) ?>:</td>
                                                <td><?= h($site['company_employees'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey" valign="top"><?= h(t('about_address')) ?>:</td>
                                                <td><?= h($site['company_address'] ?? '-') ?></td>
                                            </tr>
                                            <tr>
                                                <td class="has-text-grey"><?= h(t('about_sgs_report')) ?>:</td>
                                                <td><span class="has-text-weight-bold"><?= h($site['company_sgs_report'] ?? '-') ?></span> <a href="#" class="has-text-link ml-2"><?= h(t('about_verify_now')) ?></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="about-divider"></div>
                                    <div class="columns is-mobile">
                                        <div class="column">
                                            <span class="has-text-grey"><?= h(t('about_rating')) ?>:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                                        </div>
                                        <div class="column">
                                            <span class="has-text-grey"><?= h(t('about_resp_time')) ?>:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
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
                                <div class="level-left"><h3 class="title is-5"><?= h(t('about_gen_info')) ?></h3></div>
                                <div class="level-right"><span class="has-text-grey">6 <?= h(t('about_sgs_verified')) ?> <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_biz_type')) ?>:</td><td><?= h($site['company_business_type'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_main_products')) ?>:</td><td class="has-text-link"><?= h($site['company_main_products'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_year')) ?>:</td><td><?= h($site['company_year_established'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_employees')) ?>:</td><td><?= h($site['company_employees'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><?= h(t('about_address')) ?>:</td><td><?= h($site['company_address'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_plant_area')) ?>:</td><td><?= h($site['company_plant_area'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_capital')) ?>:</td><td><?= h($site['company_registered_capital'] ?? '-') ?></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Trade Capacity -->
                        <div class="mb-6 about-panel about-panel-divider">
                            <div class="level mb-4">
                                <div class="level-left"><h3 class="title is-5"><?= h(t('about_trade_cap')) ?></h3></div>
                                <div class="level-right"><span class="has-text-grey">7 <?= h(t('about_sgs_verified')) ?> <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_main_markets')) ?>:</td><td><?= h($site['company_main_markets'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_trade_staff')) ?>:</td><td><?= h($site['company_trade_staff'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_incoterms')) ?>:</td><td><?= h($site['company_incoterms'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_payment')) ?>:</td><td><?= h($site['company_payment_terms'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_lead_time')) ?>:</td><td><?= h($site['company_lead_time'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_overseas')) ?>:</td><td><?= h($site['company_overseas_agent'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><?= h(t('about_export_year')) ?>:</td><td><?= h($site['company_export_year'] ?? '-') ?></td></tr>
                                    <tr><td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_port')) ?>:</td><td><?= h($site['company_nearest_port'] ?? '-') ?></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- R&D Capacity -->
                        <div class="mb-6 about-panel about-panel-divider">
                            <div class="level mb-4">
                                <div class="level-left"><h3 class="title is-5"><?= h(t('about_rd_cap')) ?></h3></div>
                                <div class="level-right"><span class="has-text-grey"><?= h(t('about_all_verified')) ?> <i class="fas fa-check-circle has-text-success"></i></span></div>
                            </div>
                            <table class="table is-fullwidth about-table">
                                <tbody>
                                    <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i><?= h(t('about_rd_engineers')) ?>:</td><td><?= h($site['company_rd_engineers'] ?? '-') ?></td></tr>
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
                            <h3 class="title is-5 mb-4"><?= h(t('about_corp_show')) ?></h3>
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
                            <h3 class="title is-5 mb-4"><?= h(t('about_certificates')) ?></h3>
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
                                <p class="about-sidebar-title"><?= h(t('about_contact_provider')) ?></p>
                                <button class="button is-link is-fullwidth mb-3" onclick="location.href='<?= url('/contact') ?>'">
                                    <?= h(t('btn_send_inquiry')) ?>
                                </button>
                                <?php
                                    $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                                ?>
                                <?php if (!empty($waDigits)): ?>
                                    <a class="button is-white is-outlined is-fullwidth" href="https://wa.me/<?= h($waDigits) ?>" target="_blank">
                                        <span class="icon has-text-success"><i class="fab fa-whatsapp"></i></span>
                                        <span><?= h(t('chat_now')) ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="about-sidebar-card about-sidebar-meta">
                                <p class="about-sidebar-label"><?= h(t('about_biz_type')) ?></p>
                                <p class="about-sidebar-value"><?= h($site['company_business_type'] ?? '-') ?></p>
                                <p class="about-sidebar-label"><?= h(t('about_main_products')) ?></p>
                                <p class="about-sidebar-value"><?= h($site['company_main_products'] ?? '-') ?></p>
                                <p class="about-sidebar-label"><?= h(t('about_main_markets')) ?></p>
                                <p class="about-sidebar-value"><?= h($site['company_main_markets'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
