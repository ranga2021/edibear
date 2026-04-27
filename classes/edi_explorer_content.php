<?php

/**
 * Match free content (pdf_details, books_details, homework_details) to
 * main_category / sub_category (same taxonomy as coloring pages, books, homework).
 */
class EdiExplorerContent
{
    /**
     * Distinct main categories (id, title) used by published pdf / books / homework.
     */
    public static function loadContentMainCategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "
            SELECT DISTINCT m.id, m.title
            FROM `main_category` m
            INNER JOIN (
                SELECT `main_cat_id` AS `cid` FROM `pdf_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
                UNION
                SELECT `main_cat_id` FROM `books_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
                UNION
                SELECT `main_cat_id` FROM `homework_details` WHERE `status` = 1 AND `main_cat_id` IS NOT NULL
            ) u ON m.id = u.cid
            ORDER BY m.title ASC
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
     * Subcategories (id, main_cat_id, title) that appear in published content.
     */
    public static function loadContentSubcategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "
            SELECT DISTINCT s.id, s.main_cat_id, s.title
            FROM `sub_category` s
            INNER JOIN (
                SELECT `sub_cat_id` AS `sid` FROM `pdf_details` WHERE `status` = 1 AND `sub_cat_id` IS NOT NULL
                UNION
                SELECT `sub_cat_id` FROM `books_details` WHERE `status` = 1 AND `sub_cat_id` IS NOT NULL
                UNION
                SELECT `sub_cat_id` FROM `homework_details` WHERE `status` = 1 AND `sub_cat_id` IS NOT NULL
            ) u ON s.id = u.sid
            ORDER BY s.main_cat_id ASC, s.title ASC
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
}
