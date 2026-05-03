<?php

/**
 * Home page section backgrounds stored as single-row `carousel` entries (type suffix _bg)
 * with image files in img/home/.
 */
class EdiHomeSectionImages
{
    public const TYPE_EXPLORE = 'explore_bg';
    public const TYPE_TESTIMONIAL = 'testimonial_bg';
    public const TYPE_FOOTER = 'footer_bg';

    private const DEFAULT_PATH = array(
        self::TYPE_EXPLORE => './img/Web pic/Search image.webp',
        self::TYPE_TESTIMONIAL => './img/Web pic/Trails of tales.webp',
        self::TYPE_FOOTER => './img/Web pic/footer.jpg',
    );

    public static function homeDirFs()
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'home';
    }

    public static function assetUrl(USER $user, $carouselType)
    {
        $default = isset(self::DEFAULT_PATH[$carouselType]) ? self::DEFAULT_PATH[$carouselType] : '';
        $rows = $user->fetchAll(array('src', 'status'), array('carousel'), array('type' => $carouselType), 'id DESC LIMIT 1');
        if (empty($rows) || (string) $rows[0]['status'] !== '1' || $rows[0]['src'] === '') {
            return $default;
        }
        $fn = $rows[0]['src'];
        $full = self::homeDirFs() . DIRECTORY_SEPARATOR . $fn;
        if (!is_file($full)) {
            return $default;
        }
        return './img/home/' . $fn;
    }

    /**
     * Escape for use inside CSS url("…").
     */
    public static function cssUrlString($path)
    {
        return str_replace(array('\\', '"'), array('\\\\', '\\"'), $path);
    }

    private static function safeImageFilename($originalName)
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array($ext, $allowed, true)) {
            return null;
        }
        return bin2hex(random_bytes(8)) . '.' . $ext;
    }

    private static function validateImageUpload($field)
    {
        if (!isset($_FILES[$field]) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
            return 'No file uploaded.';
        }
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            return 'Upload failed (error code ' . (int) $_FILES[$field]['error'] . ').';
        }
        $info = @getimagesize($_FILES[$field]['tmp_name']);
        if ($info === false) {
            return 'File is not a valid image.';
        }
        return null;
    }

    /**
     * @return string|null error message or null on success
     */
    public static function saveUploaded(USER $user, $carouselType, $field)
    {
        $err = self::validateImageUpload($field);
        if ($err !== null) {
            return $err;
        }
        $safe = self::safeImageFilename($_FILES[$field]['name']);
        if ($safe === null) {
            return 'Allowed types: jpg, jpeg, png, gif, webp.';
        }
        $dir = self::homeDirFs();
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return 'Could not create upload directory.';
            }
        }
        $dest = $dir . DIRECTORY_SEPARATOR . $safe;
        if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) {
            return 'Could not save file.';
        }

        $rows = $user->fetchAll(array('id', 'src'), array('carousel'), array('type' => $carouselType), 'id ASC LIMIT 1');
        if (!empty($rows)) {
            $old = $rows[0]['src'];
            if ($old !== '' && $old !== $safe) {
                $oldPath = $dir . DIRECTORY_SEPARATOR . $old;
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $user->updateTable('carousel', array('src' => $safe, 'status' => 1), array('id' => (int) $rows[0]['id']));
        } else {
            $user->insertTable('carousel', array(
                'type' => $carouselType,
                'text1' => '',
                'text2' => '',
                'src' => $safe,
                'display_order' => null,
                'status' => 1,
            ));
        }
        return null;
    }
}
