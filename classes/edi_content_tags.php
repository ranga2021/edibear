<?php

/**
 * Free content list pages: parse admin "tag" field (slash-separated) and build query strings.
 */
class EdiContentTags
{
    /**
     * Split the tag cell from the admin form (e.g. "Animals / Fun / Grade 1").
     *
     * @return string[]
     */
    public static function splitTags($cell)
    {
        $cell = trim((string) $cell);
        if ($cell === "") {
            return array();
        }
        $out = array();
        foreach (explode("/", $cell) as $p) {
            $p = trim($p);
            if ($p !== "" && !in_array($p, $out, true)) {
                $out[] = $p;
            }
        }
        return $out;
    }

    /**
     * Distinct tags from result rows (tag column only).
     *
     * @param array<int, array<string, mixed>> $rows
     * @return string[]
     */
    public static function distinctFromRows($rows, $col = "tag")
    {
        $all = array();
        foreach ($rows as $row) {
            foreach (self::splitTags(isset($row[$col]) ? (string) $row[$col] : "") as $t) {
                if (!in_array($t, $all, true)) {
                    $all[] = $t;
                }
            }
        }
        sort($all, SORT_NATURAL | SORT_FLAG_CASE);
        return $all;
    }

    /**
     * Preserve list filters in tag/topic links (not tag itself).
     *
     * @return array<string, string>
     */
    public static function preserveListParams($language, $grade, $main_cat_id, $sub_cat_id)
    {
        $q = array();
        if ($language !== "") {
            $q["language"] = $language;
        }
        if ($grade !== "") {
            $q["grade"] = $grade;
        }
        if ($main_cat_id !== "") {
            $q["main_cat_id"] = (string) (int) $main_cat_id;
        }
        if ($sub_cat_id !== "") {
            $q["sub_cat_id"] = (string) (int) $sub_cat_id;
        }
        return $q;
    }

    /**
     * Comma-separated tag links (mockup style) for product_page explorer sections.
     *
     * @param string[] $tags
     * @param string   $listPhp e.g. "pdf.php"
     * @param array<string, string> $preserveQuery language, grade, main_cat_id, sub_cat_id
     */
    public static function renderExplorerCommaTagBarHtml(array $tags, $listPhp, array $preserveQuery, $visibleFirst = 12, $uidSuffix = "x")
    {
        if (count($tags) === 0) {
            return "";
        }
        $visibleFirst = max(1, (int) $visibleFirst);
        $moreId = "edi-explorer-tags-more-" . $uidSuffix;
        $btnId = "edi-explorer-tags-btn-" . $uidSuffix;

        $html = '<div class="edi-explorer-tag-bar mb-3" role="navigation" aria-label="Tags">';
        $n = count($tags);
        $show = min($n, $visibleFirst);
        for ($i = 0; $i < $show; $i++) {
            if ($i > 0) {
                $html .= '<span class="edi-explorer-tag-sep">, </span>';
            }
            $q = array_merge($preserveQuery, array("tag" => $tags[$i]));
            $href = $listPhp . "?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
            $html .= '<a class="edi-explorer-tag-link" href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '">' . htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8") . "</a>";
        }
        if ($n > $show) {
            $html .= '<span id="' . htmlspecialchars($moreId, ENT_QUOTES, "UTF-8") . '" class="edi-explorer-tag-more" hidden>';
            for ($i = $show; $i < $n; $i++) {
                $html .= '<span class="edi-explorer-tag-sep">, </span>';
                $q = array_merge($preserveQuery, array("tag" => $tags[$i]));
                $href = $listPhp . "?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
                $html .= '<a class="edi-explorer-tag-link" href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '">' . htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8") . "</a>";
            }
            $html .= "</span>";
            $html .= ' <button type="button" class="edi-explorer-tag-seemore btn btn-link p-0 align-baseline text-warning font-weight-bold" id="' . htmlspecialchars($btnId, ENT_QUOTES, "UTF-8") . '">See more</button>';
            $html .= "<script>(function(){var b=document.getElementById(" . json_encode($btnId) . ");var m=document.getElementById(" . json_encode($moreId) . ");if(!b||!m)return;b.addEventListener(\"click\",function(){m.hidden=!m.hidden;b.textContent=m.hidden?\"See more\":\"See less\";});})();</script>";
        }
        $html .= "</div>";
        return $html;
    }
}
