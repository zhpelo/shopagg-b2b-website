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
            'site_name' => 'Global B2B Solutions',
            'site_tagline' => 'Trusted manufacturing partner for global buyers',
            'company_about' => 'We are a manufacturing and exporting company focused on quality, compliance, and fast delivery for global B2B clients.',
            'company_address' => 'No. 88, Industrial Park, Shenzhen, China',
            'company_email' => 'sales@example.com',
            'company_phone' => '+86-000-0000-0000',
            'theme' => 'default',
            'default_lang' => 'en',
            'whatsapp' => ''
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
