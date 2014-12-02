<?php

$composer_loader = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composer_loader)) {
    $loader = require($composer_loader);
    $loader->add('GeoPattern\\', __DIR__);
} else {
    echo "Woah hold on. You seem to have not run `composer install` on before running the test.\n";
}
