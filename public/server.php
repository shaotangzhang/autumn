<?php

chdir(__DIR__);

$filePath = ltrim($_SERVER["REQUEST_URI"], '/');
if (($pos = strpos($filePath, '?')) !== false) {
    $filePath = substr($filePath, 0, $pos);
}
$filePath = realpath($filePath);

if ($filePath && is_dir($filePath)) {
    // attempt to find an index file
    foreach (['index.php', 'index.html'] as $indexFile) {
        if ($filePath = file_exists($filePath . DIRECTORY_SEPARATOR . $indexFile)) {
            break;
        }
    }
}

if ($filePath && is_file($filePath)) {
    // 1. check that file is not outside this directory for security
    // 2. check for circular reference to server.php
    // 3. don't serve dotfiles

    if (str_starts_with($filePath, __DIR__ . DIRECTORY_SEPARATOR) &&
        ($filePath !== __FILE__) &&
        !str_starts_with(basename($filePath), '.')
    ) {

        if (strtolower(substr($filePath, -4)) == '.php') {
            // php file; serve through interpreter
            include $filePath;
        } else {
            // asset file; serve from filesystem
            return false;
        }
    } else {
        // disallowed file
        header("HTTP/1.1 404 Not Found");
        echo "404 Not Found";
    }
} else {
    // rewrite to our index file
    include __DIR__ . DIRECTORY_SEPARATOR . 'index.php';
}