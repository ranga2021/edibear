<?php

require_once __DIR__ . "/edi_explorer_content.php";

/**
 * Attach product snapshot fields to each order_items row (by product_id) for admin / account UIs.
 *
 * @param PDO   $pdo
 * @param array $itemsByOrder map order_id => list of order_items rows (modified in place)
 */
class EdiOrderLineItems
{
    public static function enrichItemsByOrder(PDO $pdo, array &$itemsByOrder)
    {
        $pids = array();
        foreach ($itemsByOrder as $items) {
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $li) {
                $pid = isset($li["product_id"]) ? (int) $li["product_id"] : 0;
                if ($pid > 0) {
                    $pids[$pid] = true;
                }
            }
        }
        $idList = array_keys($pids);
        if ($idList === array()) {
            return;
        }
        $in = implode(",", array_map("intval", $idList));
        $hasPsub = EdiExplorerContent::columnExists($pdo, "products", "product_subcategory_id");
        $joinSub = $hasPsub
            ? "LEFT JOIN `product_subcategories` ps ON ps.`id` = p.`product_subcategory_id`"
            : "";
        $subSel = $hasPsub ? ", ps.`title` AS `subcategory_name`" : ", '' AS `subcategory_name`";

        $sql = "SELECT p.`id`, p.`image`, p.`language`, p.`age_group`, pc.`name` AS `category_name` $subSel
            FROM `products` p
            LEFT JOIN `product_categories` pc ON pc.`id` = p.`category_id`
            $joinSub
            WHERE p.`id` IN ($in)";

        $meta = array();
        try {
            $st = $pdo->query($sql);
            if ($st) {
                foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $id = (int) ($row["id"] ?? 0);
                    if ($id > 0) {
                        $meta[$id] = $row;
                    }
                }
            }
        } catch (Throwable $e) {
            return;
        }

        foreach ($itemsByOrder as $oid => $items) {
            if (!is_array($items)) {
                continue;
            }
            foreach ($items as $k => $li) {
                $pid = isset($li["product_id"]) ? (int) $li["product_id"] : 0;
                $m = ($pid > 0 && isset($meta[$pid])) ? $meta[$pid] : null;
                $itemsByOrder[$oid][$k]["edi_product_image"] = $m ? trim((string) ($m["image"] ?? "")) : "";
                $itemsByOrder[$oid][$k]["edi_product_language"] = $m ? trim((string) ($m["language"] ?? "")) : "";
                $itemsByOrder[$oid][$k]["edi_product_grade"] = $m ? trim((string) ($m["age_group"] ?? "")) : "";
                $itemsByOrder[$oid][$k]["edi_product_category"] = $m ? trim((string) ($m["category_name"] ?? "")) : "";
                $itemsByOrder[$oid][$k]["edi_product_subcategory"] = $m ? trim((string) ($m["subcategory_name"] ?? "")) : "";
            }
        }
    }
}
