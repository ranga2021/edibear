<?php

/**
 * Regenerate public sitemap.xml from database (same logic as admin createSiteMap).
 * Safe to call after content saves — avoids a separate browser round-trip.
 *
 * @param USER $user Connected user instance (for fetchAll).
 * @return bool True if file was written, false on failure.
 */
function edi_regenerate_public_sitemap(USER $user)
{
    try {
        date_default_timezone_set('Asia/Colombo');
        $host = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : 'localhost';
        $https = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';
        $baseDomain = ($https ? 'https://' : 'http://') . $host;

        $webPages = array(
            array('', '2022-12-10', '1.0'),
            array('blogs', '2022-12-10', '1.0'),
            array('testimonials', '2022-12-10', '0.9'),
            array('about', '2022-12-10', '0.8'),
            array('login', '2022-12-10', '0.8'),
        );

        $append = function (array $rows, $pathKey, $dateKey) use (&$webPages) {
            foreach ($rows as $row) {
                $id = isset($row['id']) ? (int) $row['id'] : 0;
                if ($id < 1) {
                    continue;
                }
                $ts = isset($row[$dateKey]) ? $row[$dateKey] : '2022-12-10';
                $webPages[] = array($pathKey . $id, $ts, '1.0');
            }
        };

        $append($user->fetchAll(array('id', 'timestamp'), array('tour_details'), array('status' => 1)), 'tour?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('blog_details'), array('status' => 1)), 'blog?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('ad1_details'), array('status' => 1)), 'ad1?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('ad2_details'), array('status' => 1)), 'ad2?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('pdf_details'), array('status' => 1)), 'pdf?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('homework_details'), array('status' => 1)), 'homework?id=', 'timestamp');
        $append($user->fetchAll(array('id', 'timestamp'), array('books_details'), array('status' => 1)), 'books?id=', 'timestamp');

        $xmlHeader = "<?xml version='1.0' encoding='UTF-8' ?>\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" />';
        $xml = new SimpleXMLElement($xmlHeader);

        foreach ($webPages as $value) {
            $url = $xml->addChild('url');
            $url->addChild('loc', $baseDomain . '/' . $value[0]);
            $url->addChild('lastmod', date('c', strtotime((string) $value[1])));
            $url->addChild('priority', (string) $value[2]);
        }

        $out = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sitemap.xml';
        return (bool) $xml->asXML($out);
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Store a one-shot success message for the next admin page (after redirect).
 */
function edi_admin_flash_success(string $message)
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION['edi_admin_flash_success'] = $message;
    }
}
