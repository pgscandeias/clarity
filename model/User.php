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

    public function __construct(array $data = array())
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
        $this->authToken = sha1(mt_rand()); // rong, rong, rong

        $tokenCookie = $cookie::generate();
        $tokenCookie
            ->setName('auth_token')
            ->setValue($this->authToken)
            ->setExpire(time() + 3600 * 24 * 30)
            ->setPath('/')
            ->send()
        ;

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

    public static function getByAuthCookie(Cookie $cookie)
    {
        return static::findOneBy('authToken', $cookie->get('auth_token'));
    }
}