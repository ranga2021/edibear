<?php

require_once __DIR__ . '/edi_explorer_content.php';

/**
 * Admin worksheet list rows (pdf / books / homework) with taxonomy labels.
 */
class EdiWorksheetAdminList
{
    private static function allowedTable($table)
    {
        return in_array($table, array('pdf_details', 'books_details', 'homework_details'), true);
    }

    /**
     * @return array<int, array{id:int,tag:string,title:string,status:string,lang_title:string,grade_title:string,subcat_title:string}>
     */
    public static function fetchRows(PDO $conn, $table, $searchTitle = '')
    {
        if (!self::allowedTable($table)) {
            return array();
        }
        $hasProductSub = EdiExplorerContent::columnExists($conn, $table, 'product_subcategory_id');
        if ($hasProductSub) {
            $subSelect = 'COALESCE(ps.title, \'\') AS subcat_title';
            $subJoin = 'LEFT JOIN `product_subcategories` ps ON ps.id = d.`product_subcategory_id`';
        } else {
            $subSelect = 'COALESCE(sc.title, \'\') AS subcat_title';
            $subJoin = 'LEFT JOIN `sub_category` sc ON sc.id = d.`sub_cat_id`';
        }
        $sql = "SELECT d.`id`, d.`tag`, d.`title`, d.`status`,
            COALESCE(lg.`title`, '') AS lang_title,
            COALESCE(gr.`title`, '') AS grade_title,
            $subSelect
            FROM `$table` d
            LEFT JOIN `languages` lg ON lg.`id` = d.`language_id`
            LEFT JOIN `grades` gr ON gr.`id` = d.`grade_id`
            $subJoin";
        $params = array();
        $q = trim((string) $searchTitle);
        if ($q !== '') {
            $sql .= ' WHERE d.`title` LIKE ?';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY d.`timestamp` DESC';
        try {
            $st = $conn->prepare($sql);
            $st->execute($params);
            return $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return array();
        }
    }
}
