<?php

# Configuration
date_default_timezone_set('UTC');
require_once 'config/config.php';
if (!defined('APP_ENV')) {
    if (file_exists(__DIR__ . '/config/config.dev.ini')) {
        define('APP_ENV', 'dev');
    } else {
        define('APP_ENV', 'prod');
    }
}
Config::_init(__DIR__ . '/config/config.'.APP_ENV.'.ini');

# Configuration - Protocol
if (APP_ENV == 'prod') define('PROTOCOL', 'https');
else define('PROTOCOL', 'http');

# Error reporting
if (Config::get('environment') != 'prod') {
    error_reporting(E_ALL);
} else {
    error_reporting(0);
}

# Timezones
require_once APP_ROOT . '/lib/TimeZone.php';
TimeZone::init();

# Composer
require_once APP_ROOT . '/vendor/autoload.php';

# Framework
require_once APP_ROOT . '/framework.php';
require_once APP_ROOT . '/view.php';

# Business
require_once APP_ROOT . '/Common/AppModel.php';
$models = array('User', 'Account', 'Role', 'Room', 'Message');
foreach ($models as $m) {
    require_once APP_ROOT . "/model/$m.php";
}

# Actions
$actions = array('Signup', 'Settings');
foreach ($actions as $a) {
    require_once APP_ROOT . "/action/".$a."Action.php";
}

# Db connection
AppModel::connect();

# Init
$app = new App();
