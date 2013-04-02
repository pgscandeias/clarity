<?php

class User extends AppModel
{
    public static $_table = 'users';
    public static $_fields = array(
        'name',
        'email',
        'loginToken',
        'authToken',
    );
    
    public $name;
    public $email;
    public $loginToken;
    public $authToken;

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->loginToken = static::generateToken();
        $this->authToken = static::generateToken();
    }

    public static function generateToken()
    {
        // Yeah this is wrong. Just experimenting.
        return sha1(mt_rand());
    }

    public function renewAuthCookie(Cookie $cookie)
    {
        $this->authToken = static::generateToken();

        $tokenCookie = $cookie::generate();
        $tokenCookie
            ->setName('auth_token')
            ->setValue($this->authToken)
            ->setExpire(time() + 3600 * 24 * 30)
            ->setPath('/')
            ->send()
        ;

        $this->save();
        return $this;
    }

    public function addAccount(Account $account, $role = 'user')
    {
        $r = new Role;
        $r->account_id = $account->id;
        $r->user_id = $this->id;
        $r->role = $role;
        $r->save();

        return $r;
    }

    // Return a list of Accounts with the $role property populated for this user
    public function getAccounts()
    {
        // XXX: Hardcoding the ORDER clause for now
        $sth = static::$db->prepare('
            SELECT 
                a.*,
                r.role
            FROM '.Account::$_table.' a
            JOIN '.Role::$_table.' r on r.account_id = a.id
            WHERE r.user_id = :uid
            GROUP BY a.id
            ORDER BY a.name ASC
        ');

        if (!$sth->execute(array(':uid' => $this->id))) return;

        $results = array();
        $rows = $sth->fetchAll(PDO::FETCH_OBJ);
        foreach ($rows as $row) {
            $results[] = new Account($row);
        }

        return $results;
    }

    public function hasAccount(Account $account)
    {
        $accounts = $this->getAccounts();
        foreach ($accounts as $a) {
            if ($a->id == $account->id) return true;
        }

        return false;
    }

    public function gravatar($size = 50)
    {
        return "https://www.gravatar.com/avatar/"
               . md5($this->email)
               . "?s=" . $size
        ;
    }

    public static function getByAuthCookie(Cookie $cookie)
    {
        return static::findOneBy('authToken', $cookie->get('auth_token'));
    }
}