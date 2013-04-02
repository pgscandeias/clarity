<?php

class Account extends AppModel
{
    public static $_table = 'accounts';
    public static $_fields = array(
        'name',
        'slug',
    );

    public $name;
    public $slug;
    public $role; // Not saved in DB, populated by the User model


    public function generateSlug($i = 1)
    {
        $this->slug = slugify($this->name);
        if ($i > 1) $this->slug .= $i;

        if (static::findOneBy('slug', $this->slug)) {
            $i++;
            $this->generateSlug($i);
        }
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