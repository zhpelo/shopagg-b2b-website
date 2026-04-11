<?php
declare(strict_types=1);

/**
 * 迁移: 创建设置表
 * 版本: 20260410000002
 */

return new class {
    public function up(SQLite3 $db): void {
        $db->exec('CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )');
        
        // 默认设置
        $settings = [
            // ── 基础设置 ──────────────────────────────────────────────
            'site_name'                => 'SHOPAGG B2B外贸官网',
            'site_tagline'             => '专为中国工厂打造的B2B外贸官网系统',
            'theme'                    => 'default',
            'default_lang'             => 'en',

            // ── SEO ───────────────────────────────────────────────────
            'seo_title'                => 'SHOPAGG B2B外贸官网',
            'seo_description'          => 'Professional B2B manufacturer with ISO-certified production, OEM/ODM capabilities, and reliable global export experience.',
            'seo_keywords'             => 'B2B manufacturer, OEM supplier, industrial products, global exporter',

            // ── 联系方式 ──────────────────────────────────────────────
            'company_email'            => 'sales@example.com',
            'company_phone'            => '+86-755-12345678',
            'company_address'          => 'No. 88, Industrial Park, Baoding, Hebei, China',
            'whatsapp'                 => '8675512345678',

            // ── 社交媒体 ──────────────────────────────────────────────
            'facebook'                 => '',
            'instagram'                => '',
            'linkedin'                 => '',
            'youtube'                  => '',
            'twitter'                  => '',

            // ── 公司简介 ──────────────────────────────────────────────
            'company_bio'              => 'We are a professional manufacturing and exporting company focused on quality, compliance, and fast delivery for global B2B clients. With years of industry experience, we serve buyers across 50+ countries with reliable products and dedicated after-sales support.',
            'company_business_type'    => 'Manufacturer & Trading Company',
            'company_main_products'    => 'Industrial parts, custom components, OEM products',
            'company_year_established' => '2005',
            'company_employees'        => '100-200',
            'company_plant_area'       => '8,000 m²',
            'company_registered_capital' => 'USD 2,000,000',

            // ── 资质认证 ──────────────────────────────────────────────
            'company_sgs_report'       => '123456789',
            'company_rating'           => '5.0/5',
            'company_response_time'    => '≤24h',

            // ── 贸易能力 ──────────────────────────────────────────────
            'company_main_markets'     => 'North America, Europe, Southeast Asia, Middle East',
            'company_trade_staff'      => '15',
            'company_incoterms'        => 'FOB, CIF, EXW, DDP',
            'company_payment_terms'    => 'T/T, L/C, PayPal, Western Union',
            'company_lead_time'        => '15-30 days',
            'company_overseas_agent'   => 'No',
            'company_export_year'      => '2008',
            'company_nearest_port'     => 'Tianjin Port',

            // ── 研发能力 ──────────────────────────────────────────────
            'company_rd_engineers'     => '20+',

            // ── 翻译设置 ──────────────────────────────────────────────
            'translate_enabled'        => '1',
            'translate_auto_browser'   => '0',
            'translate_languages'      => '["en","zh-CN","zh-TW","ja","ko","es","fr","de","ar"]',

            // ── 自定义代码 ────────────────────────────────────────────
            'head_code'                => '',
            'footer_code'              => '',
        ];
        
        foreach ($settings as $k => $v) {
            $stmt = $db->prepare("INSERT INTO settings (key, value) VALUES (:k, :v)");
            $stmt->bindValue(':k', $k, SQLITE3_TEXT);
            $stmt->bindValue(':v', $v, SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS settings');
    }
};
