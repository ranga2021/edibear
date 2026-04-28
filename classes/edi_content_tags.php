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
}
