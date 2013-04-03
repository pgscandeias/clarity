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

    public function link($absolute = false)
    {
        $link = '/' . $this->slug;
        if ($absolute) $link = PROTOCOL . '://'.$_SERVER['HTTP_HOST'] . $link;

        return $link;
    }

    public function getRooms()
    {
        $rooms = array();

        $q = '
            SELECT r.*, u.id u_id, u.name u_name, u.email u_email
            FROM '.Room::$_table.' r
            JOIN '.User::$_table.' u on r.user_id = u.id
            WHERE r.account_id = :aid
            GROUP BY r.id
            ORDER BY r.updated DESC, r.id DESC
        ';

        $sth = static::$db->prepare($q);
        if ($sth->execute(array(':aid' => $this->id))) {
            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
            foreach ($rows as $row) {
                $room = new Room($row);
                $room->account = $this;
                $room->user = new User(array(
                    'id' => $row->u_id,
                    'name' => $row->u_name,
                    'email' => $row->u_email,
                ));
                $rooms[] = $room;
            }
        } elseif (APP_ENV != 'prod') {
            print_r($sth->errorInfo());die;
        }

        return $rooms;
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