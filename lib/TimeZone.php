<?php

class TimeZone
{
    public static $zones = array();

    public static function init()
    {
        static::$zones = (array) json_decode(file_get_contents(__DIR__ . '/../config/timezones.json'));
    }

    public static function all()
    {
        return static::$zones;
    }

    public static function get($zone)
    {
        return @static::$zones[$zone];
    }
}