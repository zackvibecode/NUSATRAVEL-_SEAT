<?php

/**
 * Router for php -S on Render. Serves static assets directly and
 * everything else through the Laravel front controller.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve existing static files (css, js, images, favicon) directly
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri) && ! is_dir(__DIR__.'/public'.$uri)) {
    return false;
}

// Everything else goes to the Laravel front controller
require __DIR__.'/public/index.php';
