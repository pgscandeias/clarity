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
    public $room;
    public $user;

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->created = date('Y-m-d H:i:s');
    }
}