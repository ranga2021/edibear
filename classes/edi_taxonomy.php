<?php

/**
 * Shared languages + grades for homepage explorer and admin product forms.
 * Uses PDO (not USER::fetchAll with empty WHERE).
 */
class EdiTaxonomy
{
    public static function loadLanguages(PDO $conn)
    {
        $rows = array();
        try {
            $st = $conn->query("SELECT id, title FROM languages ORDER BY id ASC");
            if ($st) {
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        if (count($rows) === 0) {
            return array(
                array("id" => 1, "title" => "Sinhala"),
                array("id" => 2, "title" => "English"),
                array("id" => 3, "title" => "Tamil"),
            );
        }
        $order = array("Sinhala" => 1, "English" => 2, "Tamil" => 3);
        usort($rows, function ($a, $b) use ($order) {
            $ta = trim((string) ($a["title"] ?? ""));
            $tb = trim((string) ($b["title"] ?? ""));
            $oa = $order[$ta] ?? 99;
            $ob = $order[$tb] ?? 99;
            if ($oa === $ob) {
                return strcasecmp($ta, $tb);
            }
            return $oa <=> $ob;
        });
        return $rows;
    }

    public static function gradeSortKey($title)
    {
        $t = trim((string) $title);
        if ($t === "") {
            return 9999;
        }
        if (strcasecmp($t, "Pre School") === 0 || strcasecmp($t, "Pre-School") === 0 || strcasecmp($t, "Pre school") === 0) {
            return 0;
        }
        if (preg_match('/^Grade[\s-]*(\d+)$/i', $t, $m)) {
            return 100 + (int) $m[1];
        }
        return 500 + crc32($t);
    }

    /**
     * True when title is a numeric "Grade N" / "Grade-N" with N > 5 (shop explorer caps at Grade 5).
     */
    public static function isNumericGradeAboveFive($title)
    {
        $t = trim((string) $title);
        if (preg_match('/^Grade[\s-]*(\d+)$/i', $t, $m)) {
            return (int) $m[1] > 5;
        }
        return false;
    }

    public static function loadGrades(PDO $conn, $adminAll = false)
    {
        $rows = array();
        try {
            $st = $conn->query("SELECT id, title FROM grades ORDER BY id ASC");
            if ($st) {
                $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {
            $rows = array();
        }
        if (count($rows) === 0) {
            $rows = array(array("id" => 1, "title" => "Pre School"));
            for ($g = 1; $g <= 5; $g++) {
                $rows[] = array("id" => $g + 1, "title" => "Grade " . $g);
            }
            return $rows;
        }
        usort($rows, function ($a, $b) {
            $ka = self::gradeSortKey($a["title"] ?? "");
            $kb = self::gradeSortKey($b["title"] ?? "");
            if ($ka === $kb) {
                return strcasecmp((string) ($a["title"] ?? ""), (string) ($b["title"] ?? ""));
            }
            return $ka <=> $kb;
        });
        if (!$adminAll) {
            $rows = array_values(array_filter($rows, function ($r) {
                return !self::isNumericGradeAboveFive($r["title"] ?? "");
            }));
        }
        return $rows;
    }

    public static function addGrade(PDO $conn, $title)
    {
        $title = trim((string) $title);
        if ($title === "") {
            return array("ok" => false, "error" => "Grade title is required.");
        }
        $st = $conn->prepare("SELECT id FROM grades WHERE title = :t LIMIT 1");
        $st->execute(array(":t" => $title));
        if ($st->fetch()) {
            return array("ok" => false, "error" => "A grade with that name already exists.");
        }
        $ins = $conn->prepare("INSERT INTO grades (title) VALUES (:t)");
        $ins->execute(array(":t" => $title));
        $newId = (int) $conn->lastInsertId();
        return array("ok" => true, "id" => $newId, "title" => $title);
    }

    public static function allowedTitles($rows)
    {
        $out = array();
        foreach ($rows as $r) {
            $t = trim((string) ($r["title"] ?? ""));
            if ($t !== "") {
                $out[$t] = true;
            }
        }
        return array_keys($out);
    }

    /**
     * Read content_language_id and content_grade_id from POST, validated against
     * rows from loadLanguages / loadGrades. Returns int ids or null if empty/invalid.
     */
    public static function contentLanguageGradeFromPost(array $langRows, array $gradeRows)
    {
        $pl = (int) ($_POST["content_language_id"] ?? 0);
        $pg = (int) ($_POST["content_grade_id"] ?? 0);
        $okL = null;
        $okG = null;
        foreach ($langRows as $r) {
            if ((int) ($r["id"] ?? 0) === $pl && $pl > 0) {
                $okL = $pl;
                break;
            }
        }
        foreach ($gradeRows as $r) {
            if ((int) ($r["id"] ?? 0) === $pg && $pg > 0) {
                $okG = $pg;
                break;
            }
        }
        return array("language_id" => $okL, "grade_id" => $okG);
    }
}
