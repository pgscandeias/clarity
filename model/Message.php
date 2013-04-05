<?php

class Message extends AppModel
{
    public static $_table = 'messages';
    public static $_fields = array(
        'created',
        'message',
    );

    public $created;
    public $message;
    public $user;
    public $room;

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->created = date('Y-m-d H:i:s');
    }

    public function save()
    {
        if (!$this->user instanceof User) $error = 'no user set';
        if (!$this->room instanceof Room) $error = 'no room set';
        if (@$error) {
            if (!isProduction()) throw new Exception($error);
            return;
        }

        $params = array(
            ':created' => $this->created,
            ':message' => $this->message,
            ':room_id' => $this->room->id,
            ':user_id' => $this->user->id,
        );
        $columns = array('created', 'message', 'room_id', 'user_id');

        if ($this->id) $this->update($params);
        else $this->insert($columns, $params);
    }
}