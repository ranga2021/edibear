<?php

/**
 * Helpers for admin add/edit product (gallery slots, safe uploads).
 */
class EdiProductAdmin
{
    const GALLERY_SLOTS = 4;

    /**
     * @return string[]
     */
    public static function gallerySlotsFromDb($raw)
    {
        $n = self::GALLERY_SLOTS;
        $raw = trim((string) $raw);
        if ($raw === '') {
            return array_fill(0, $n, '');
        }
        if ($raw[0] === '[') {
            $d = json_decode($raw, true);
            if (is_array($d)) {
                $out = array_fill(0, $n, '');
                for ($i = 0; $i < $n; $i++) {
                    $out[$i] = isset($d[$i]) ? trim((string) $d[$i]) : '';
                }
                return $out;
            }
        }
        $parts = preg_split('/\s*,\s*/', $raw);
        $out = array_fill(0, $n, '');
        for ($i = 0; $i < $n; $i++) {
            $out[$i] = isset($parts[$i]) ? trim((string) $parts[$i]) : '';
        }
        return $out;
    }

    public static function encodeGallerySlots(array $slots)
    {
        $n = self::GALLERY_SLOTS;
        $out = array_fill(0, $n, '');
        $hasAny = false;
        for ($i = 0; $i < $n; $i++) {
            $out[$i] = isset($slots[$i]) ? (string) $slots[$i] : '';
            if ($out[$i] !== '') {
                $hasAny = true;
            }
        }
        return $hasAny ? json_encode($out, JSON_UNESCAPED_UNICODE) : null;
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
