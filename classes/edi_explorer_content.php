<?php

/**
 * Match free content (pdf_details, books_details, homework_details) to
 * main_category / sub_category (same taxonomy as coloring pages, books, homework).
 */
class EdiExplorerContent
{
    private static $colCache = array();

    /**
     * Honey Market product categories (shop taxonomy).
     *
     * @return array<int, array{id:int, name:string}>
     */
    public static function loadProductCategoryOptions(PDO $conn)
    {
        $rows = array();
        try {
            $where = "";
            if (self::columnExists($conn, "product_categories", "status")) {
                $where = " WHERE `status` = 1";
            }
            $s = $conn->query("SELECT `id`, `name` FROM `product_categories`" . $where . " ORDER BY `name` ASC");
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        $out = array();
        foreach ($rows as $r) {
            $name = trim((string) ($r['name'] ?? ''));
            if (!self::isValidTaxonomyTitle($name)) {
                continue;
            }
            $out[] = array('id' => (int) ($r['id'] ?? 0), 'name' => $name);
        }
        return $out;
    }

    /**
     * Honey Market product subcategories (shop taxonomy).
     *
     * @return array<int, array{id:int, product_category_id:int, title:string}>
     */
    public static function loadProductSubcategoryOptions(PDO $conn)
    {
        $rows = array();
        try {
            $where = "";
            if (self::columnExists($conn, "product_subcategories", "status")) {
                $where = " WHERE `status` = 1";
            }
            $s = $conn->query("SELECT `id`, `product_category_id`, `title` FROM `product_subcategories`" . $where . " ORDER BY `product_category_id` ASC, `title` ASC");
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        $out = array();
        foreach ($rows as $r) {
            $title = trim((string) ($r['title'] ?? ''));
            if (!self::isValidTaxonomyTitle($title)) {
                continue;
            }
            $out[] = array(
                'id' => (int) ($r['id'] ?? 0),
                'product_category_id' => (int) ($r['product_category_id'] ?? 0),
                'title' => $title,
            );
        }
        return self::dedupeProductSubcategoryRows($out);
    }

    /**
     * Collapse duplicate subcategory titles within the same parent category (keeps first row).
     * Prevents duplicate labels in dropdowns when the database has accidental duplicates.
     *
     * @param array<int, array{id?:int, product_category_id?:int, title?:string}> $rows
     * @return array<int, array{id:int, product_category_id:int, title:string}>
     */
    public static function dedupeProductSubcategoryRows(array $rows)
    {
        $seen = array();
        $out = array();
        foreach ($rows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $pcid = (int) ($r['product_category_id'] ?? 0);
            $title = strtolower(trim((string) ($r['title'] ?? '')));
            if ($title === '') {
                continue;
            }
            $key = $pcid . "\0" . $title;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = array(
                'id' => (int) ($r['id'] ?? 0),
                'product_category_id' => $pcid,
                'title' => trim((string) ($r['title'] ?? '')),
            );
        }
        return $out;
    }

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
     * Set column-exists cache (e.g. after ALTER TABLE adds a column in another helper).
     */
    public static function rememberColumnExists($table, $col, $exists = true)
    {
        $k = (string) $table . '|' . (string) $col;
        self::$colCache[$k] = (bool) $exists;
    }

    /**
     * Ensure a nullable TEXT column exists. Returns true when column exists/created.
     */
    public static function ensureNullableTextColumn(PDO $conn, $table, $col)
    {
        if (self::columnExists($conn, $table, $col)) {
            return true;
        }
        $tbl = str_replace(array('`', '.'), array('``', ''), (string) $table);
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
        if ($column === '') {
            return false;
        }
        try {
            $conn->exec("ALTER TABLE `" . $tbl . "` ADD COLUMN `" . $column . "` TEXT NULL DEFAULT NULL");
        } catch (Throwable $e) {
            return self::columnExists($conn, $table, $col);
        }
        self::$colCache[$table . '|' . $col] = true;
        return true;
    }

    /**
     * Distinct main categories (main_category table) tied to published free content only.
     * Does not use product_categories / Honey Market shop taxonomy.
     */
    public static function loadContentMainCategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "SELECT `id`, `title` FROM `main_category` ORDER BY `title` ASC";
        try {
            $s = $conn->query($sql);
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        return self::sanitizeMainCategoryRows($rows);
    }

    /**
     * Subcategories (sub_category table) used on published pdf / books / homework rows.
     * Parent main_cat_id must also appear on at least one such row (never product_subcategories).
     */
    public static function loadContentSubcategoryOptions(PDO $conn)
    {
        $rows = array();
        $sql = "SELECT `id`, `main_cat_id`, `title` FROM `sub_category` ORDER BY `main_cat_id` ASC, `title` ASC";
        try {
            $s = $conn->query($sql);
            if ($s) {
                $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        return self::sanitizeSubcategoryRows($rows);
    }

    private static function sanitizeMainCategoryRows(array $rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $title = trim((string) ($r['title'] ?? ''));
            if (!self::isValidTaxonomyTitle($title)) {
                continue;
            }
            $out[] = array(
                'id' => (int) ($r['id'] ?? 0),
                'title' => $title,
            );
        }
        return $out;
    }

    private static function sanitizeSubcategoryRows(array $rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $title = trim((string) ($r['title'] ?? ''));
            if (!self::isValidTaxonomyTitle($title)) {
                continue;
            }
            $out[] = array(
                'id' => (int) ($r['id'] ?? 0),
                'main_cat_id' => (int) ($r['main_cat_id'] ?? 0),
                'title' => $title,
            );
        }
        return $out;
    }

    private static function isValidTaxonomyTitle($title)
    {
        $norm = strtolower(trim((string) $title));
        if ($norm === '') {
            return false;
        }
        // Ignore UI placeholder-like labels accidentally saved to taxonomy.
        if (strpos($norm, 'required') !== false || strpos($norm, 'optional') !== false) {
            return false;
        }
        if ($norm === 'category' || $norm === 'subcategory') {
            return false;
        }
        return true;
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

    /**
     * Fetch rows by Honey Market product category/subcategory (works even if main_cat_id is not yet backfilled).
     * Requires the content table to have product_category_id and (optionally) product_subcategory_id columns.
     */
    public static function fetchMatchingByProductTaxonomy(PDO $conn, $table, $langF, $ageF, $productCatId, $productSubId, $limit = 8)
    {
        $pcid = (int) $productCatId;
        $psid = (int) $productSubId;
        if ($pcid <= 0) {
            return array();
        }
        if (!self::columnExists($conn, $table, "product_category_id")) {
            return array();
        }
        $hasSub = self::columnExists($conn, $table, "product_subcategory_id");

        $sql = "SELECT t.* FROM `" . str_replace('`', '``', $table) . "` t WHERE t.status = 1 AND t.product_category_id = :pcid";
        $params = array(':pcid' => $pcid);
        if ($psid > 0 && $hasSub) {
            $sql .= " AND t.product_subcategory_id = :psid";
            $params[':psid'] = $psid;
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
     * Tag rows by Honey Market product category/subcategory.
     * Pass $limit <= 0 to scan all matching rows (needed so tag chips are complete; slash-separated tags cannot use SQL DISTINCT).
     *
     * @return array<int, array{tag?: string}>
     */
    public static function fetchMatchingTagRowsByProductTaxonomy(PDO $conn, $table, $langF, $ageF, $productCatId, $productSubId, $limit = 500)
    {
        $pcid = (int) $productCatId;
        $psid = (int) $productSubId;
        if ($pcid <= 0) {
            return array();
        }
        if (!self::columnExists($conn, $table, "product_category_id")) {
            return array();
        }
        $hasSub = self::columnExists($conn, $table, "product_subcategory_id");

        $sql = "SELECT t.tag FROM `" . str_replace('`', '``', $table) . "` t WHERE t.status = 1 AND t.product_category_id = :pcid";
        $params = array(':pcid' => $pcid);
        if ($psid > 0 && $hasSub) {
            $sql .= " AND t.product_subcategory_id = :psid";
            $params[':psid'] = $psid;
        }
        if ($langF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:langf))";
            $params[':langf'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= " AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :agef)";
            $params[':agef'] = $ageF;
        }
        $lim = (int) $limit;
        if ($lim > 0) {
            $sql .= " ORDER BY t.id DESC LIMIT " . $lim;
        }
        $st = $conn->prepare($sql);
        $st->execute($params);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Map shop product category / subcategory picks to free-content main_category / sub_category IDs.
     * The site EXPLORE and list pages use main_cat_id & sub_cat_id, not product_category_id.
     * Matching: same title/name (case-insensitive), then common synonyms (e.g. "Coloring Pages" → Leisure Activities).
     *
     * @return array{main_cat_id: int|null, sub_cat_id: int|null}
     */
    public static function mapProductSelectionsToContentCategoryIds(PDO $conn, $productCatId, $productSubId)
    {
        $out = array("main_cat_id" => null, "sub_cat_id" => null);
        $pcid = (int) $productCatId;
        $psid = (int) $productSubId;
        if ($pcid <= 0) {
            return $out;
        }
        try {
            $st = $conn->prepare("SELECT `name` FROM `product_categories` WHERE `id` = ?");
            $st->execute(array($pcid));
            $pname = trim((string) $st->fetchColumn());
        } catch (Throwable $e) {
            return $out;
        }
        if ($pname === "") {
            return $out;
        }
        $mainId = self::resolveMainCategoryIdFromProductName($conn, $pname);
        if ($mainId === null) {
            return $out;
        }
        $out["main_cat_id"] = $mainId;
        if ($psid <= 0) {
            return $out;
        }
        try {
            $st3 = $conn->prepare("SELECT `title` FROM `product_subcategories` WHERE `id` = ? AND `product_category_id` = ?");
            $st3->execute(array($psid, $pcid));
            $stitle = trim((string) $st3->fetchColumn());
        } catch (Throwable $e) {
            return $out;
        }
        if ($stitle === "") {
            return $out;
        }
        $st4 = $conn->prepare("SELECT `id` FROM `sub_category` WHERE `main_cat_id` = ? AND LOWER(TRIM(`title`)) = LOWER(?) LIMIT 1");
        $st4->execute(array($mainId, $stitle));
        $sid = $st4->fetchColumn();
        if ($sid !== false) {
            $out["sub_cat_id"] = (int) $sid;
        }
        return $out;
    }

    /**
     * @return int|null
     */
    private static function resolveMainCategoryIdFromProductName(PDO $conn, $productCategoryName)
    {
        $name = trim((string) $productCategoryName);
        $st = $conn->prepare("SELECT `id` FROM `main_category` WHERE LOWER(TRIM(`title`)) = LOWER(?) LIMIT 1");
        $st->execute(array($name));
        $id = $st->fetchColumn();
        if ($id !== false) {
            return (int) $id;
        }
        $syn = self::synonymMainCategoryTitleForProductCategory(strtolower($name));
        if ($syn === null) {
            return null;
        }
        $st2 = $conn->prepare("SELECT `id` FROM `main_category` WHERE LOWER(TRIM(`title`)) = LOWER(?) LIMIT 1");
        $st2->execute(array($syn));
        $id2 = $st2->fetchColumn();
        return $id2 === false ? null : (int) $id2;
    }

    /**
     * When product_categories.name does not match main_category.title exactly.
     *
     * @return string|null target main_category.title
     */
    private static function synonymMainCategoryTitleForProductCategory($lowerName)
    {
        $map = array(
            "coloring pages" => "Leisure Activities",
            "colouring pages" => "Leisure Activities",
            "coloring" => "Leisure Activities",
            "fun activities" => "Leisure Activities",
            "worksheets" => "Study Packs",
            "homework" => "Study Packs",
            "study packs" => "Study Packs",
            "brain boosters" => "Books & Papers",
            "books" => "Books & Papers",
            "books & papers" => "Books & Papers",
        );
        return isset($map[$lowerName]) ? $map[$lowerName] : null;
    }

    /**
     * True if any published worksheet row exists for this legacy main_category id.
     */
    private static function mainCategoryHasPublishedWorksheet(PDO $conn, $mainCatId)
    {
        $mid = (int) $mainCatId;
        if ($mid <= 0) {
            return false;
        }
        foreach (array('pdf_details', 'books_details', 'homework_details') as $table) {
            try {
                $st = $conn->prepare(
                    "SELECT 1 FROM `" . str_replace('`', '``', $table) . "` WHERE `status` = 1 AND `main_cat_id` = ? LIMIT 1"
                );
                $st->execute(array($mid));
                if ($st->fetchColumn()) {
                    return true;
                }
            } catch (Throwable $e) {
                continue;
            }
        }
        return false;
    }

    /**
     * Distinct product_categories.id values referenced by published worksheets (Honey Market link).
     *
     * @return array<int, int>
     */
    private static function distinctProductCategoryIdsFromPublishedWorksheets(PDO $conn)
    {
        $out = array();
        foreach (array('pdf_details', 'books_details', 'homework_details') as $table) {
            if (!self::columnExists($conn, $table, 'product_category_id')) {
                continue;
            }
            try {
                $sql = "SELECT DISTINCT `product_category_id` AS pcid FROM `" . str_replace('`', '``', $table)
                    . "` WHERE `status` = 1 AND `product_category_id` IS NOT NULL AND `product_category_id` > 0";
                $s = $conn->query($sql);
                if ($s) {
                    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $id = (int) ($row['pcid'] ?? 0);
                        if ($id > 0) {
                            $out[$id] = $id;
                        }
                    }
                }
            } catch (Throwable $e) {
                continue;
            }
        }
        return array_values($out);
    }

    /**
     * When worksheets are not yet linked via product_category_id, infer shop categories from name → main_category mapping.
     *
     * @return array<int, int>
     */
    private static function fallbackProductCategoryIdsByMappedMainCategory(PDO $conn)
    {
        $out = array();
        foreach (self::loadProductCategoryOptions($conn) as $c) {
            $cid = (int) ($c['id'] ?? 0);
            if ($cid <= 0) {
                continue;
            }
            $mapped = self::mapProductSelectionsToContentCategoryIds($conn, $cid, 0);
            $mid = (int) ($mapped['main_cat_id'] ?? 0);
            if ($mid > 0 && self::mainCategoryHasPublishedWorksheet($conn, $mid)) {
                $out[$cid] = $cid;
            }
        }
        return array_values($out);
    }

    /**
     * Product category ids that have at least one published worksheet (not shop-only).
     * Returns null when we cannot determine links (caller should show full category list).
     *
     * @return array<int, int>|null
     */
    public static function worksheetExplorerProductCategoryIds(PDO $conn)
    {
        $strict = self::distinctProductCategoryIdsFromPublishedWorksheets($conn);
        if (!empty($strict)) {
            return $strict;
        }
        $fb = self::fallbackProductCategoryIdsByMappedMainCategory($conn);
        if (!empty($fb)) {
            return $fb;
        }
        return null;
    }

    /**
     * Shop categories for the homepage EXPLORE form: only categories that have worksheets.
     *
     * @return array<int, array{id:int, name:string}>
     */
    public static function loadProductCategoryOptionsForWorksheetsExplorer(PDO $conn)
    {
        $all = self::loadProductCategoryOptions($conn);
        $ids = self::worksheetExplorerProductCategoryIds($conn);
        if ($ids === null) {
            return $all;
        }
        $flip = array_flip($ids);
        $out = array();
        foreach ($all as $c) {
            if (isset($flip[(int) ($c['id'] ?? 0)])) {
                $out[] = $c;
            }
        }
        return $out;
    }

    /**
     * Subcategories for EXPLORE: only under allowed parent category ids.
     * Pass null to skip filtering (same as loadProductSubcategoryOptions).
     *
     * @param array<int, int>|null $allowedParentCategoryIds
     * @return array<int, array{id:int, product_category_id:int, title:string}>
     */
    public static function loadProductSubcategoryOptionsForWorksheetsExplorer(PDO $conn, $allowedParentCategoryIds = null)
    {
        $subs = self::loadProductSubcategoryOptions($conn);
        if ($allowedParentCategoryIds === null) {
            return $subs;
        }
        if ($allowedParentCategoryIds === array()) {
            return array();
        }
        $flip = array_flip($allowedParentCategoryIds);
        $out = array();
        foreach ($subs as $s) {
            $pid = (int) ($s['product_category_id'] ?? 0);
            if (isset($flip[$pid])) {
                $out[] = $s;
            }
        }
        return $out;
    }

    /** @var int Worksheets (all free-content types) per page on Treasures explorer */
    public static function explorerWorksPerPage()
    {
        return 16;
    }

    /**
     * @return string|null table name or null
     */
    private static function explorerTableForKind($kind)
    {
        $k = (string) $kind;
        if ($k === 'pdf') {
            return 'pdf_details';
        }
        if ($k === 'books') {
            return 'books_details';
        }
        if ($k === 'homework') {
            return 'homework_details';
        }
        return null;
    }

    /**
     * WHERE fragment after `WHERE t.status = 1` for main_cat / sub_cat explorer filters.
     *
     * @param array<string, mixed> $params
     * @return string
     */
    private static function sqlFragmentContentExplorerFilters($langF, $ageF, $mainCatId, $subCatId, array &$params, $paramPrefix)
    {
        $pfx = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $paramPrefix);
        if ($pfx === '') {
            $pfx = 'p';
        }
        $sql = '';
        $sql .= ' AND t.main_cat_id = :' . $pfx . '_mcid';
        $params[':' . $pfx . '_mcid'] = (int) $mainCatId;
        if ((int) $subCatId > 0) {
            $sql .= ' AND t.sub_cat_id = :' . $pfx . '_scid';
            $params[':' . $pfx . '_scid'] = (int) $subCatId;
        }
        if ($langF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:' . $pfx . '_lang))';
            $params[':' . $pfx . '_lang'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :' . $pfx . '_age)';
            $params[':' . $pfx . '_age'] = $ageF;
        }
        return $sql;
    }

    /**
     * WHERE fragment after `WHERE t.status = 1` for Honey Market product category explorer.
     *
     * @param array<string, mixed> $params
     * @return string|null null if table has no product_category_id column
     */
    private static function sqlFragmentProductExplorerFilters(PDO $conn, $table, $langF, $ageF, $productCatId, $productSubId, array &$params, $paramPrefix)
    {
        if (!self::columnExists($conn, $table, 'product_category_id')) {
            return null;
        }
        $pfx = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $paramPrefix);
        if ($pfx === '') {
            $pfx = 'p';
        }
        $sql = '';
        $sql .= ' AND t.product_category_id = :' . $pfx . '_pcid';
        $params[':' . $pfx . '_pcid'] = (int) $productCatId;
        $hasSub = self::columnExists($conn, $table, 'product_subcategory_id');
        if ((int) $productSubId > 0 && $hasSub) {
            $sql .= ' AND t.product_subcategory_id = :' . $pfx . '_psid';
            $params[':' . $pfx . '_psid'] = (int) $productSubId;
        }
        if ($langF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:' . $pfx . '_lang))';
            $params[':' . $pfx . '_lang'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :' . $pfx . '_age)';
            $params[':' . $pfx . '_age'] = $ageF;
        }
        return $sql;
    }

    /**
     * @return array<int, array{ws_kind:string, row:array<string, mixed>}>
     */
    private static function hydrateMergedExplorerRows(PDO $conn, array $unionRows)
    {
        $buckets = array('pdf' => array(), 'books' => array(), 'homework' => array());
        $order = array();
        foreach ($unionRows as $r) {
            if (!is_array($r)) {
                continue;
            }
            $kind = (string) ($r['ws_kind'] ?? '');
            $id = (int) ($r['id'] ?? 0);
            if ($id <= 0 || !isset($buckets[$kind])) {
                continue;
            }
            $buckets[$kind][$id] = true;
            $order[] = array($kind, $id);
        }
        $loaded = array('pdf' => array(), 'books' => array(), 'homework' => array());
        foreach ($buckets as $kind => $idSet) {
            if (!$idSet) {
                continue;
            }
            $table = self::explorerTableForKind($kind);
            if ($table === null) {
                continue;
            }
            $ids = array_values(array_filter(array_map('intval', array_keys($idSet))));
            if ($ids === array()) {
                continue;
            }
            $in = implode(',', $ids);
            $tblEsc = str_replace('`', '``', $table);
            try {
                $st = $conn->query("SELECT * FROM `" . $tblEsc . "` WHERE `id` IN (" . $in . ") AND `status` = 1");
                if (!$st) {
                    continue;
                }
                foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $loaded[$kind][(int) $row['id']] = $row;
                }
            } catch (Throwable $e) {
            }
        }
        $out = array();
        foreach ($order as $pair) {
            $kind = $pair[0];
            $id = $pair[1];
            if (!empty($loaded[$kind][$id])) {
                $out[] = array('ws_kind' => $kind, 'row' => $loaded[$kind][$id]);
            }
        }
        return $out;
    }

    /**
     * Total published worksheets (pdf + books + homework) for content-category explorer.
     */
    public static function countMergedExplorerByContentTaxonomy(PDO $conn, $langF, $ageF, $mainCatId, $subCatId)
    {
        if ((int) $mainCatId <= 0) {
            return 0;
        }
        $total = 0;
        foreach (array('pdf_details', 'books_details', 'homework_details') as $table) {
            $params = array();
            $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '` t WHERE t.status = 1';
            $sql .= self::sqlFragmentContentExplorerFilters($langF, $ageF, $mainCatId, $subCatId, $params, 'cnt_' . $table);
            try {
                $st = $conn->prepare($sql);
                $st->execute($params);
                $total += (int) $st->fetchColumn();
            } catch (Throwable $e) {
            }
        }
        return $total;
    }

    /**
     * One page of merged worksheets for content-category explorer (newest first by `timestamp`).
     *
     * @return array<int, array{ws_kind:string, row:array<string, mixed>}>
     */
    public static function fetchMergedExplorerPageByContentTaxonomy(PDO $conn, $langF, $ageF, $mainCatId, $subCatId, $page, $perPage = null)
    {
        if ((int) $mainCatId <= 0) {
            return array();
        }
        $pp = $perPage === null ? self::explorerWorksPerPage() : max(1, (int) $perPage);
        $pg = max(1, (int) $page);
        $offset = ($pg - 1) * $pp;
        $params = array();
        $parts = array();
        foreach (array(
            array('pdf_details', 'pdf', 'u_pdf'),
            array('books_details', 'books', 'u_books'),
            array('homework_details', 'homework', 'u_hw'),
        ) as $trip) {
            $tbl = $trip[0];
            $kind = $trip[1];
            $pfx = $trip[2];
            $frag = self::sqlFragmentContentExplorerFilters($langF, $ageF, $mainCatId, $subCatId, $params, $pfx);
            $tblEsc = str_replace('`', '``', $tbl);
            $parts[] = "(SELECT '" . $kind . "' AS ws_kind, t.id, t.`timestamp` AS row_ts FROM `" . $tblEsc . "` t WHERE t.status = 1" . $frag . ')';
        }
        $union = implode(' UNION ALL ', $parts) . ' ORDER BY row_ts DESC, id DESC, ws_kind ASC LIMIT ' . (int) $pp . ' OFFSET ' . (int) $offset;
        try {
            $st = $conn->prepare($union);
            $st->execute($params);
            $unionRows = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return array();
        }
        return self::hydrateMergedExplorerRows($conn, $unionRows);
    }

    /**
     * Total for Honey Market product category explorer.
     */
    public static function countMergedExplorerByProductTaxonomy(PDO $conn, $langF, $ageF, $productCatId, $productSubId)
    {
        if ((int) $productCatId <= 0) {
            return 0;
        }
        $total = 0;
        foreach (array('pdf_details', 'books_details', 'homework_details') as $table) {
            if (!self::columnExists($conn, $table, 'product_category_id')) {
                continue;
            }
            $params = array();
            $frag = self::sqlFragmentProductExplorerFilters($conn, $table, $langF, $ageF, $productCatId, $productSubId, $params, 'pcnt_' . $table);
            if ($frag === null) {
                continue;
            }
            $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '` t WHERE t.status = 1' . $frag;
            try {
                $st = $conn->prepare($sql);
                $st->execute($params);
                $total += (int) $st->fetchColumn();
            } catch (Throwable $e) {
            }
        }
        return $total;
    }

    /**
     * One page of merged worksheets for product-category explorer.
     *
     * @return array<int, array{ws_kind:string, row:array<string, mixed>}>
     */
    public static function fetchMergedExplorerPageByProductTaxonomy(PDO $conn, $langF, $ageF, $productCatId, $productSubId, $page, $perPage = null)
    {
        if ((int) $productCatId <= 0) {
            return array();
        }
        $pp = $perPage === null ? self::explorerWorksPerPage() : max(1, (int) $perPage);
        $pg = max(1, (int) $page);
        $offset = ($pg - 1) * $pp;
        $params = array();
        $parts = array();
        foreach (array(
            array('pdf_details', 'pdf', 'up_pdf'),
            array('books_details', 'books', 'up_books'),
            array('homework_details', 'homework', 'up_hw'),
        ) as $trip) {
            $tbl = $trip[0];
            $kind = $trip[1];
            $pfx = $trip[2];
            $frag = self::sqlFragmentProductExplorerFilters($conn, $tbl, $langF, $ageF, $productCatId, $productSubId, $params, $pfx);
            if ($frag === null) {
                continue;
            }
            $tblEsc = str_replace('`', '``', $tbl);
            $parts[] = "(SELECT '" . $kind . "' AS ws_kind, t.id, t.`timestamp` AS row_ts FROM `" . $tblEsc . "` t WHERE t.status = 1" . $frag . ')';
        }
        if ($parts === array()) {
            return array();
        }
        $union = implode(' UNION ALL ', $parts) . ' ORDER BY row_ts DESC, id DESC, ws_kind ASC LIMIT ' . (int) $pp . ' OFFSET ' . (int) $offset;
        try {
            $st = $conn->prepare($union);
            $st->execute($params);
            $unionRows = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return array();
        }
        return self::hydrateMergedExplorerRows($conn, $unionRows);
    }

    /**
     * True when ws_* tables exist and pdf/books/homework rows can store ws_category_id (homepage explore uses this).
     */
    public static function worksheetWsExplorerReady(PDO $conn)
    {
        require_once __DIR__ . '/edi_ws_taxonomy.php';
        if (!EdiWsTaxonomy::tableExists($conn, 'ws_categories') || !EdiWsTaxonomy::tableExists($conn, 'ws_subcategories')) {
            return false;
        }
        foreach (array('pdf_details', 'books_details', 'homework_details') as $t) {
            if (!self::columnExists($conn, $t, 'ws_category_id')) {
                return false;
            }
        }
        return true;
    }

    /**
     * WHERE fragment after `WHERE t.status = 1` for worksheet ws_* explorer filters.
     *
     * @param array<string, mixed> $params
     * @return string|null
     */
    private static function sqlFragmentWsExplorerFilters(PDO $conn, $table, $langF, $ageF, $wsCatId, $wsSubId, array &$params, $paramPrefix)
    {
        if (!self::columnExists($conn, $table, 'ws_category_id')) {
            return null;
        }
        $pfx = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $paramPrefix);
        if ($pfx === '') {
            $pfx = 'w';
        }
        $sql = '';
        $sql .= ' AND t.ws_category_id = :' . $pfx . '_wcid';
        $params[':' . $pfx . '_wcid'] = (int) $wsCatId;
        if ((int) $wsSubId > 0 && self::columnExists($conn, $table, 'ws_subcategory_id')) {
            $sql .= ' AND t.ws_subcategory_id = :' . $pfx . '_wsid';
            $params[':' . $pfx . '_wsid'] = (int) $wsSubId;
        }
        if ($langF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `languages` l WHERE l.id = t.language_id AND LOWER(TRIM(l.title)) = LOWER(:' . $pfx . '_lang))';
            $params[':' . $pfx . '_lang'] = $langF;
        }
        if ($ageF !== '') {
            $sql .= ' AND EXISTS (SELECT 1 FROM `grades` g WHERE g.id = t.grade_id AND TRIM(g.title) = :' . $pfx . '_age)';
            $params[':' . $pfx . '_age'] = $ageF;
        }
        return $sql;
    }

    /**
     * Tag rows for explorer when filtering by ws_* taxonomy ($limit <= 0 = all matches).
     *
     * @return array<int, array{tag?: string}>
     */
    public static function fetchMatchingTagRowsByWsTaxonomy(PDO $conn, $table, $langF, $ageF, $wsCatId, $wsSubId, $limit = 500)
    {
        $wcid = (int) $wsCatId;
        if ($wcid <= 0) {
            return array();
        }
        $params = array();
        $frag = self::sqlFragmentWsExplorerFilters($conn, $table, $langF, $ageF, $wcid, $wsSubId, $params, 'tagws');
        if ($frag === null) {
            return array();
        }
        $sql = 'SELECT t.tag FROM `' . str_replace('`', '``', $table) . '` t WHERE t.status = 1' . $frag;
        $lim = (int) $limit;
        if ($lim > 0) {
            $sql .= ' ORDER BY t.id DESC LIMIT ' . $lim;
        }
        try {
            $st = $conn->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return array();
        }
    }

    /**
     * Total published worksheets for ws_* explorer filters.
     */
    public static function countMergedExplorerByWsTaxonomy(PDO $conn, $langF, $ageF, $wsCatId, $wsSubId)
    {
        if ((int) $wsCatId <= 0) {
            return 0;
        }
        $total = 0;
        foreach (array('pdf_details', 'books_details', 'homework_details') as $table) {
            $params = array();
            $frag = self::sqlFragmentWsExplorerFilters($conn, $table, $langF, $ageF, (int) $wsCatId, (int) $wsSubId, $params, 'wcnt_' . $table);
            if ($frag === null) {
                continue;
            }
            $sql = 'SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '` t WHERE t.status = 1' . $frag;
            try {
                $st = $conn->prepare($sql);
                $st->execute($params);
                $total += (int) $st->fetchColumn();
            } catch (Throwable $e) {
            }
        }
        return $total;
    }

    /**
     * One page of merged worksheets for ws_* explorer (newest first by `timestamp`).
     *
     * @return array<int, array{ws_kind:string, row:array<string, mixed>}>
     */
    public static function fetchMergedExplorerPageByWsTaxonomy(PDO $conn, $langF, $ageF, $wsCatId, $wsSubId, $page, $perPage = null)
    {
        if ((int) $wsCatId <= 0) {
            return array();
        }
        $pp = $perPage === null ? self::explorerWorksPerPage() : max(1, (int) $perPage);
        $pg = max(1, (int) $page);
        $offset = ($pg - 1) * $pp;
        $params = array();
        $parts = array();
        foreach (array(
            array('pdf_details', 'pdf', 'uw_pdf'),
            array('books_details', 'books', 'uw_books'),
            array('homework_details', 'homework', 'uw_hw'),
        ) as $trip) {
            $tbl = $trip[0];
            $kind = $trip[1];
            $pfx = $trip[2];
            $frag = self::sqlFragmentWsExplorerFilters($conn, $tbl, $langF, $ageF, (int) $wsCatId, (int) $wsSubId, $params, $pfx);
            if ($frag === null) {
                continue;
            }
            $tblEsc = str_replace('`', '``', $tbl);
            $parts[] = "(SELECT '" . $kind . "' AS ws_kind, t.id, t.`timestamp` AS row_ts FROM `" . $tblEsc . "` t WHERE t.status = 1" . $frag . ')';
        }
        if ($parts === array()) {
            return array();
        }
        $union = implode(' UNION ALL ', $parts) . ' ORDER BY row_ts DESC, id DESC, ws_kind ASC LIMIT ' . (int) $pp . ' OFFSET ' . (int) $offset;
        try {
            $st = $conn->prepare($union);
            $st->execute($params);
            $unionRows = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return array();
        }
        return self::hydrateMergedExplorerRows($conn, $unionRows);
    }
}
