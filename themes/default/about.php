<section class="section">
    <div class="container">
        <!-- 顶部 Tab 导航 (模拟图片效果) -->
        <div class="tabs is-boxed mb-6">
            <ul>
                <li><a>Product Description</a></li>
                <li class="is-active"><a>Company Info.</a></li>
            </ul>
        </div>

        <div class="columns">
            <!-- 左侧内容 -->
            <div class="column is-9">
                <!-- Company Profile -->
                <div class="mb-6">
                    <h2 class="title is-4 mb-4">Company Profile</h2>
                    <div class="columns">
                        <div class="column is-5">
                            <figure class="image is-4by3 mb-3">
                                <img src="<?= h($site['og_image'] ?? 'https://via.placeholder.com/400x300') ?>" alt="Company Profile" style="object-fit: cover; border-radius: 4px;">
                            </figure>
                            <button class="button is-white is-fullwidth border" style="border: 1px solid #dbdbdb;">Book a Factory Tour</button>
                        </div>
                        <div class="column is-7">
                            <table class="table is-fullwidth is-narrow is-borderless mt-0">
                                <tbody class="is-size-7">
                                    <tr>
                                        <td class="has-text-grey" width="40%"><i class="fas fa-check has-text-success mr-2"></i>Business Type:</td>
                                        <td><?= h($site['company_business_type'] ?? 'Trading Company') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Main Products:</td>
                                        <td class="has-text-link"><?= h($site['company_main_products'] ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>Year of Establishment:</td>
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
                            <div class="is-divider my-2" style="border-top: 1px solid #f0f0f0;"></div>
                            <div class="columns is-mobile is-size-7">
                                <div class="column">
                                    <span class="has-text-grey">Rating:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_rating'] ?? '5.0/5') ?></span>
                                </div>
                                <div class="column">
                                    <span class="has-text-grey">Avg. Response Time:</span> <span class="has-text-weight-bold ml-1"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content is-size-7 mt-4 has-text-justified">
                        <?= nl2br(h($site['company_bio'] ?? '')) ?>
                        <div class="mt-3"><a class="has-text-link">View All <i class="fas fa-chevron-down"></i></a></div>
                    </div>
                </div>

                <!-- General Information -->
                <div class="mb-6 pt-5" style="border-top: 1px solid #f0f0f0;">
                    <div class="level mb-4">
                        <div class="level-left"><h3 class="title is-5">General Information</h3></div>
                        <div class="level-right"><span class="is-size-7 has-text-grey">6 items verified <i class="fas fa-check-circle has-text-success"></i> by SGS</span></div>
                    </div>
                    <table class="table is-fullwidth is-size-7">
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
                <div class="mb-6 pt-5" style="border-top: 1px solid #f0f0f0;">
                    <div class="level mb-4">
                        <div class="level-left"><h3 class="title is-5">Trade Capacity</h3></div>
                        <div class="level-right"><span class="is-size-7 has-text-grey">7 items verified <i class="fas fa-check-circle has-text-success"></i> by SGS</span></div>
                    </div>
                    <table class="table is-fullwidth is-size-7">
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
                <div class="mb-6 pt-5" style="border-top: 1px solid #f0f0f0;">
                    <div class="level mb-4">
                        <div class="level-left"><h3 class="title is-5">R&D Capacity</h3></div>
                        <div class="level-right"><span class="is-size-7 has-text-grey">All information verified <i class="fas fa-check-circle has-text-success"></i> by SGS</span></div>
                    </div>
                    <table class="table is-fullwidth is-size-7">
                        <tbody>
                            <tr><td width="30%" class="has-text-grey"><i class="fas fa-check has-text-success mr-2"></i>R&D Engineers:</td><td><?= h($site['company_rd_engineers'] ?? '-') ?></td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Company Show -->
                <div class="mb-6 pt-5" style="border-top: 1px solid #f0f0f0;">
                    <h3 class="title is-5 mb-4">Company Show</h3>
                    <div class="columns is-multiline">
                        <?php 
                        $showItems = json_decode($site['company_show_json'] ?? '[]', true);
                        foreach ($showItems as $item): ?>
                            <div class="column is-3">
                                <figure class="image is-4by3 mb-2">
                                    <img src="<?= h($item['img']) ?>" alt="<?= h($item['title']) ?>" style="object-fit: cover; border: 1px solid #eee; border-radius: 2px;">
                                </figure>
                                <p class="is-size-7 has-text-centered"><?= h($item['title']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Certificates -->
                <div class="mb-6 pt-5" style="border-top: 1px solid #f0f0f0;">
                    <h3 class="title is-5 mb-4">Certificates</h3>
                    <div class="columns is-multiline">
                        <?php 
                        $certItems = json_decode($site['company_certificates_json'] ?? '[]', true);
                        foreach ($certItems as $item): ?>
                            <div class="column is-3">
                                <figure class="image is-4by3 mb-2">
                                    <img src="<?= h($item['img']) ?>" alt="<?= h($item['title']) ?>" style="object-fit: contain; background: #fafafa; border: 1px solid #eee; border-radius: 2px; padding: 5px;">
                                </figure>
                                <p class="is-size-7 has-text-centered"><?= h($item['title']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- 右侧边栏 (可选) -->
            <div class="column is-3">
                <div class="box is-shadowless" style="border: 1px solid #eee; position: sticky; top: 20px;">
                    <p class="has-text-weight-bold mb-3">Contact Provider</p>
                    <button class="button is-danger is-fullwidth mb-3" onclick="location.href='/contact'">Send Inquiry</button>
                    <?php
                        $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                    ?>
                    <?php if (!empty($waDigits)): ?>
                        <a class="button is-white border is-fullwidth" style="border: 1px solid #dbdbdb;" href="https://wa.me/<?= h($waDigits) ?>" target="_blank">
                            <span class="icon has-text-success"><i class="fab fa-whatsapp"></i></span>
                            <span>Chat Now</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.table.is-borderless td, .table.is-borderless th { border: none; }
.tabs.is-boxed li.is-active a { background-color: #fff; border-bottom-color: #d65a53; border-bottom-width: 2px; color: #333; font-weight: bold; }
</style>
