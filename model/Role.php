<?php

class Role extends AppModel
{
    public static $_table = 'roles';
    public static $_fields = array(
        'account_id',
        'user_id',
        'role',
    );

    public $account_id;
    public $user_id;
    public $role = 'user';

    
    public static function get($accountId, $userId)
    {
        $sth = static::$db->prepare('
            SELECT *
            FROM '.static::$_table.'
            WHERE account_id = :aid AND user_id = :uid
        ');

        if (!$sth->execute(array(
            ':aid' => $accountId,
            ':uid' => $userId,
        ))) return;

        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!$row) return;

        $o = new static;
        $o->id = $row->id;
        foreach ($row as $field => $value) {
            if (in_array($field, static::$_fields)) {
                $o->{$field} = $value;
            }
        }

        return $o;
    }
}