<?php

define("APP_ROOT", __DIR__ . "/..");
define("CACHE_DIR", __DIR__ . "/../cache");
define("APP_ENV", "test");
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/BaseTestCase.php';

# Testing Db
AppModel::connect();
