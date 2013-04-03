<?php

# Configuration
date_default_timezone_set('UTC');
require_once 'config.php';
if (!defined('APP_ENV')) {
    if (file_exists(__DIR__ . '/config.dev.ini')) {
        define('APP_ENV', 'dev');
    } else {
        define('APP_ENV', 'prod');
    }
}
Config::_init(__DIR__ . '/config.'.APP_ENV.'.ini');

# Configuration - Protocol
if (APP_ENV == 'prod') define('PROTOCOL', 'https');
else define('PROTOCOL', 'http');

# Error reporting
if (Config::get('environment') != 'prod') {
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
require_once APP_ROOT . '/Common/AppModel.php';
$models = array('User', 'Account', 'Role', 'Room');
foreach ($models as $m) {
    require_once APP_ROOT . "/model/$m.php";
}

# Actions
$actions = array('Signup');
foreach ($actions as $a) {
    require_once APP_ROOT . "/action/".$a."Action.php";
}

# Db connection
AppModel::connect();

# Init
$app = new App();
$view = new View(APP_ROOT . '/views/');
