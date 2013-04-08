<?php

function getTimezones()
{
    $zones = array();
    $file = file(__DIR__ . '/config/timezones');

    foreach ($file as $line) {
        $parts = explode('  ', $line);

        $zone = $parts[1];
        $sign = $parts[0][0];
        $time = explode(':', ltrim($parts[0], $sign));

        $hours = $time[0];
        $minutes = $time[1];
        $seconds = (int) $hours * 3600 + (int) $minutes * 60;
        if ($sign == '-') $seconds = $seconds * -1;

        $zones[$zone] = array(
            'offsetLabel' => 'GMT'.$parts[0],
            'offsetSeconds' => $seconds,
        );
    }

    return $zones;
}
