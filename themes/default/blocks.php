<?php
/**
 * 默认主题 - 区块配置文件
 * 
 * 定义所有可在后台自定义的模板区块内容。
 * 每个区块包含：label（后台显示名）、fields（字段列表）。
 * 字段类型：text / textarea / image / icon / color / select / repeater
 * 
 * 前台模板通过 block('区块key', '字段key') 读取值。
 * 用户修改保存在 storage/blocks/{theme}.php，不修改本文件。
 */

return [

    // ============================================================
    // 首页 - 优势卖点区块（3列）
    // ============================================================
    'home_value_props' => [
        'label' => 'Homepage - Value Proposition',
        'description' => '首页轮播下方的 3 列优势卖点',
        'fields' => [
            'item1_icon'  => ['type' => 'icon',  'label' => 'Item 1 Icon',  'default' => 'fas fa-check-circle'],
            'item1_title' => ['type' => 'text',  'label' => 'Item 1 Title', 'default' => 'Quality Assurance'],
            'item1_desc'  => ['type' => 'text',  'label' => 'Item 1 Description', 'default' => 'ISO-aligned production with strict QC before shipment.'],
            'item2_icon'  => ['type' => 'icon',  'label' => 'Item 2 Icon',  'default' => 'fas fa-globe-americas'],
            'item2_title' => ['type' => 'text',  'label' => 'Item 2 Title', 'default' => 'Global Logistics'],
            'item2_desc'  => ['type' => 'text',  'label' => 'Item 2 Description', 'default' => 'On-time delivery with consolidated freight options.'],
            'item3_icon'  => ['type' => 'icon',  'label' => 'Item 3 Icon',  'default' => 'fas fa-user-shield'],
            'item3_title' => ['type' => 'text',  'label' => 'Item 3 Title', 'default' => 'Dedicated Support'],
            'item3_desc'  => ['type' => 'text',  'label' => 'Item 3 Description', 'default' => 'One-to-one account service for long-term buyers.'],
        ],
    ],

    // ============================================================
    // 首页 - 精选产品区块
    // ============================================================
    'home_featured' => [
        'label' => 'Homepage - Featured Products',
        'description' => '精选产品板块标题文字',
        'fields' => [
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Featured Products'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Company Highlights'],
            'link_text'  => ['type' => 'text', 'label' => 'View All Text', 'default' => 'View All →'],
        ],
    ],

    // ============================================================
    // 首页 - Why Choose Us 区块
    // ============================================================
    'home_why_us' => [
        'label' => 'Homepage - Why Choose Us',
        'description' => '为什么选择我们板块',
        'fields' => [
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Why Choose Us'],
            'badge1'     => ['type' => 'text', 'label' => 'Badge 1',   'default' => 'ISO Certified'],
            'badge2'     => ['type' => 'text', 'label' => 'Badge 2',   'default' => 'OEM & ODM'],
            'badge3'     => ['type' => 'text', 'label' => 'Badge 3',   'default' => 'R&D Team'],
            'link_text'  => ['type' => 'text', 'label' => 'Button Text', 'default' => 'About Us'],
            'image'      => ['type' => 'image', 'label' => 'Section Image', 'default' => ''],
        ],
    ],

    // ============================================================
    // 首页 - 案例区块
    // ============================================================
    'home_cases' => [
        'label' => 'Homepage - Success Cases',
        'description' => '成功案例板块标题',
        'fields' => [
            'heading'    => ['type' => 'text', 'label' => 'Heading',    'default' => 'Success Cases'],
            'subheading' => ['type' => 'text', 'label' => 'Subheading', 'default' => 'Global Presence'],
        ],
    ],

    // ============================================================
    // 首页 - 底部 CTA 区块
    // ============================================================
    'home_cta' => [
        'label' => 'Homepage - Bottom CTA',
        'description' => '首页底部行动号召区块',
        'fields' => [
            'heading'    => ['type' => 'text', 'label' => 'Heading',     'default' => 'Ready to start your project?'],
            'text'       => ['type' => 'text', 'label' => 'Description', 'default' => 'Contact us today for a professional quote and expert consultation.'],
            'btn1_text'  => ['type' => 'text', 'label' => 'Button 1 Text', 'default' => 'Request Quote'],
            'btn2_text'  => ['type' => 'text', 'label' => 'Button 2 Text (WhatsApp)', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // 页头 - Header 区块
    // ============================================================
    'header' => [
        'label' => 'Header',
        'description' => '网站顶部导航栏',
        'fields' => [
            'cta_text' => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => 'Request Quote'],
            'cta_url'  => ['type' => 'text', 'label' => 'CTA Button URL',  'default' => '/contact'],
        ],
    ],

    // ============================================================
    // 页脚 - Footer 区块
    // ============================================================
    'footer' => [
        'label' => 'Footer',
        'description' => '网站底部信息',
        'fields' => [
            'quick_links_title' => ['type' => 'text', 'label' => 'Quick Links Title', 'default' => 'Quick Links'],
            'contact_title'     => ['type' => 'text', 'label' => 'Contact Title',     'default' => 'Contact'],
            'copyright'         => ['type' => 'text', 'label' => 'Copyright Text',    'default' => ''],
        ],
    ],

    // ============================================================
    // 浮动联系窗口
    // ============================================================
    'float_contact' => [
        'label' => 'Floating Contact Widget',
        'description' => '右下角/左侧悬浮联系窗口',
        'fields' => [
            'toggle_text'  => ['type' => 'text', 'label' => 'Toggle Button Text', 'default' => 'Contact'],
            'eyebrow'      => ['type' => 'text', 'label' => 'Eyebrow Text',   'default' => 'Global Contact'],
            'title'        => ['type' => 'text', 'label' => 'Panel Title',    'default' => 'Talk to our team'],
            'desc'         => ['type' => 'text', 'label' => 'Panel Description', 'default' => 'Quick access to your configured contact details and social channels.'],
            'cta_text'     => ['type' => 'text', 'label' => 'CTA Button Text', 'default' => 'Send Inquiry'],
            'cta_url'      => ['type' => 'text', 'label' => 'CTA Button URL',  'default' => '/contact'],
        ],
    ],

    // ============================================================
    // Contact 页面
    // ============================================================
    'page_contact' => [
        'label' => 'Contact Page',
        'description' => '联系我们页面',
        'fields' => [
            'label'       => ['type' => 'text', 'label' => 'Page Label',    'default' => 'Contact'],
            'heading'     => ['type' => 'text', 'label' => 'Page Heading',  'default' => 'Contact Us'],
            'form_title'  => ['type' => 'text', 'label' => 'Form Title',    'default' => 'Send Message'],
            'form_subtitle' => ['type' => 'text', 'label' => 'Form Subtitle', 'default' => 'Project requirements, customization, etc.'],
            'form_btn'    => ['type' => 'text', 'label' => 'Form Submit Button', 'default' => 'Send Message'],
            'response_label' => ['type' => 'text', 'label' => 'Response Time Label', 'default' => 'Avg. Response Time'],
            'markets_label'  => ['type' => 'text', 'label' => 'Markets Label', 'default' => 'Main Markets'],
            'chat_btn'    => ['type' => 'text', 'label' => 'WhatsApp Button', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // About 页面
    // ============================================================
    'page_about' => [
        'label' => 'About Page',
        'description' => '关于我们页面',
        'fields' => [
            'label'       => ['type' => 'text', 'label' => 'Page Label',    'default' => 'Company Profile'],
            'cta_btn1'    => ['type' => 'text', 'label' => 'CTA Button 1',  'default' => 'Send My Inquiry'],
            'cta_btn2'    => ['type' => 'text', 'label' => 'CTA Button 2',  'default' => 'Book a Factory Tour'],
            'company_show_title' => ['type' => 'text', 'label' => 'Gallery Title', 'default' => 'Company Show'],
            'certificates_title' => ['type' => 'text', 'label' => 'Certificates Title', 'default' => 'Certificates'],
            'sidebar_title'   => ['type' => 'text', 'label' => 'Sidebar Title',  'default' => 'Contact Provider'],
            'sidebar_btn'     => ['type' => 'text', 'label' => 'Sidebar Button', 'default' => 'Send My Inquiry'],
            'sidebar_chat'    => ['type' => 'text', 'label' => 'Sidebar Chat Button', 'default' => 'Chat Now'],
        ],
    ],

    // ============================================================
    // 品牌颜色
    // ============================================================
    'brand_colors' => [
        'label' => 'Brand Colors',
        'description' => '品牌主色调（影响按钮、链接等）',
        'fields' => [
            'primary'    => ['type' => 'color', 'label' => 'Primary Color (Accent)', 'default' => '#0ea5e9'],
            'primary_dark' => ['type' => 'color', 'label' => 'Primary Dark (Hover)', 'default' => '#0284c7'],
            'ink'        => ['type' => 'color', 'label' => 'Text Dark',    'default' => '#0f172a'],
            'muted'      => ['type' => 'color', 'label' => 'Text Muted',   'default' => '#475569'],
            'surface'    => ['type' => 'color', 'label' => 'Background',    'default' => '#ffffff'],
            'border'     => ['type' => 'color', 'label' => 'Border Color',  'default' => '#e2e8f0'],
        ],
    ],

];
