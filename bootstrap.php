<?php

# Configuration
require_once 'config.php';
Config::_init(__DIR__ . '/config.ini');

# Error reporting
if (Config::get('environment') == 'dev') {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

# Composer
require_once APP_ROOT . '/vendor/autoload.php';

# Framework
require_once APP_ROOT . '/framework.php';
require_once APP_ROOT . '/view.php';

# Business
require_once APP_ROOT . '/models.php';

# Db connection
$dsn = 'mongodb://'.Config::get('db_username').':'.Config::get('db_password').'';
$dsn.= '@'.Config::get('db_host').':'.Config::get('db_port');
$dsn.= '/'.Config::get('db_name');

try { $mongo = new MongoClient($dsn); }
catch (Exception $e) {
    echo "Database error";
    if (Config::get('environment') == 'dev') {
        echo "<br>".$dsn;
        var_dump($e->getMessage());
    }
    die;
}
Model::$db = $mongo->selectDB(Config::get('db_name'));

# Init
$app = new App();
$view = new View(APP_ROOT . '/views/');
