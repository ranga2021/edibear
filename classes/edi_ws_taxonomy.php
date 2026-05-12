<?php

/**
 * Worksheet-specific taxonomy (ws_categories / ws_subcategories) — separate from shop product_categories.
 */
class EdiWsTaxonomy
{
    /**
     * @return bool
     */
    public static function tableExists(PDO $conn, $table)
    {
        $t = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $table);
        if ($t === '') {
            return false;
        }
        try {
            $st = $conn->query("SHOW TABLES LIKE " . $conn->quote($t));
            return $st && $st->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Nullable FK columns on worksheet content tables for ws_* taxonomy.
     *
     * @return bool both columns exist or were created
     */
    public static function ensureWorksheetWsColumns(PDO $conn, $table)
    {
        $tbl = str_replace(array('`', '.'), array('``', ''), (string) $table);
        if ($tbl === '') {
            return false;
        }
        foreach (array('ws_category_id', 'ws_subcategory_id') as $col) {
            if (EdiExplorerContent::columnExists($conn, $table, $col)) {
                continue;
            }
            try {
                $conn->exec(
                    "ALTER TABLE `" . $tbl . "` ADD COLUMN `" . $col . "` INT UNSIGNED NULL DEFAULT NULL"
                );
                EdiExplorerContent::rememberColumnExists($table, $col, true);
            } catch (Throwable $e) {
                if (!EdiExplorerContent::columnExists($conn, $table, $col)) {
                    return false;
                }
            }
        }
        return EdiExplorerContent::columnExists($conn, $table, 'ws_category_id')
            && EdiExplorerContent::columnExists($conn, $table, 'ws_subcategory_id');
    }

    /**
     * @return array<int, array{id:int, name:string, sort_order:int}>
     */
    public static function loadCategories(PDO $conn)
    {
        if (!self::tableExists($conn, 'ws_categories')) {
            return array();
        }
        try {
            $s = $conn->query(
                "SELECT `id`, `name`, `sort_order` FROM `ws_categories` ORDER BY `sort_order` ASC, `name` ASC"
            );
            return $s ? $s->fetchAll(PDO::FETCH_ASSOC) : array();
        } catch (Throwable $e) {
            return array();
        }
    }

    /**
     * @return array<int, array{id:int, category_id:int, name:string, sort_order:int}>
     */
    public static function loadSubcategories(PDO $conn)
    {
        if (!self::tableExists($conn, 'ws_subcategories')) {
            return array();
        }
        try {
            $s = $conn->query(
                "SELECT `id`, `category_id`, `name`, `sort_order` FROM `ws_subcategories` ORDER BY `sort_order` ASC, `name` ASC"
            );
            return $s ? $s->fetchAll(PDO::FETCH_ASSOC) : array();
        } catch (Throwable $e) {
            return array();
        }
    }

    /**
     * Normalize POSTed worksheet taxonomy: subcategory implies its parent category.
     *
     * @return array{0: int|null, 1: int|null} [ws_category_id, ws_subcategory_id]
     */
    public static function normalizeWorksheetWsIds(PDO $conn, $postedCatId, $postedSubId)
    {
        $subId = (int) $postedSubId;
        $catId = (int) $postedCatId;
        if ($subId > 0 && self::tableExists($conn, 'ws_subcategories')) {
            try {
                $st = $conn->prepare("SELECT `category_id` FROM `ws_subcategories` WHERE `id` = ? LIMIT 1");
                $st->execute(array($subId));
                $parent = $st->fetchColumn();
                if ($parent === false) {
                    return array(null, null);
                }
                return array((int) $parent, $subId);
            } catch (Throwable $e) {
                return array(null, null);
            }
        }
        if ($catId > 0) {
            return array($catId, null);
        }
        return array(null, null);
    }

    /**
     * Count ws_products rows tied to this category (via subcategories).
     *
     * @return int
     */
    public static function countWsProductsForCategory(PDO $conn, $categoryId)
    {
        $cid = (int) $categoryId;
        if ($cid < 1 || !self::tableExists($conn, 'ws_products') || !self::tableExists($conn, 'ws_subcategories')) {
            return 0;
        }
        try {
            $st = $conn->prepare(
                "SELECT COUNT(*) FROM `ws_products` p
                 INNER JOIN `ws_subcategories` s ON s.`id` = p.`subcategory_id`
                 WHERE s.`category_id` = ?"
            );
            $st->execute(array($cid));
            return (int) $st->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * @return int
     */
    public static function countWsProductsForSubcategory(PDO $conn, $subcategoryId)
    {
        $sid = (int) $subcategoryId;
        if ($sid < 1 || !self::tableExists($conn, 'ws_products')) {
            return 0;
        }
        try {
            $st = $conn->prepare("SELECT COUNT(*) FROM `ws_products` WHERE `subcategory_id` = ?");
            $st->execute(array($sid));
            return (int) $st->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * @return int
     */
    public static function countSubcategoriesForCategory(PDO $conn, $categoryId)
    {
        $cid = (int) $categoryId;
        if ($cid < 1 || !self::tableExists($conn, 'ws_subcategories')) {
            return 0;
        }
        try {
            $st = $conn->prepare("SELECT COUNT(*) FROM `ws_subcategories` WHERE `category_id` = ?");
            $st->execute(array($cid));
            return (int) $st->fetchColumn();
        } catch (Throwable $e) {
            return 0;
        }
    }
}
