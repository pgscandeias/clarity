<?php
class Config
{
    public static $config = array();

    public static function _init($iniPath)
    {
        $ini = parse_ini_file($iniPath);
        if (!$ini) {
            throw new Exception('No valid config file found');
        }

        static::$config = $ini;
    }

    public static function get($var)
    {
        return isset(static::$config[$var]) ? static::$config[$var] : null;
    }
}