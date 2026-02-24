<?php
/**
 * 页面模板：联系我们
 * 作用：展示联系信息与询盘表单，便于客户快速提交需求。
 * 变量：$site（站点设置，包含联系方式与公司简介）。
 * 注意：表单提交到 /contact 并包含 CSRF 校验。
 */
?>
<section class="section contact-section">
    <div class="container">
        <div class="columns">
            <div class="column is-5">
                <div class="contact-info-card">
                    <p class="contact-eyebrow">Contact</p>
                    <h1 class="title is-3 contact-title">Contact Us</h1>
                    <p class="subtitle is-6 contact-subtitle"><?= h($site['company_bio'] ?? '') ?></p>
                    <div class="content contact-details">
                        <p><span class="icon"><i class="fas fa-map-marker-alt"></i></span><strong>Address:</strong> <?= h($site['company_address'] ?? '') ?></p>
                        <p><span class="icon"><i class="fas fa-envelope"></i></span><strong>Email:</strong> <?= h($site['company_email'] ?? '') ?></p>
                        <p><span class="icon"><i class="fas fa-phone"></i></span><strong>Phone:</strong> <?= h($site['company_phone'] ?? '') ?></p>
                    </div>
                    <div class="contact-highlights">
                        <div class="contact-highlight">
                            <span class="contact-highlight-label">Avg. Response Time</span>
                            <span class="contact-highlight-value"><?= h($site['company_response_time'] ?? '≤24h') ?></span>
                        </div>
                        <div class="contact-highlight">
                            <span class="contact-highlight-label">Main Markets</span>
                            <span class="contact-highlight-value"><?= h($site['company_main_markets'] ?? '-') ?></span>
                        </div>
                    </div>
                    <?php
                        $waDigits = preg_replace('/\D+/', '', $site['whatsapp'] ?? '');
                    ?>
                    <?php if (!empty($waDigits)): ?>
                        <a class="button is-success is-fullwidth" href="https://wa.me/<?= h($waDigits) ?>" target="_blank">
                            <span class="icon"><i class="fab fa-whatsapp"></i></span>
                            <span>Chat Now</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="column is-7">
                <div class="box soft-card contact-form-card">
                    <h2 class="title is-5 mb-4">Send Message</h2>
                    <p class="has-text-grey mb-5 contact-form-desc">Project requirements, customization, etc.</p>
                    <form method="post" action="<?= url('/contact') ?>" class="contact-form">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Name</label>
                                    <div class="control">
                                        <input class="input" name="name" required placeholder="Full Name">
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Email</label>
                                    <div class="control">
                                        <input class="input" name="email" type="email" required placeholder="example@email.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="columns">
                            <div class="column">
                                <div class="field">
                                    <label class="label">Company</label>
                                    <div class="control">
                                        <input class="input" name="company" placeholder="Company Ltd.">
                                    </div>
                                </div>
                            </div>
                            <div class="column">
                                <div class="field">
                                    <label class="label">Phone</label>
                                    <div class="control">
                                        <input class="input" name="phone">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">Message</label>
                            <div class="control">
                                <textarea class="textarea" name="message" rows="6" required placeholder="Project requirements, customization, etc."></textarea>
                            </div>
                        </div>
                        <button class="button is-link is-medium" type="submit">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>