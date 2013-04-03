<?php

class Room extends AppModel
{
    public static $_table = 'rooms';
    public static $_fields = array(
        'created',
        'updated',
        'title',
        'description',
    );

    public $created;
    public $updated;
    public $title;
    public $description;
    public $account;
    public $user;

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->created = date('Y-m-d H:i:s');
    }

    public function save()
    {
        if (!$this->user instanceof User) $error = 'no user set';
        if (!$this->account instanceof Account) $error = 'no account set';
        if (@$error) {
            if (!isProduction()) throw new Exception($error);
            return;
        }

        $params = array(
            ':created' => $this->created,
            ':updated' => date('Y-m-d H:i:s'),
            ':title' => $this->title,
            ':description' => $this->description,
            ':account_id' => $this->account->id,
            ':user_id' => $this->user->id,
        );
        $columns = array('created', 'updated', 'title', 'description', 'account_id', 'user_id');

        if ($this->id) $this->update($params);
        else $this->insert($columns, $params);
    }

    public static function get(Account $account, $id)
    {
        $q = '
            SELECT r.*, u.id u_id, u.name u_name, u.email u_email
            FROM '.static::$_table.' r
            JOIN '.User::$_table.' u on r.user_id = u.id
            WHERE r.id = :id AND r.account_id = :aid
        ';
        $params = array(
            ':id' => $id,
            ':aid' => $account->id,
        );
        $sth = static::$db->prepare($q);
        if (!$sth->execute($params)) return;

        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!$row) return;

        $room = new static($row);
        $room->id = $row->id;
        $room->account = $account;
        $room->user = new User(array(
            'id' => $row->u_id,
            'name' => $row->u_name,
            'email' => $row->u_email,
        ));

        return $room;
    }

    public function url($absolute = false)
    {
        return $this->account->url($absolute) . '/rooms/' . $this->id;
    }
}