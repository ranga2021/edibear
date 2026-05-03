<?php
/**
 * Legacy URL: sidebar now points to add-pdf.php. Keep a redirect for bookmarks.
 */
require_once("../classes/session_config.php");
header("Location: ./add-pdf", true, 302);
exit;
