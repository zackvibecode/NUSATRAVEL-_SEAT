<?php

/**
 * Router for php -S on Render (started with -t public).
 *
 * When this script returns false, PHP serves the file from the docroot
 * (public/), so static assets resolve correctly. Everything else goes
 * through the Laravel front controller.
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docroot = $_SERVER['DOCUMENT_ROOT'] ?? __DIR__.'/public';

// Serve existing static files (css, js, images, favicon) directly
if ($uri !== '/' && is_file($docroot.$uri)) {
    return false;
}

// Everything else goes to the Laravel front controller
require $docroot.'/index.php';
