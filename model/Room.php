<?php

class Room extends AppModel
{
    public static $_table = 'rooms';
    public static $_fields = array(
        'created',
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
        if (!$this->user instanceof User) return;
        if (!$this->account instanceof Account) return;

        $params = array(
            ':created' => $this->created,
            ':title' => $this->title,
            ':description' => $this->description,
            ':account_id' => $this->account->id,
            ':user_id' => $this->user->id,
        );
        $columns = array('created', 'title', 'description', 'account_id', 'user_id');

        if ($this->id) $this->update($params);
        else $this->insert($columns, $params);
    }

    public static function get(Account $account, $id)
    {
        $room = static::find($id);
        if ($room && $room->account_id == $account->id) return $room;
    }
}