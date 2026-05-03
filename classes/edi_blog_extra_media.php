<?php

/**
 * Extra gallery images and YouTube URLs for blog posts (table blog_extra_media).
 */
class EdiBlogExtraMedia
{
    public static function tableExists(PDO $conn)
    {
        try {
            $st = $conn->query("SHOW TABLES LIKE 'blog_extra_media'");
            return $st && $st->rowCount() > 0;
        } catch (Throwable $e) {
            return false;
        }
    }

    public static function fetchForBlog(PDO $conn, $blogId)
    {
        $blogId = (int) $blogId;
        if ($blogId < 1 || !self::tableExists($conn)) {
            return array();
        }
        $st = $conn->prepare(
            "SELECT `id`, `media_type`, `path`, `caption`, `sort_order` FROM `blog_extra_media` WHERE `blog_id` = :bid ORDER BY `sort_order` ASC, `id` ASC"
        );
        $st->execute(array(':bid' => $blogId));
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : array();
    }

    /**
     * Replace all extra media rows for a blog from admin POST (8 slots).
     *
     * @param USER   $user
     * @param int    $blogId
     * @param string $uploadDir Absolute filesystem path (e.g. .../img/blogs)
     * @param string $uploadUrlPrefix Web path prefix for src (e.g. ../img/blogs/)
     */
    public static function syncFromAdminPost($user, $blogId, $uploadDir, $uploadUrlPrefix = '../img/blogs/')
    {
        $conn = $user->getConnection();
        if (!self::tableExists($conn)) {
            return;
        }
        $blogId = (int) $blogId;
        if ($blogId < 1) {
            return;
        }
        $user->deleteTableRow('blog_extra_media', array('blog_id' => $blogId));

        $sort = 0;
        for ($i = 0; $i < 8; $i++) {
            $kind = isset($_POST['extra_kind_' . $i]) ? strtolower(trim((string) $_POST['extra_kind_' . $i])) : '';
            $caption = isset($_POST['extra_caption_' . $i]) ? trim((string) $_POST['extra_caption_' . $i]) : '';
            $caption = mb_substr($caption, 0, 250, 'UTF-8');
            $videoUrl = isset($_POST['extra_video_url_' . $i]) ? trim((string) $_POST['extra_video_url_' . $i]) : '';
            $existing = isset($_POST['extra_image_existing_' . $i]) ? trim((string) $_POST['extra_image_existing_' . $i]) : '';
            $existing = basename(str_replace('\\', '/', $existing));

            if ($kind === 'video') {
                if ($videoUrl === '' || $videoUrl === '.') {
                    continue;
                }
                $user->insertTable(
                    'blog_extra_media',
                    array(
                        'blog_id' => $blogId,
                        'sort_order' => $sort,
                        'media_type' => 'video',
                        'path' => mb_substr($videoUrl, 0, 500, 'UTF-8'),
                        'caption' => $caption,
                    ),
                    false
                );
                $sort++;
                continue;
            }
            if ($kind !== 'image') {
                continue;
            }

            $fileName = '';
            $field = 'extra_image_' . $i;
            if (!empty($_FILES[$field]['name']) && is_uploaded_file($_FILES[$field]['tmp_name'])) {
                $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
                $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
                if ($ext === '') {
                    $ext = 'jpg';
                }
                $fileName = $blogId . '-extra-' . time() . '-' . $i . '.' . strtolower($ext);
                $dest = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
                if (!@move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
                    $fileName = '';
                }
            }
            if ($fileName === '' && $existing !== '') {
                $fileName = $existing;
            }
            if ($fileName === '') {
                continue;
            }
            $user->insertTable(
                'blog_extra_media',
                array(
                    'blog_id' => $blogId,
                    'sort_order' => $sort,
                    'media_type' => 'image',
                    'path' => $fileName,
                    'caption' => $caption,
                ),
                false
            );
            $sort++;
        }
    }
}
