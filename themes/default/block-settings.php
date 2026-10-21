<?php
declare(strict_types=1);

/** Additional editor fields; existing keys and saved overrides remain compatible. */
return static function (array $definitions): array {
    $additional = [
        'ui_404' => [
            'group' => 'pages',
            'group_label' => '其他页面',
            'label' => '404 页面 · 界面文案',
            'fields' => [
                'label_71c5b377' => [
                    'type' => 'text',
                    'label' => 'Page Not Found',
                    'default' => 'Page Not Found'
                ],
                'label_3847c29d' => [
                    'type' => 'text',
                    'label' => 'The page you are looking for does not exist or has been moved.',
                    'default' => 'The page you are looking for does not exist or has been moved.'
                ],
                'label_eb233972' => [
                    'type' => 'text',
                    'label' => 'Go Home',
                    'default' => 'Go Home'
                ]
            ]
        ],
        'ui_about' => [
            'group' => 'about',
            'group_label' => '其他页面',
            'label' => '关于我们 · 界面文案',
            'fields' => [
                'label_389c8a70' => [
                    'type' => 'text',
                    'label' => 'Rating',
                    'default' => 'Rating'
                ],
                'label_9a393006' => [
                    'type' => 'text',
                    'label' => 'Avg. Response',
                    'default' => 'Avg. Response'
                ],
                'label_bd796d2d' => [
                    'type' => 'text',
                    'label' => 'Established',
                    'default' => 'Established'
                ],
                'label_da6136a1' => [
                    'type' => 'text',
                    'label' => 'Company Profile',
                    'default' => 'Company Profile'
                ],
                'label_b8b74919' => [
                    'type' => 'text',
                    'label' => 'Business Type:',
                    'default' => 'Business Type:'
                ],
                'label_1a4f7352' => [
                    'type' => 'text',
                    'label' => 'Main Products:',
                    'default' => 'Main Products:'
                ],
                'label_f3f76e48' => [
                    'type' => 'text',
                    'label' => 'Year Established:',
                    'default' => 'Year Established:'
                ],
                'label_55080664' => [
                    'type' => 'text',
                    'label' => 'Employees:',
                    'default' => 'Employees:'
                ],
                'label_21775c6b' => [
                    'type' => 'text',
                    'label' => 'Address:',
                    'default' => 'Address:'
                ],
                'label_c0f29566' => [
                    'type' => 'text',
                    'label' => 'SGS Audit No.:',
                    'default' => 'SGS Audit No.:'
                ],
                'label_809ed9f4' => [
                    'type' => 'text',
                    'label' => 'Verify Now',
                    'default' => 'Verify Now'
                ],
                'label_686db27e' => [
                    'type' => 'text',
                    'label' => 'Rating:',
                    'default' => 'Rating:'
                ],
                'label_4a52e8ab' => [
                    'type' => 'text',
                    'label' => 'Avg. Response Time:',
                    'default' => 'Avg. Response Time:'
                ],
                'label_ff8492b1' => [
                    'type' => 'text',
                    'label' => 'General Information',
                    'default' => 'General Information'
                ],
                'label_614155db' => [
                    'type' => 'text',
                    'label' => '6 items verified by SGS',
                    'default' => '6 items verified by SGS'
                ],
                'label_c614298b' => [
                    'type' => 'text',
                    'label' => 'Plant Area:',
                    'default' => 'Plant Area:'
                ],
                'label_9461f571' => [
                    'type' => 'text',
                    'label' => 'Registered Capital:',
                    'default' => 'Registered Capital:'
                ],
                'label_5745be8f' => [
                    'type' => 'text',
                    'label' => 'Trade Capacity',
                    'default' => 'Trade Capacity'
                ],
                'label_6069e2c' => [
                    'type' => 'text',
                    'label' => '7 items verified by SGS',
                    'default' => '7 items verified by SGS'
                ],
                'label_62119a09' => [
                    'type' => 'text',
                    'label' => 'Main Markets:',
                    'default' => 'Main Markets:'
                ],
                'label_1a077ac7' => [
                    'type' => 'text',
                    'label' => 'Trade Staff:',
                    'default' => 'Trade Staff:'
                ],
                'label_4ad895e3' => [
                    'type' => 'text',
                    'label' => 'Incoterms:',
                    'default' => 'Incoterms:'
                ],
                'label_882de154' => [
                    'type' => 'text',
                    'label' => 'Payment Terms:',
                    'default' => 'Payment Terms:'
                ],
                'label_b329803a' => [
                    'type' => 'text',
                    'label' => 'Avg. Lead Time:',
                    'default' => 'Avg. Lead Time:'
                ],
                'label_dbab40c' => [
                    'type' => 'text',
                    'label' => 'Overseas Agent:',
                    'default' => 'Overseas Agent:'
                ],
                'label_554a836' => [
                    'type' => 'text',
                    'label' => 'Export Year:',
                    'default' => 'Export Year:'
                ],
                'label_a192f63a' => [
                    'type' => 'text',
                    'label' => 'Nearest Port:',
                    'default' => 'Nearest Port:'
                ],
                'label_46bb7f59' => [
                    'type' => 'text',
                    'label' => 'R&D Capacity',
                    'default' => 'R&D Capacity'
                ],
                'label_a2afda38' => [
                    'type' => 'text',
                    'label' => 'All information verified by SGS',
                    'default' => 'All information verified by SGS'
                ],
                'label_aa92c9e1' => [
                    'type' => 'text',
                    'label' => 'R&D Engineers:',
                    'default' => 'R&D Engineers:'
                ],
                'label_3e9fe619' => [
                    'type' => 'text',
                    'label' => 'Business Type',
                    'default' => 'Business Type'
                ],
                'label_48b49a9c' => [
                    'type' => 'text',
                    'label' => 'Main Products',
                    'default' => 'Main Products'
                ],
                'label_c80ea749' => [
                    'type' => 'text',
                    'label' => 'Main Markets',
                    'default' => 'Main Markets'
                ]
            ]
        ],
        'ui_case_detail' => [
            'group' => 'cases',
            'group_label' => '案例页面',
            'label' => '案例详情 · 界面文案',
            'fields' => [
                'label_fc19629c' => [
                    'type' => 'text',
                    'label' => 'Success Case',
                    'default' => 'Success Case'
                ],
                'label_387ef928' => [
                    'type' => 'text',
                    'label' => 'Project Details',
                    'default' => 'Project Details'
                ],
                'label_a16bff42' => [
                    'type' => 'text',
                    'label' => 'About This Case',
                    'default' => 'About This Case'
                ],
                'label_bb48cb11' => [
                    'type' => 'text',
                    'label' => 'Publish Time',
                    'default' => 'Publish Time'
                ],
                'label_cde963bc' => [
                    'type' => 'text',
                    'label' => 'If you are interested in this solution or have similar needs, please contact our expert team.',
                    'default' => 'If you are interested in this solution or have similar needs, please contact our expert team.'
                ],
                'label_46718cba' => [
                    'type' => 'text',
                    'label' => 'Request Quote',
                    'default' => 'Request Quote'
                ],
                'label_aaadada5' => [
                    'type' => 'text',
                    'label' => 'Back to All Cases',
                    'default' => 'Back to All Cases'
                ]
            ]
        ],
        'ui_case_list' => [
            'group' => 'cases',
            'group_label' => '案例页面',
            'label' => '案例列表 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_a353cb06' => [
                    'type' => 'text',
                    'label' => 'Cases',
                    'default' => 'Cases'
                ],
                'label_cde963bc' => [
                    'type' => 'text',
                    'label' => 'If you are interested in this solution or have similar needs, please contact our expert team.',
                    'default' => 'If you are interested in this solution or have similar needs, please contact our expert team.'
                ],
                'label_17736424' => [
                    'type' => 'text',
                    'label' => 'Read More →',
                    'default' => 'Read More →'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ]
            ]
        ],
        'ui_contact' => [
            'group' => 'contact',
            'group_label' => '其他页面',
            'label' => '联系我们 · 界面文案',
            'fields' => [
                'label_700560f3' => [
                    'type' => 'text',
                    'label' => 'Address',
                    'default' => 'Address'
                ],
                'label_43352167' => [
                    'type' => 'text',
                    'label' => 'Email',
                    'default' => 'Email'
                ],
                'label_4930944f' => [
                    'type' => 'text',
                    'label' => 'Phone',
                    'default' => 'Phone'
                ],
                'label_fe07306' => [
                    'type' => 'text',
                    'label' => 'Name',
                    'default' => 'Name'
                ],
                'label_c1bf0f5c' => [
                    'type' => 'text',
                    'label' => 'Company',
                    'default' => 'Company'
                ],
                'label_ae0ed984' => [
                    'type' => 'text',
                    'label' => 'Message',
                    'default' => 'Message'
                ],
                'label_fe2b40f' => [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'default' => 'Full Name'
                ],
                'label_de23fd6' => [
                    'type' => 'text',
                    'label' => 'example@email.com',
                    'default' => 'example@email.com'
                ],
                'label_9dffbda' => [
                    'type' => 'text',
                    'label' => 'Company Ltd.',
                    'default' => 'Company Ltd.'
                ],
                'label_303afb03' => [
                    'type' => 'text',
                    'label' => 'Project requirements, customization, etc.',
                    'default' => 'Project requirements, customization, etc.'
                ]
            ]
        ],
        'ui_footer' => [
            'group' => 'global',
            'group_label' => '全站通用',
            'label' => '页脚 · 界面文案',
            'fields' => [
                'label_dfa246ff' => [
                    'type' => 'text',
                    'label' => '. All rights reserved.',
                    'default' => '. All rights reserved.'
                ]
            ]
        ],
        'ui_header' => [
            'group' => 'global',
            'group_label' => '全站通用',
            'label' => '页头 · 界面文案',
            'fields' => [
                'label_108ed5f7' => [
                    'type' => 'text',
                    'label' => 'Search the website',
                    'default' => 'Search the website'
                ],
                'label_c646a2c9' => [
                    'type' => 'text',
                    'label' => 'Search',
                    'default' => 'Search'
                ],
                'label_c5a8374a' => [
                    'type' => 'text',
                    'label' => 'Main navigation',
                    'default' => 'Main navigation'
                ],
                'label_f276c8fc' => [
                    'type' => 'text',
                    'label' => 'Open site search',
                    'default' => 'Open site search'
                ],
                'label_5f4ec78' => [
                    'type' => 'text',
                    'label' => 'Open navigation menu',
                    'default' => 'Open navigation menu'
                ],
                'label_6c37dca6' => [
                    'type' => 'text',
                    'label' => 'Close site search',
                    'default' => 'Close site search'
                ]
            ]
        ],
        'ui_home' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '首页 · 界面文案',
            'fields' => [
                'label_73ac94c3' => [
                    'type' => 'text',
                    'label' => 'Contact',
                    'default' => 'Contact'
                ],
                'label_7007efad' => [
                    'type' => 'text',
                    'label' => 'Factory',
                    'default' => 'Factory'
                ],
                'label_48b49a9c' => [
                    'type' => 'text',
                    'label' => 'Main Products',
                    'default' => 'Main Products'
                ],
                'label_bd796d2d' => [
                    'type' => 'text',
                    'label' => 'Established',
                    'default' => 'Established'
                ],
                'label_d7121a6e' => [
                    'type' => 'text',
                    'label' => 'No cases available.',
                    'default' => 'No cases available.'
                ]
            ]
        ],
        'ui_list' => [
            'group' => 'pages',
            'group_label' => '其他页面',
            'label' => '通用列表 · 界面文案',
            'fields' => [
                'label_4f07e0ca' => [
                    'type' => 'text',
                    'label' => 'Read More',
                    'default' => 'Read More'
                ]
            ]
        ],
        'ui_page_detail' => [
            'group' => 'pages',
            'group_label' => '其他页面',
            'label' => '独立页面 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_e83d9196' => [
                    'type' => 'text',
                    'label' => 'Page',
                    'default' => 'Page'
                ],
                'label_6129f1d9' => [
                    'type' => 'text',
                    'label' => '发布于',
                    'default' => 'Published'
                ],
                'label_cd482da5' => [
                    'type' => 'text',
                    'label' => '更新于',
                    'default' => 'Updated'
                ],
                'label_a1d0c7a1' => [
                    'type' => 'text',
                    'label' => 'Need help?',
                    'default' => 'Need help?'
                ],
                'label_ccc7c4c' => [
                    'type' => 'text',
                    'label' => '如果你对本页面内容有疑问，欢迎直接联系我们。',
                    'default' => 'Contact our team if you have any questions about this page.'
                ],
                'label_6c781549' => [
                    'type' => 'text',
                    'label' => 'Back Home',
                    'default' => 'Back Home'
                ],
                'label_91dd0cb5' => [
                    'type' => 'text',
                    'label' => 'Contact Us',
                    'default' => 'Contact Us'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ]
            ]
        ],
        'ui_post_detail' => [
            'group' => 'posts',
            'group_label' => '文章页面',
            'label' => '文章详情 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_c79c4311' => [
                    'type' => 'text',
                    'label' => 'Blog',
                    'default' => 'Blog'
                ],
                'label_e1407260' => [
                    'type' => 'text',
                    'label' => '上一篇',
                    'default' => 'Previous article'
                ],
                'label_aee2653b' => [
                    'type' => 'text',
                    'label' => '下一篇',
                    'default' => 'Next article'
                ],
                'label_43328c31' => [
                    'type' => 'text',
                    'label' => 'Category:',
                    'default' => 'Category:'
                ],
                'label_a89aac2f' => [
                    'type' => 'text',
                    'label' => 'More in this category',
                    'default' => 'More in this category'
                ],
                'label_2f1e2487' => [
                    'type' => 'text',
                    'label' => 'Back to Blog List',
                    'default' => 'Back to Blog List'
                ],
                'label_73ac94c3' => [
                    'type' => 'text',
                    'label' => 'Contact',
                    'default' => 'Contact'
                ],
                'label_7865024c' => [
                    'type' => 'text',
                    'label' => 'Recommended',
                    'default' => 'Recommended'
                ],
                'label_a9bef445' => [
                    'type' => 'text',
                    'label' => 'You might also like',
                    'default' => 'You might also like'
                ],
                'label_79166812' => [
                    'type' => 'text',
                    'label' => 'View all articles',
                    'default' => 'View all articles'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ],
                'label_86f98b4d' => [
                    'type' => 'text',
                    'label' => 'Article navigation',
                    'default' => 'Article navigation'
                ]
            ]
        ],
        'ui_post_list' => [
            'group' => 'posts',
            'group_label' => '文章页面',
            'label' => '文章列表 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_c79c4311' => [
                    'type' => 'text',
                    'label' => 'Blog',
                    'default' => 'Blog'
                ],
                'label_6e4606f1' => [
                    'type' => 'text',
                    'label' => 'Get the latest industry insights and company news',
                    'default' => 'Get the latest industry insights and company news'
                ],
                'label_6e04e991' => [
                    'type' => 'text',
                    'label' => 'No articles found',
                    'default' => 'No articles found'
                ],
                'label_8b0d9992' => [
                    'type' => 'text',
                    'label' => 'View All Articles',
                    'default' => 'View All Articles'
                ],
                'label_b2b9ee7a' => [
                    'type' => 'text',
                    'label' => 'Read Full Article →',
                    'default' => 'Read Full Article →'
                ],
                'label_f075e98d' => [
                    'type' => 'text',
                    'label' => 'Categories',
                    'default' => 'Categories'
                ],
                'label_25f34c4f' => [
                    'type' => 'text',
                    'label' => 'All Articles',
                    'default' => 'All Articles'
                ],
                'label_478c3941' => [
                    'type' => 'text',
                    'label' => 'Need Help?',
                    'default' => 'Need Help?'
                ],
                'label_3b42ed2f' => [
                    'type' => 'text',
                    'label' => 'If you have any questions, feel free to contact our professional team.',
                    'default' => 'If you have any questions, feel free to contact our professional team.'
                ],
                'label_73ac94c3' => [
                    'type' => 'text',
                    'label' => 'Contact',
                    'default' => 'Contact'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ]
            ]
        ],
        'ui_product_detail' => [
            'group' => 'products',
            'group_label' => '产品页面',
            'label' => '产品详情 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_cb2c4cad' => [
                    'type' => 'text',
                    'label' => 'Products',
                    'default' => 'Products'
                ],
                'label_1c05b93a' => [
                    'type' => 'text',
                    'label' => 'SKU',
                    'default' => 'SKU'
                ],
                'label_5753df2c' => [
                    'type' => 'text',
                    'label' => 'MOQ',
                    'default' => 'MOQ'
                ],
                'label_dd3ec7ca' => [
                    'type' => 'text',
                    'label' => 'Price',
                    'default' => 'Price'
                ],
                'label_440bcd59' => [
                    'type' => 'text',
                    'label' => '+ Pieces',
                    'default' => '+ Pieces'
                ],
                'label_65b23062' => [
                    'type' => 'text',
                    'label' => 'Pieces',
                    'default' => 'Pieces'
                ],
                'label_323b57b9' => [
                    'type' => 'text',
                    'label' => '/Pieces',
                    'default' => '/Pieces'
                ],
                'label_19950e3e' => [
                    'type' => 'text',
                    'label' => 'Thank you for your interest. Our team will review your requirements.',
                    'default' => 'Thank you for your interest. Our team will review your requirements.'
                ],
                'label_c2137cef' => [
                    'type' => 'text',
                    'label' => 'ISO Certified',
                    'default' => 'ISO Certified'
                ],
                'label_81636f4' => [
                    'type' => 'text',
                    'label' => 'OEM & ODM',
                    'default' => 'OEM & ODM'
                ],
                'label_5d5fd1ef' => [
                    'type' => 'text',
                    'label' => 'Global Presence',
                    'default' => 'Global Presence'
                ],
                'label_46718cba' => [
                    'type' => 'text',
                    'label' => 'Request Quote',
                    'default' => 'Request Quote'
                ],
                'label_c6e20d97' => [
                    'type' => 'text',
                    'label' => 'Your Name *',
                    'default' => 'Your Name *'
                ],
                'label_1ac07b9' => [
                    'type' => 'text',
                    'label' => 'Email Address *',
                    'default' => 'Email Address *'
                ],
                'label_c1bf0f5c' => [
                    'type' => 'text',
                    'label' => 'Company',
                    'default' => 'Company'
                ],
                'label_efa0b76d' => [
                    'type' => 'text',
                    'label' => 'Quantity Needed',
                    'default' => 'Quantity Needed'
                ],
                'label_4e26fc61' => [
                    'type' => 'text',
                    'label' => 'Requirements',
                    'default' => 'Requirements'
                ],
                'label_cba8b492' => [
                    'type' => 'text',
                    'label' => 'Send My Inquiry',
                    'default' => 'Send My Inquiry'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ],
                'label_bb0536da' => [
                    'type' => 'text',
                    'label' => 'Next product image',
                    'default' => 'Next product image'
                ],
                'label_ff5fa91a' => [
                    'type' => 'text',
                    'label' => 'Previous product image',
                    'default' => 'Previous product image'
                ],
                'label_fe2b40f' => [
                    'type' => 'text',
                    'label' => 'Full Name',
                    'default' => 'Full Name'
                ],
                'label_de23fd6' => [
                    'type' => 'text',
                    'label' => 'example@email.com',
                    'default' => 'example@email.com'
                ],
                'label_9dffbda' => [
                    'type' => 'text',
                    'label' => 'Company Ltd.',
                    'default' => 'Company Ltd.'
                ],
                'label_91f6996b' => [
                    'type' => 'text',
                    'label' => 'e.g. 500 units',
                    'default' => 'e.g. 500 units'
                ],
                'label_303afb03' => [
                    'type' => 'text',
                    'label' => 'Project requirements, customization, etc.',
                    'default' => 'Project requirements, customization, etc.'
                ]
            ]
        ],
        'ui_product_list' => [
            'group' => 'products',
            'group_label' => '产品页面',
            'label' => '产品列表 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_cb2c4cad' => [
                    'type' => 'text',
                    'label' => 'Products',
                    'default' => 'Products'
                ],
                'label_d7285d69' => [
                    'type' => 'text',
                    'label' => 'Uncategorized',
                    'default' => 'Uncategorized'
                ],
                'label_d43fa44c' => [
                    'type' => 'text',
                    'label' => 'breadcrumb',
                    'default' => 'breadcrumb'
                ]
            ]
        ],
        'ui_search' => [
            'group' => 'pages',
            'group_label' => '其他页面',
            'label' => '搜索 · 界面文案',
            'fields' => [
                'label_52f50eae' => [
                    'type' => 'text',
                    'label' => 'Home',
                    'default' => 'Home'
                ],
                'label_c646a2c9' => [
                    'type' => 'text',
                    'label' => 'Search',
                    'default' => 'Search'
                ],
                'label_dbda9ba2' => [
                    'type' => 'text',
                    'label' => 'Site Search',
                    'default' => 'Site Search'
                ],
                'label_7bf73995' => [
                    'type' => 'text',
                    'label' => 'Find products, articles and cases',
                    'default' => 'Find products, articles and cases'
                ],
                'label_df974620' => [
                    'type' => 'text',
                    'label' => 'Search products, articles and cases',
                    'default' => 'Search products, articles and cases'
                ],
                'label_70a48e15' => [
                    'type' => 'text',
                    'label' => 'What are you looking for?',
                    'default' => 'What are you looking for?'
                ],
                'label_5675dd94' => [
                    'type' => 'text',
                    'label' => 'Enter a keyword to search all published products, articles and cases.',
                    'default' => 'Enter a keyword to search all published products, articles and cases.'
                ],
                'label_94f50c43' => [
                    'type' => 'text',
                    'label' => 'Search results',
                    'default' => 'Search results'
                ],
                'label_30e3edb' => [
                    'type' => 'text',
                    'label' => 'No matching results',
                    'default' => 'No matching results'
                ],
                'label_58ac5472' => [
                    'type' => 'text',
                    'label' => 'Try a shorter keyword, check the spelling, or search all content types.',
                    'default' => 'Try a shorter keyword, check the spelling, or search all content types.'
                ],
                'label_2692f483' => [
                    'type' => 'text',
                    'label' => 'Search all content',
                    'default' => 'Search all content'
                ],
                'label_136a35ae' => [
                    'type' => 'text',
                    'label' => 'View details',
                    'default' => 'View details'
                ],
                'label_ead1fb48' => [
                    'type' => 'text',
                    'label' => 'Previous',
                    'default' => 'Previous'
                ],
                'label_dce2da08' => [
                    'type' => 'text',
                    'label' => 'Next',
                    'default' => 'Next'
                ],
                'label_30635f6c' => [
                    'type' => 'text',
                    'label' => 'Breadcrumb',
                    'default' => 'Breadcrumb'
                ],
                'label_47c40d93' => [
                    'type' => 'text',
                    'label' => 'Filter search results',
                    'default' => 'Filter search results'
                ],
                'label_2cc1f875' => [
                    'type' => 'text',
                    'label' => 'Search results pagination',
                    'default' => 'Search results pagination'
                ],
                'label_b3aa23a3' => [
                    'type' => 'text',
                    'label' => 'Previous page',
                    'default' => 'Previous page'
                ],
                'label_6f88c1e3' => [
                    'type' => 'text',
                    'label' => 'Next page',
                    'default' => 'Next page'
                ]
            ]
        ],
        'ui_thanks' => [
            'group' => 'pages',
            'group_label' => '其他页面',
            'label' => '提交成功 · 界面文案',
            'fields' => [
                'label_e54b2564' => [
                    'type' => 'text',
                    'label' => 'Inquiry Sent Successfully!',
                    'default' => 'Inquiry Sent Successfully!'
                ],
                'label_2191f9b1' => [
                    'type' => 'text',
                    'label' => 'Thank you for your interest. Our team will review your requirements and get back to you soon.',
                    'default' => 'Thank you for your interest. Our team will review your requirements and get back to you soon.'
                ],
                'label_2b001de4' => [
                    'type' => 'text',
                    'label' => 'Expected Response Time: We usually reply within 24 hours during business days.',
                    'default' => 'Expected Response Time: We usually reply within 24 hours during business days.'
                ],
                'label_69863d42' => [
                    'type' => 'text',
                    'label' => 'Back to Home',
                    'default' => 'Back to Home'
                ],
                'label_fffe8213' => [
                    'type' => 'text',
                    'label' => 'View More Products',
                    'default' => 'View More Products'
                ]
            ]
        ],
        'home_why_us' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '为什么选择我们',
            'fields' => [
                'image' => [
                    'type' => 'hidden',
                    'label' => 'image',
                    'default' => ''
                ]
            ]
        ],
        'home_video' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '工厂视频展示',
            'fields' => [
                'media' => [
                    'type' => 'media',
                    'label' => '工厂视频',
                    'default' => '',
                    'allowed' => 'video',
                    'media_type_key' => 'media_type',
                    'default_media_type' => 'video'
                ],
                'poster' => [
                    'type' => 'image',
                    'label' => '视频封面',
                    'default' => ''
                ],
                'eyebrow' => [
                    'type' => 'text',
                    'label' => 'eyebrow',
                    'default' => 'INSIDE OUR FACTORY'
                ],
                'heading' => [
                    'type' => 'text',
                    'label' => 'heading',
                    'default' => 'See our production in action'
                ],
                'text' => [
                    'type' => 'text',
                    'label' => 'text',
                    'default' => 'Explore our production process and quality control.'
                ],
                'empty_text' => [
                    'type' => 'text',
                    'label' => 'empty text',
                    'default' => 'Contact our team to arrange a factory tour.'
                ],
                'is_visible' => [
                    'type' => 'toggle',
                    'label' => '显示此区块',
                    'default' => 'yes'
                ],
                'media_type' => [
                    'type' => 'hidden',
                    'label' => '媒体类型',
                    'default' => 'video'
                ]
            ]
        ],
        'home_featured' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '精选产品',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '8',
                    'min' => 1,
                    'max' => 24,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ],
        'home_articles' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '文章资讯',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '3',
                    'min' => 1,
                    'max' => 24,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ],
        'home_cases' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '成功案例',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '6',
                    'min' => 1,
                    'max' => 24,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ],
        'home_company_show' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '公司展示',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '6',
                    'min' => 1,
                    'max' => 24,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ],
        'home_certificates' => [
            'group' => 'home',
            'group_label' => '首页',
            'label' => '资质证书',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '6',
                    'min' => 1,
                    'max' => 24,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ],
        'product_detail' => [
            'group' => 'pages',
            'group_label' => '页面配置',
            'label' => '产品详情页',
            'fields' => [
                'limit' => [
                    'type' => 'number',
                    'label' => '显示数量',
                    'default' => '4',
                    'min' => 1,
                    'max' => 8,
                    'step' => 1,
                    'help' => '只展示已发布内容；选定产品按选择顺序显示。'
                ]
            ]
        ]
    ];
    foreach ($additional as $key => $block) {
        if (!isset($definitions[$key])) {
            $definitions[$key] = $block;
        } else {
            $definitions[$key]['fields'] = ($definitions[$key]['fields'] ?? []) + $block['fields'];
        }
    }
    foreach ($definitions as &$definition) {
        foreach ($definition['fields'] as $key => &$field) {
            if (($field['type'] ?? '') === 'text' && preg_match('/(?:_url|_href|^url|_action)$/', $key)) $field['type'] = 'url';
        }
        unset($field);
    }
    unset($definition);
    return $definitions;
};
