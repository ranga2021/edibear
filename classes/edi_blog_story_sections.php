<?php

/**
 * Sync blog_descriptions rows from admin add/edit blog form.
 * Field names: inputBlogDescription{1..N}, inputBlogImageOne{N}, inputBlogImageTwo{N},
 * optional desc_image_01_existing_{N}, desc_image_02_existing_{N} (basename only).
 */
class EdiBlogStorySections
{
    private static function columnExists(PDO $conn, $table, $col)
    {
        try {
            $t = str_replace(array('`', '.'), array('``', ''), (string) $table);
            $c = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $col);
            if ($t === '' || $c === '') {
                return false;
            }
            $st = $conn->query("SHOW COLUMNS FROM `" . $t . "` LIKE " . $conn->quote($c));
            return $st && $st->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function ensureCaptionColumns(PDO $conn)
    {
        if (!self::columnExists($conn, 'blog_descriptions', 'image_01_caption')) {
            try {
                $conn->exec("ALTER TABLE `blog_descriptions` ADD COLUMN `image_01_caption` VARCHAR(255) NULL DEFAULT NULL");
            } catch (Throwable $e) {
            }
        }
        if (!self::columnExists($conn, 'blog_descriptions', 'image_02_caption')) {
            try {
                $conn->exec("ALTER TABLE `blog_descriptions` ADD COLUMN `image_02_caption` VARCHAR(255) NULL DEFAULT NULL");
            } catch (Throwable $e) {
            }
        }
    }

    public static function fetchForBlog(PDO $conn, $blogId)
    {
        $blogId = (int) $blogId;
        if ($blogId < 1) {
            return array();
        }
        self::ensureCaptionColumns($conn);
        $hasC1 = self::columnExists($conn, 'blog_descriptions', 'image_01_caption');
        $hasC2 = self::columnExists($conn, 'blog_descriptions', 'image_02_caption');
        $sql = "SELECT `id`, `description`, `image_01`, `image_02`";
        $sql .= $hasC1 ? ", `image_01_caption`" : ", '' AS `image_01_caption`";
        $sql .= $hasC2 ? ", `image_02_caption`" : ", '' AS `image_02_caption`";
        $sql .= " FROM `blog_descriptions` WHERE `blog_id` = ? ORDER BY `id` ASC";
        try {
            $st = $conn->prepare($sql);
            $st->execute(array($blogId));
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            return is_array($rows) ? $rows : array();
        } catch (Throwable $e) {
            return array();
        }
    }

    /**
     * @param USER   $user
     * @param int    $blogId
     * @param int    $slotCount  Number of form slots (1..12)
     * @param string $absUploadDir Absolute path to img/blogs
     * @param bool   $replaceExisting If true, delete existing rows for blog_id (and unlink old images) before insert
     */
    public static function syncFromAdminPost($user, $blogId, $slotCount, $absUploadDir, $replaceExisting)
    {
        $blogId = (int) $blogId;
        $slotCount = min(12, max(1, (int) $slotCount));
        if ($blogId < 1) {
            return;
        }
        $conn = $user->getConnection();
        self::ensureCaptionColumns($conn);

        $absUploadDir = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $absUploadDir), DIRECTORY_SEPARATOR);

        if ($replaceExisting) {
            $keepFiles = array();
            for ($k = 1; $k <= 12; $k++) {
                $k1 = isset($_POST["desc_image_01_existing_$k"]) ? basename(str_replace('\\', '/', (string) $_POST["desc_image_01_existing_$k"])) : '';
                $k2 = isset($_POST["desc_image_02_existing_$k"]) ? basename(str_replace('\\', '/', (string) $_POST["desc_image_02_existing_$k"])) : '';
                if ($k1 !== '') {
                    $keepFiles[$k1] = true;
                }
                if ($k2 !== '') {
                    $keepFiles[$k2] = true;
                }
            }

            $old = $user->fetchAll(
                array('image_01', 'image_02'),
                array('blog_descriptions'),
                array('blog_id' => $blogId)
            );
            if (is_array($old)) {
                foreach ($old as $o) {
                    foreach (array('image_01', 'image_02') as $col) {
                        $fn = basename(str_replace('\\', '/', trim((string) ($o[$col] ?? ''))));
                        if ($fn !== '' && empty($keepFiles[$fn])) {
                            $p = $absUploadDir . DIRECTORY_SEPARATOR . $fn;
                            if (is_file($p)) {
                                @unlink($p);
                            }
                        }
                    }
                }
            }
            $user->deleteTableRow('blog_descriptions', array('blog_id' => $blogId));
        }

        for ($i = 1; $i <= $slotCount; $i++) {
            $desc = strip_tags(isset($_POST["inputBlogDescription$i"]) ? (string) $_POST["inputBlogDescription$i"] : '', '<br>');
            $cap1 = isset($_POST["inputBlogImageOneCaption$i"]) ? trim((string) $_POST["inputBlogImageOneCaption$i"]) : '';
            $cap2 = isset($_POST["inputBlogImageTwoCaption$i"]) ? trim((string) $_POST["inputBlogImageTwoCaption$i"]) : '';
            $cap1 = mb_substr($cap1, 0, 255, 'UTF-8');
            $cap2 = mb_substr($cap2, 0, 255, 'UTF-8');

            $has1 = !empty($_FILES["inputBlogImageOne$i"]['name']);
            $has2 = !empty($_FILES["inputBlogImageTwo$i"]['name']);

            $ex1 = isset($_POST["desc_image_01_existing_$i"]) ? basename(str_replace('\\', '/', (string) $_POST["desc_image_01_existing_$i"])) : '';
            $ex2 = isset($_POST["desc_image_02_existing_$i"]) ? basename(str_replace('\\', '/', (string) $_POST["desc_image_02_existing_$i"])) : '';

            if ($desc === '' && !$has1 && !$has2 && $ex1 === '' && $ex2 === '') {
                continue;
            }

            $descID = $user->insertTable(
                'blog_descriptions',
                array(
                    'blog_id' => $blogId,
                    'description' => $desc,
                    'image_01' => '',
                    'image_02' => '',
                ),
                true
            );

            if ($descID === false) {
                continue;
            }

            $img1 = '';
            $img2 = '';

            if ($has1) {
                $ext = pathinfo((string) $_FILES["inputBlogImageOne$i"]['name'], PATHINFO_EXTENSION);
                $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                if ($ext === '') {
                    $ext = 'jpg';
                }
                $img1 = $blogId . '-' . $descID . '-1.' . strtolower($ext);
                @move_uploaded_file($_FILES["inputBlogImageOne$i"]['tmp_name'], $absUploadDir . DIRECTORY_SEPARATOR . $img1);
            } elseif ($ex1 !== '') {
                $img1 = $ex1;
            }

            if ($has2) {
                $ext = pathinfo((string) $_FILES["inputBlogImageTwo$i"]['name'], PATHINFO_EXTENSION);
                $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                if ($ext === '') {
                    $ext = 'jpg';
                }
                $img2 = $blogId . '-' . $descID . '-2.' . strtolower($ext);
                @move_uploaded_file($_FILES["inputBlogImageTwo$i"]['tmp_name'], $absUploadDir . DIRECTORY_SEPARATOR . $img2);
            } elseif ($ex2 !== '') {
                $img2 = $ex2;
            }

            $user->updateTable(
                'blog_descriptions',
                array(
                    'image_01' => $img1,
                    'image_02' => $img2,
                    'image_01_caption' => $cap1,
                    'image_02_caption' => $cap2,
                ),
                array('id' => $descID)
            );
        }
    }
}
