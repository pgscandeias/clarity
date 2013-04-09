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

    // $since is the Id of the last received message
    public function getMessages($since = 0)
    {
        $q = '
            SELECT m.*, u.id u_id, u.name u_name, u.email u_email
            FROM '.Message::$_table.' m
            JOIN '.User::$_table.' u on m.user_id = u.id
            WHERE m.room_id = :rid AND m.id > :since
            GROUP BY m.id
            ORDER BY m.id ASC
        ';

        $messages = array();
        $sth = static::$db->prepare($q);
        $params = array(
            ':rid' => $this->id, 
            ':since' => (int) $since,
        );
        if ($sth->execute($params)) {
            $rows = $sth->fetchAll(PDO::FETCH_OBJ);
            foreach ($rows as $row) {
                $msg = new Message($row);
                $msg->room = $this;
                $msg->user = new User(array(
                    'id' => $row->u_id,
                    'name' => $row->u_name,
                    'email' => $row->u_email,
                ));
                $messages[] = $msg;
            }
        } elseif (APP_ENV != 'prod') {
            print_r($sth->errorInfo());die;
        }

        return $messages;
    }

    public function url($absolute = false)
    {
        return $this->account->url($absolute) . '/rooms/' . $this->id;
    }

    public function delete()
    {
        $q = "DELETE FROM ".Message::$_table." WHERE room_id = :rid";
        $sth = static::$db->prepare($q);
        $sth->execute(array(':rid' => $this->id));

        return parent::delete();
    }

    public function getUpdated(User $user)
    {
        if (!@$user->timeOffset) return $this->updated;

        $utc = strtotime($this->updated);
        return date('Y-m-d H:i:s', $utc + $user->timeOffset);
    }
}