<?php

/**
 * Match free content (pdf_details, books_details, homework_details) to
 * main_category / sub_category (same taxonomy as coloring pages, books, homework).
 */
class EdiExplorerContent
{
    private static $colCache = array();

    /**
     * True if a column exists on a table (used by admin add content forms).
     */
    public static function columnExists(PDO $conn, $table, $col)
    {
        $k = $table . '|' . $col;
        if (isset(self::$colCache[$k])) {
            return self::$colCache[$k];
        }
        $ok = false;
        try {
            $s = $conn->query("SHOW COLUMNS FROM `" . str_replace(array('`', '.'), array('``', ''), $table) . "` LIKE " . $conn->quote($col));
            $ok = $s && $s->rowCount() > 0;
        } catch (Throwable $e) {
            $ok = false;
        }
        self::$colCache[$k] = $ok;
        return $ok;
    }

    /**
     * Distinct main categories (main_category table) tied to published free content only.
     * Does not use product_categories / Honey Market shop taxonomy.
     */
    public static function loadContentMainCategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "
            SELECT DISTINCT m.id, m.title
            FROM `main_category` m
            WHERE
                EXISTS (SELECT 1 FROM `pdf_details` p WHERE p.`status` = 1 AND p.`main_cat_id` = m.`id`)
                OR EXISTS (SELECT 1 FROM `books_details` b WHERE b.`status` = 1 AND b.`main_cat_id` = m.`id`)
                OR EXISTS (SELECT 1 FROM `homework_details` h WHERE h.`status` = 1 AND h.`main_cat_id` = m.`id`)
            ORDER BY m.`title` ASC
        ";
        try {
            $s = $conn->query($sql);
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        return $rows;
    }

    /**
     * Subcategories (sub_category table) used on published pdf / books / homework rows.
     * Parent main_cat_id must also appear on at least one such row (never product_subcategories).
     */
    public static function loadContentSubcategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "
            SELECT DISTINCT s.id, s.main_cat_id, s.title
            FROM `sub_category` s
            WHERE s.`main_cat_id` IN (
                SELECT DISTINCT u.`cid` FROM (
                    SELECT `main_cat_id` AS `cid` FROM `pdf_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
                    UNION
                    SELECT `main_cat_id` FROM `books_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
                    UNION
                    SELECT `main_cat_id` FROM `homework_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
                ) u
            )
            AND (
                EXISTS (SELECT 1 FROM `pdf_details` p WHERE p.`status` = 1 AND p.`sub_cat_id` = s.`id`)
                OR EXISTS (SELECT 1 FROM `books_details` b WHERE b.`status` = 1 AND b.`sub_cat_id` = s.`id`)
                OR EXISTS (SELECT 1 FROM `homework_details` h WHERE h.`status` = 1 AND h.`sub_cat_id` = s.`id`)
            )
            ORDER BY s.`main_cat_id` ASC, s.`title` ASC
        ";
        try {
            $s = $conn->query($sql);
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        return $rows;
    }

    /**
     * Rows for a content table: main_cat_id (required) + optional sub_cat_id, language, grade.
     */
    public static function fetchMatching(PDO $conn, $table, $langF, $ageF, $mainCatId, $subCatId, $limit = 8)
    {
        if ((int) $mainCatId <= 0) {
            return array();
        }
        $sql = "SELECT t.* FROM `" . str_replace('`', '``', $table) . "` t WHERE t.status = 1";
        $params = array();
        $sql .= " AND t.main_cat_id = :mcid";
        $params[':mcid'] = (int) $mainCatId;
        if ((int) $subCatId > 0) {
            $sql .= " AND t.sub_cat_id = :scid";
            $params[':scid'] = (int) $subCatId;
        }
        if ($langF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:langf))";
            $params[':langf'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :agef)";
            $params[':agef'] = $ageF;
        }
        $lim = max(1, (int) $limit);
        $sql .= " ORDER BY t.id DESC LIMIT " . $lim;
        $st = $conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tag column values for explorer context (same filters as fetchMatching, higher limit for tag cloud).
     *
     * @return array<int, array{tag?: string}>
     */
    public static function fetchMatchingTagRows(PDO $conn, $table, $langF, $ageF, $mainCatId, $subCatId, $limit = 500)
    {
        if ((int) $mainCatId <= 0) {
            return array();
        }
        $sql = "SELECT t.tag FROM `" . str_replace('`', '``', $table) . "` t WHERE t.status = 1";
        $params = array();
        $sql .= " AND t.main_cat_id = :mcid";
        $params[':mcid'] = (int) $mainCatId;
        if ((int) $subCatId > 0) {
            $sql .= " AND t.sub_cat_id = :scid";
            $params[':scid'] = (int) $subCatId;
        }
        if ($langF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:langf))";
            $params[':langf'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :agef)";
            $params[':agef'] = $ageF;
        }
        $lim = max(1, (int) $limit);
        $sql .= " ORDER BY t.id DESC LIMIT " . $lim;
        $st = $conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}
