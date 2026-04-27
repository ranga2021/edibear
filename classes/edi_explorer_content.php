<?php

/**
 * Match free content (pdf_details, books_details, homework_details) to
 * product_categories / product_subcategories for homepage EXPLORE + shop page.
 */
class EdiExplorerContent
{
    private static $colCache = array();

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
     * Rows for a content table matching explorer / shop filters.
     */
    public static function fetchMatching(PDO $conn, $table, $langF, $ageF, $catF, $subF, $limit = 8)
    {
        if (trim((string) $catF) === '' || (int) $catF <= 0) {
            return array();
        }
        $hasPc = self::columnExists($conn, $table, 'product_category_id');
        $hasPs = self::columnExists($conn, $table, 'product_subcategory_id');

        $stn = $conn->prepare("SELECT `name` FROM `product_categories` WHERE `id` = ?");
        $stn->execute(array((int) $catF));
        $cname = $stn->fetchColumn();
        if ($cname === false) {
            return array();
        }
        $cname = trim((string) $cname);

        $sql = "SELECT t.* FROM `" . str_replace('`', '``', $table) . "` t WHERE t.status = 1";
        $params = array();

        if ($langF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:langf))";
            $params[':langf'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :agef)";
            $params[':agef'] = $ageF;
        }

        if ($hasPc) {
            $sql .= " AND (t.product_category_id = :pcid OR (t.product_category_id IS NULL AND LOWER(TRIM(t.tag)) = LOWER(:ptag)))";
            $params[':pcid'] = (int) $catF;
            $params[':ptag'] = $cname;
        } else {
            $sql .= " AND LOWER(TRIM(t.tag)) = LOWER(:ptag2)";
            $params[':ptag2'] = $cname;
        }

        if ((int) $subF > 0 && $hasPs) {
            $sql .= " AND t.product_subcategory_id = :psid";
            $params[':psid'] = (int) $subF;
        }

        $lim = max(1, (int) $limit);
        $sql .= " ORDER BY t.id DESC LIMIT " . $lim;

        $st = $conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Extra AND clause for list pages (uses quoted literals — only pass validated IDs).
     */
    public static function listPageExtraSql(PDO $conn, $table, $cat, $sub)
    {
        if (trim((string) $cat) === '' || (int) $cat <= 0) {
            return '';
        }
        $hasPc = self::columnExists($conn, $table, 'product_category_id');
        $hasPs = self::columnExists($conn, $table, 'product_subcategory_id');
        $cid = (int) $cat;
        $sid = (int) $sub;

        $stn = $conn->prepare("SELECT `name` FROM `product_categories` WHERE `id` = ?");
        $stn->execute(array($cid));
        $cname = $stn->fetchColumn();
        if ($cname === false) {
            return '1=0';
        }
        $q = $conn->quote(trim((string) $cname));
        if ($hasPc) {
            $out = "(product_category_id = " . $cid . " OR (product_category_id IS NULL AND LOWER(TRIM(tag)) = LOWER(" . $q . ")))";
        } else {
            $out = "LOWER(TRIM(tag)) = LOWER(" . $q . ")";
        }
        if ($sid > 0 && $hasPs) {
            $out = "(" . $out . " AND product_subcategory_id = " . $sid . ")";
        }
        return $out;
    }
}
