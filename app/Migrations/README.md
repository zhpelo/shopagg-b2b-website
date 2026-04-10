# 数据库迁移指南

## 简介

数据库迁移系统用于管理数据库结构的版本化变更。迁移文件存放在 `app/Migrations/` 目录，按版本号顺序执行。

## 迁移文件命名规范

格式：`YYYYMMDDHHMMSS_description.php`

示例：
- `20250410120000_add_user_email_index.php`
- `20250410123000_create_order_table.php`

## 迁移文件模板

```php
<?php
declare(strict_types=1);

/**
 * 迁移: 描述这个迁移的作用
 * 版本: 20250410120000
 */

return new class {
    /**
     * 执行迁移（升级）
     */
    public function up(SQLite3 $db): void {
        // 创建表示例
        $db->exec('CREATE TABLE IF NOT EXISTS example (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        
        // 创建索引示例
        $db->exec('CREATE INDEX IF NOT EXISTS idx_example_name ON example(name)');
    }
    
    /**
     * 回滚迁移（降级）
     */
    public function down(SQLite3 $db): void {
        $db->exec('DROP TABLE IF EXISTS example');
    }
};
```

## SQLite 特殊注意事项

### 1. 添加字段
SQLite 支持 `ALTER TABLE ADD COLUMN`，但有以下限制：
- 只能添加到末尾
- 不能添加 PRIMARY KEY 或 UNIQUE 约束
- 不能添加 NOT NULL 且没有默认值的字段

```php
// ✅ 正确的添加字段
$db->exec('ALTER TABLE users ADD COLUMN phone TEXT');
$db->exec('ALTER TABLE users ADD COLUMN status TEXT DEFAULT "active"');

// ❌ 错误的添加字段（NOT NULL 没有默认值）
$db->exec('ALTER TABLE users ADD COLUMN age INTEGER NOT NULL');
```

### 2. 修改/删除字段
SQLite 不直接支持 `ALTER TABLE DROP COLUMN` 或 `ALTER TABLE ALTER COLUMN`。
需要重建表：

```php
public function up(SQLite3 $db): void {
    // 1. 创建新表
    $db->exec('CREATE TABLE users_new (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        email TEXT NOT NULL
    )');
    
    // 2. 复制数据
    $db->exec('INSERT INTO users_new (id, username, email) 
               SELECT id, username, email FROM users');
    
    // 3. 删除旧表
    $db->exec('DROP TABLE users');
    
    // 4. 重命名新表
    $db->exec('ALTER TABLE users_new RENAME TO users');
}
```

### 3. 索引管理

```php
// 创建索引
$db->exec('CREATE INDEX IF NOT EXISTS idx_name ON table(column)');

// 创建唯一索引
$db->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_email ON users(email)');

// 删除索引
$db->exec('DROP INDEX IF EXISTS idx_name');
```

## 现有迁移文件说明

| 文件 | 说明 |
|------|------|
| `20240101000001_create_users_table.php` | 用户表 + 默认 admin 账号 |
| `20240101000002_create_settings_table.php` | 设置表 + 默认配置 |
| `20240101000003_create_products_table.php` | 产品表（含完整字段） |
| `20240101000004_create_posts_table.php` | 文章/页面/案例表 |
| `20240101000005_create_inquiries_table.php` | 询单表 |
| `20240101000006_create_messages_table.php` | 留言表 |
| `20240101000007_create_product_categories_table.php` | 产品分类表 |
| `20240101000008_create_product_prices_table.php` | 产品价格表 |
| `20240101000009_create_media_files_table.php` | 媒体文件表 |
| `20240101000010_create_update_logs_table.php` | 程序更新日志表 |

## 工作流程

### 新安装系统
1. Database.php 调用 Migrator
2. 执行所有未执行的迁移
3. 创建所有表结构和初始数据

### 版本更新
1. 程序更新时下载新版本文件
2. 自动执行新迁移文件
3. 数据库结构与代码版本保持一致

## 后台管理

访问 `/admin/settings-updater` → "数据库迁移" 标签页：
- 查看待执行和已执行的迁移
- 手动执行数据库迁移

## 参考

- [SQLite ALTER TABLE](https://www.sqlite.org/lang_altertable.html)
- [SQLite 数据类型](https://www.sqlite.org/datatype3.html)
