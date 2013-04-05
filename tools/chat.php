<?php

define("APP_ROOT", __DIR__ . "/..");
define("CACHE_DIR", __DIR__ . "/../cache");
define("APP_ENV", "dev");
require_once __DIR__ . '/../bootstrap.php';

AppModel::connect();


$i = 0;
while (1) {
    $i++;

    echo "Iteration $i\n";

    sleep(1);
}