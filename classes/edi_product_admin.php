<?php

/**
 * Helpers for admin add/edit product (gallery slots, safe uploads).
 */
class EdiProductAdmin
{
    /**
     * @return array{0:string,1:string,2:string}
     */
    public static function gallerySlotsFromDb($raw)
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return array('', '', '');
        }
        if ($raw[0] === '[') {
            $d = json_decode($raw, true);
            if (is_array($d)) {
                $out = array('', '', '');
                for ($i = 0; $i < 3; $i++) {
                    $out[$i] = isset($d[$i]) ? trim((string) $d[$i]) : '';
                }
                return $out;
            }
        }
        $parts = preg_split('/\s*,\s*/', $raw);
        $out = array('', '', '');
        for ($i = 0; $i < 3; $i++) {
            $out[$i] = isset($parts[$i]) ? trim((string) $parts[$i]) : '';
        }
        return $out;
    }

    /**
     * @param array{0:string,1:string,2:string} $slots
     */
    public static function encodeGallerySlots(array $slots)
    {
        $a = isset($slots[0]) ? (string) $slots[0] : '';
        $b = isset($slots[1]) ? (string) $slots[1] : '';
        $c = isset($slots[2]) ? (string) $slots[2] : '';
        if ($a === '' && $b === '' && $c === '') {
            return null;
        }
        return json_encode(array($a, $b, $c), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Move one uploaded file into $uploadDir; returns basename or '' if skipped.
     *
     * @param array<string,mixed>|null $file One $_FILES element
     */
    public static function saveUploadedProductImage($file, $uploadDir)
    {
        if (!is_array($file) || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return '';
        }
        $orig = isset($file['name']) ? basename((string) $file['name']) : '';
        $ext = pathinfo($orig, PATHINFO_EXTENSION);
        $ext = $ext !== '' ? preg_replace('/[^a-zA-Z0-9]/', '', $ext) : 'jpg';
        $base = pathinfo($orig, PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) $base);
        if ($base === '' || $base === '_') {
            $base = 'img';
        }
        $safe = $base . '_' . bin2hex(random_bytes(4)) . ($ext !== '' ? '.' . $ext : '');
        $dest = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $safe;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return '';
        }
        return $safe;
    }
}
