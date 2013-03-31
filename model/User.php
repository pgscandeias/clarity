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


    public static function generateLoginToken($email)
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

    public static function getByAuthCookie(Cookie $cookie)
    {
        return static::findOneBy(array('authToken' => $cookie->get('auth_token')));
    }
}