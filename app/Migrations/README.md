# 数据库迁移指南

## 简介

数据库迁移系统用于管理数据库结构的版本化变更。每次更新程序时，系统会自动执行未执行的迁移文件，确保数据库结构与代码版本保持一致。

## 迁移文件命名规范

迁移文件位于 `app/Migrations/` 目录，命名格式：

```
YYYYMMDDHHMMSS_description.php
```

例如：
- `20250410120000_add_user_email_index.php`
- `20250410123000_create_order_table.php`

## 迁移文件结构

```php
<?php
declare(strict_types=1);

/**
 * 迁移: 添加用户邮箱索引
 * 版本: 20250410120000
 */

use SQLite3;

return new class {
    /**
     * 执行迁移（升级）
     */
    public function up(SQLite3 $db): void {
        // 创建索引示例
        $db->exec('CREATE INDEX IF NOT EXISTS idx_users_email ON users(email)');
        
        // 添加字段示例
        $db->exec('ALTER TABLE users ADD COLUMN phone TEXT');
        
        // 创建表示例
        $db->exec('CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            total DECIMAL(10,2) DEFAULT 0,
            status TEXT DEFAULT "pending",
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
    }
    
    /**
     * 回滚迁移（降级）
     */
    public function down(SQLite3 $db): void {
        // 删除索引示例
        $db->exec('DROP INDEX IF EXISTS idx_users_email');
        
        // 注意：SQLite 不支持直接删除字段，需要重建表
        // 删除表示例
        $db->exec('DROP TABLE IF EXISTS orders');
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
        -- 注意：移除了不需要的字段
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

## 常用操作示例

### 创建表

```php
public function up(SQLite3 $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        parent_id INTEGER DEFAULT 0,
        sort_order INTEGER DEFAULT 0,
        is_active INTEGER DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    // 创建索引
    $db->exec('CREATE INDEX idx_categories_parent ON categories(parent_id)');
    $db->exec('CREATE INDEX idx_categories_slug ON categories(slug)');
}
```

### 添加字段并填充数据

```php
public function up(SQLite3 $db): void {
    // 添加字段
    $db->exec('ALTER TABLE products ADD COLUMN view_count INTEGER DEFAULT 0');
    
    // 填充默认值
    $db->exec('UPDATE products SET view_count = 0 WHERE view_count IS NULL');
}
```

### 创建关联表

```php
public function up(SQLite3 $db): void {
    $db->exec('CREATE TABLE IF NOT EXISTS product_tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        tag_name TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
    $db->exec('CREATE INDEX idx_product_tags_product ON product_tags(product_id)');
    $db->exec('CREATE INDEX idx_product_tags_name ON product_tags(tag_name)');
}
```

## 测试迁移

在部署前，建议先在测试环境验证迁移：

1. 复制生产数据库到测试环境
2. 执行迁移
3. 验证数据完整性
4. 测试回滚（如需要）

## 最佳实践

1. **原子性**：每个迁移应该是一个完整的、独立的操作单元
2. **幂等性**：迁移应该可以安全地重复执行（使用 `IF NOT EXISTS` 等）
3. **数据安全**：修改表结构前，确保已备份数据
4. **版本控制**：迁移文件应该提交到 Git 版本控制
5. **文档**：在迁移文件头部添加清晰的注释说明

## 故障排除

### 迁移执行失败

1. 检查 SQL 语法是否正确
2. 确认表名、字段名是否正确
3. 查看 `migrations` 表中的执行记录
4. 检查 PHP 错误日志

### 手动修复

如果迁移执行失败，可以：

1. 修复迁移文件
2. 手动删除 `migrations` 表中失败的记录
3. 重新执行迁移

```sql
-- 查看迁移记录
SELECT * FROM migrations ORDER BY id DESC;

-- 删除失败的迁移记录（谨慎操作）
DELETE FROM migrations WHERE version = '20250410120000';
```

## 参考

- [SQLite ALTER TABLE 文档](https://www.sqlite.org/lang_altertable.html)
- [SQLite 数据类型](https://www.sqlite.org/datatype3.html)
