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
        if (strpos($cell, " ||| ") !== false) {
            $out = array();
            foreach (explode(" ||| ", $cell, 3) as $p) {
                $p = trim((string) $p);
                if ($p !== "" && !in_array($p, $out, true)) {
                    $out[] = $p;
                }
            }
            return $out;
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
     * Blog admin tag cell: Language ||| Grade ||| Category, or legacy single string (→ category only).
     *
     * @return array{0:string,1:string,2:string}
     */
    public static function blogTagTripleParts($cell)
    {
        $cell = trim((string) $cell);
        if ($cell === "") {
            return array("", "", "");
        }
        if (strpos($cell, " ||| ") !== false) {
            $p = explode(" ||| ", $cell, 3);
            return array(
                trim((string) ($p[0] ?? "")),
                trim((string) ($p[1] ?? "")),
                trim((string) ($p[2] ?? "")),
            );
        }
        return array("", "", $cell);
    }

    /**
     * One-line label for blog cards / hero: category or topic only (third segment of
     * Language ||| Grade ||| Category). Legacy posts use the full tag cell.
     * Plain text — escape when outputting HTML.
     *
     * @return string
     */
    public static function blogCategoryDisplayLabel($cell)
    {
        $parts = self::blogTagTripleParts($cell);
        return trim((string) ($parts[2] ?? ""));
    }

    /**
     * Topic tags for display/filtering on blog pages: excludes Language and Grade from
     * "Language ||| Grade ||| Category" admin cells; legacy cells use slash-split topics only.
     *
     * @return string[]
     */
    public static function blogTopicTagsFromCell($cell)
    {
        $cell = trim((string) $cell);
        if ($cell === "") {
            return array();
        }
        if (strpos($cell, " ||| ") !== false) {
            $parts = self::blogTagTripleParts($cell);
            $topic = trim((string) ($parts[2] ?? ""));
            return self::splitTags($topic);
        }
        return self::splitTags($cell);
    }

    /**
     * Distinct blog topic tags (not language/grade) from rows with a tag column.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return string[]
     */
    public static function distinctBlogTopicTagsFromRows(array $rows, $col = "tag")
    {
        $all = array();
        foreach ($rows as $row) {
            foreach (self::blogTopicTagsFromCell(isset($row[$col]) ? (string) $row[$col] : "") as $t) {
                if ($t !== "" && !in_array($t, $all, true)) {
                    $all[] = $t;
                }
            }
        }
        sort($all, SORT_NATURAL | SORT_FLAG_CASE);
        return $all;
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
     * @param string|null $inlineFilterTargetSelector when set (e.g. "#edi-explorer-region-pdf"), render buttons that filter cards in-page instead of linking out
     */
    public static function renderExplorerCommaTagBarHtml(array $tags, $listPhp, array $preserveQuery, $visibleFirst = 12, $uidSuffix = "x", $inlineFilterTargetSelector = null)
    {
        if (count($tags) === 0) {
            return "";
        }
        $visibleFirst = max(1, (int) $visibleFirst);
        $moreId = "edi-explorer-tags-more-" . $uidSuffix;
        $btnId = "edi-explorer-tags-btn-" . $uidSuffix;
        $useInlineFilter = is_string($inlineFilterTargetSelector) && trim($inlineFilterTargetSelector) !== "";
        $barClass = "edi-explorer-tag-bar mb-3";
        if ($useInlineFilter) {
            $barClass .= " edi-explorer-tag-bar--filter";
        }
        $dataTarget = "";
        if ($useInlineFilter) {
            $dataTarget = ' data-edi-filter-target="' . htmlspecialchars(trim($inlineFilterTargetSelector), ENT_QUOTES, "UTF-8") . '"';
        }

        $html = '<div class="' . htmlspecialchars($barClass, ENT_QUOTES, "UTF-8") . '" role="navigation" aria-label="Tags"' . $dataTarget . ">";
        $n = count($tags);
        $show = min($n, $visibleFirst);
        $printed = 0;
        if ($useInlineFilter) {
            $html .= '<button type="button" class="edi-explorer-tag-btn is-selected" data-edi-tag="" aria-pressed="true">All</button>';
            $printed = 1;
        }
        for ($i = 0; $i < $show; $i++) {
            if ($printed > 0) {
                $html .= '<span class="edi-explorer-tag-sep">, </span>';
            }
            $label = htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8");
            if ($useInlineFilter) {
                $html .= '<button type="button" class="edi-explorer-tag-btn" data-edi-tag="' . $label . '" aria-pressed="false">' . $label . "</button>";
            } else {
                $q = array_merge($preserveQuery, array("tag" => $tags[$i]));
                $href = $listPhp . "?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
                $html .= '<a class="edi-explorer-tag-link" href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '">' . $label . "</a>";
            }
            $printed++;
        }
        if ($n > $show) {
            $html .= '<span id="' . htmlspecialchars($moreId, ENT_QUOTES, "UTF-8") . '" class="edi-explorer-tag-more" hidden>';
            for ($i = $show; $i < $n; $i++) {
                if ($printed > 0) {
                    $html .= '<span class="edi-explorer-tag-sep">, </span>';
                }
                $label = htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8");
                if ($useInlineFilter) {
                    $html .= '<button type="button" class="edi-explorer-tag-btn" data-edi-tag="' . $label . '" aria-pressed="false">' . $label . "</button>";
                } else {
                    $q = array_merge($preserveQuery, array("tag" => $tags[$i]));
                    $href = $listPhp . "?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
                    $html .= '<a class="edi-explorer-tag-link" href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '">' . $label . "</a>";
                }
                $printed++;
            }
            $html .= "</span>";
            $html .= ' <button type="button" class="edi-explorer-tag-seemore btn btn-link p-0 align-baseline text-warning font-weight-bold" id="' . htmlspecialchars($btnId, ENT_QUOTES, "UTF-8") . '">See more</button>';
            $html .= "<script>(function(){var b=document.getElementById(" . json_encode($btnId) . ");var m=document.getElementById(" . json_encode($moreId) . ");if(!b||!m)return;b.addEventListener(\"click\",function(){m.hidden=!m.hidden;b.textContent=m.hidden?\"See more\":\"See less\";});})();</script>";
        }
        $html .= "</div>";
        return $html;
    }

    /**
     * Comma-separated tag links for The Hidden Den (blogs listing) or a single blog hero,
     * matching the free-resource (e.g. PDF) tag strip pattern: comma links + See more.
     *
     * @param string[] $tags Distinct tag strings (e.g. from distinctFromRows or splitTags)
     * @param int        $visibleFirst Number of tags shown before "See more"
     * @param string     $uidSuffix    Unique suffix for DOM ids (e.g. "den" or "post-12")
     */
    public static function renderBlogTagChipsHtml(array $tags, $visibleFirst = 20, $uidSuffix = "blogs")
    {
        if (count($tags) === 0) {
            return "";
        }
        $visibleFirst = max(1, (int) $visibleFirst);
        $uidSuffix = preg_replace("/[^a-zA-Z0-9_-]/", "", (string) $uidSuffix);
        if ($uidSuffix === "") {
            $uidSuffix = "blogs";
        }

        $n = count($tags);
        $show = min($n, $visibleFirst);
        $moreId = "edi-blog-tags-more-" . $uidSuffix;
        $btnId = "edi-blog-tags-btn-" . $uidSuffix;

        $html = '<div class="edi-blog-tag-chips text-dark" role="navigation" aria-label="Tags">';
        for ($i = 0; $i < $show; $i++) {
            if ($i > 0) {
                $html .= '<span class="text-muted edi-blog-tag-sep">, </span> ';
            }
            $q = array("tag" => $tags[$i]);
            $href = "./blogs?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
            $safeWord = htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8");
            $html .= '<a href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '" class="edi-blog-topic-link">' . $safeWord . "</a>";
        }
        if ($n > $show) {
            $html .= ' <span class="text-muted">&hellip;</span> ';
            $html .= '<button type="button" id="' . htmlspecialchars($btnId, ENT_QUOTES, "UTF-8") . '" class="edi-blog-tag-seemore btn btn-link p-0 align-baseline text-warning font-weight-bold">See more</button>';
            $html .= '<div id="' . htmlspecialchars($moreId, ENT_QUOTES, "UTF-8") . '" class="edi-blog-tag-more-wrap mt-2" style="display:none">';
            for ($i = $show; $i < $n; $i++) {
                $q = array("tag" => $tags[$i]);
                $href = "./blogs?" . http_build_query($q, "", "&", PHP_QUERY_RFC3986);
                $safeWord = htmlspecialchars($tags[$i], ENT_QUOTES, "UTF-8");
                $html .= ' <a href="' . htmlspecialchars($href, ENT_QUOTES, "UTF-8") . '" class="btn btn-sm btn-light border px-2 py-0 mb-1 mr-1 text-dark">' . $safeWord . "</a>";
            }
            $html .= "</div>";
            $html .= "<script>(function(){var b=document.getElementById(" . json_encode($btnId) . ");var h=document.getElementById(" . json_encode($moreId) . ");if(!b||!h)return;b.addEventListener(\"click\",function(){var open=h.style.display===\"block\";h.style.display=open?\"none\":\"block\";b.textContent=open?\"See more\":\"See less\";});})();</script>";
        }
        $html .= "</div>";
        return $html;
    }
}
