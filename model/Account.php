<?php

class Account extends AppModel
{
    public static $_table = 'accounts';
    public static $_fields = array(
        'name' => 'string',
        'slug' => 'string',
        'created' => 'datetime',
        'updated' => 'datetime', 
    );

    public function generateSlug()
    {
        $this->slug = slugify($this->name);
    }
}

function slugify($string, $space = "-") {
    if (function_exists('iconv')) {
        $string = @iconv('UTF-8', 'ASCII//TRANSLIT', $string);
    }
    $string = preg_replace("/[^a-zA-Z0-9 -]/", "", $string);
    $string = strtolower($string);
    $string = str_replace(" ", $space, $string);

    return $string;
}