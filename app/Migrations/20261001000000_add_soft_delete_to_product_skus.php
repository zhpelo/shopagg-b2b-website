<?php
declare(strict_types=1);

/**
 * Keep SKU primary keys stable when product specifications are edited.
 *
 * Removed specifications are archived instead of deleted so cart, inventory,
 * and order plugins can safely retain references to product_skus.id.
 */
return new class {
    public function up(SQLite3 $db): void {
        if (!$this->tableExists($db, 'product_skus')) {
            return;
        }

        if (!$this->hasColumn($db, 'product_skus', 'deleted_at')) {
            if (!$db->exec('ALTER TABLE product_skus ADD COLUMN deleted_at TEXT')) {
                throw new RuntimeException('Unable to add product_skus.deleted_at: ' . $db->lastErrorMsg());
            }
        }

        if (!$db->exec('CREATE INDEX IF NOT EXISTS idx_product_skus_active ON product_skus(product_id, deleted_at, sort_order)')) {
            throw new RuntimeException('Unable to create active SKU index: ' . $db->lastErrorMsg());
        }
    }

    public function down(SQLite3 $db): void {
        if (!$this->tableExists($db, 'product_skus') || !$this->hasColumn($db, 'product_skus', 'deleted_at')) {
            return;
        }

        if (!$db->exec('PRAGMA foreign_keys = OFF')) {
            throw new RuntimeException('Unable to disable foreign keys: ' . $db->lastErrorMsg());
        }

        try {
            if (!$db->exec('CREATE TABLE product_skus_rollback (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                sku_name TEXT NOT NULL,
                min_qty INTEGER NOT NULL,
                price REAL NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
            )')) {
                throw new RuntimeException('Unable to rebuild product_skus: ' . $db->lastErrorMsg());
            }

            if (!$db->exec('INSERT INTO product_skus_rollback (id, product_id, sku_name, min_qty, price, sort_order, created_at, updated_at)
                SELECT id, product_id, sku_name, min_qty, price, sort_order, created_at, updated_at
                FROM product_skus')) {
                throw new RuntimeException('Unable to copy product_skus: ' . $db->lastErrorMsg());
            }

            if (!$db->exec('DROP TABLE product_skus')
                || !$db->exec('ALTER TABLE product_skus_rollback RENAME TO product_skus')
                || !$db->exec('CREATE INDEX IF NOT EXISTS idx_product_skus_product ON product_skus(product_id)')
                || !$db->exec('CREATE INDEX IF NOT EXISTS idx_product_skus_sort ON product_skus(product_id, sort_order)')) {
                throw new RuntimeException('Unable to finish rebuilding product_skus: ' . $db->lastErrorMsg());
            }
        } finally {
            $db->exec('PRAGMA foreign_keys = ON');
        }
    }

    private function tableExists(SQLite3 $db, string $table): bool {
        $stmt = $db->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table");
        $stmt->bindValue(':table', $table, SQLITE3_TEXT);
        $result = $stmt->execute();
        return $result !== false && $result->fetchArray(SQLITE3_ASSOC) !== false;
    }

    private function hasColumn(SQLite3 $db, string $table, string $column): bool {
        $result = $db->query('PRAGMA table_info(' . $table . ')');
        if ($result === false) {
            return false;
        }

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if (($row['name'] ?? '') === $column) {
                return true;
            }
        }
        return false;
    }
};
