<?php

/**
 * Sync blog_descriptions rows from admin add/edit blog form.
 * Field names: inputBlogDescription{1..N}, inputBlogImageOne{N}, inputBlogImageTwo{N},
 * optional desc_image_01_existing_{N}, desc_image_02_existing_{N} (basename only).
 */
class EdiBlogStorySections
{
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
                ),
                array('id' => $descID)
            );
        }
    }
}
