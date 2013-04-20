<?php

class Account extends AppModel
{
    private $_trial_days = 15;

    public static $_table = 'accounts';
    public static $_fields = array(
        'created',
        'name',
        'slug',
        'paid',
    );

    public $created;
    public $name;
    public $slug;
    public $paid = false;
    public $role; // Not saved in DB, populated by the User model


    public function __construct($data = array())
    {
        parent::__construct($data);
        $this->created = date('Y-m-d H:i:s');
    }

    public function generateSlug($i = 1)
    {
        $this->slug = slugify($this->name);
        if ($i > 1) $this->slug .= $i;

        if (static::findOneBy('slug', $this->slug)) {
            $i++;
            $this->generateSlug($i);
        }
    }

    public function url($absolute = false)
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

    public function getUsers()
    {
        $q = '
            SELECT u.*, r.role
            FROM '.User::$_table.' u
            JOIN '.Role::$_table.' r on r.user_id = u.id
            WHERE r.account_id = :aid
            GROUP BY u.id
            ORDER BY u.name ASC
        ';

        $users = array();
        $sth = static::$db->prepare($q);
        if ($sth->execute(array(':aid' => $this->id))) {
            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
            foreach ($rows as $row) {
                $users[] = new User($row);
            }
        } elseif (APP_ENV != 'prod') {
            print_r($sth->errorInfo());die;
        }

        return $users;
    }

    public function invite(User $user)
    {
        $role = $user->addAccount($this, 'invited');
        $role->hasJoined = false;
        $role->joinToken = User::generateToken(); // Generic token generator
        $role->save();

        return $role;
    }

    public function trialHasEnded()
    {
        $now = new DateTime;
        $start = new DateTime($this->created);
        $end = new DateTime($this->created);
        $end->add(new DateInterval('P'.$this->_trial_days.'D'));

        return $now <= $end;
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