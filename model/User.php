<?php

class User extends AppModel
{
    public static $_table = 'users';
    public static $_fields = array(
        'name',
        'email',
        'loginToken',
        'authToken',
        'timezone',
        'timeOffset',
    );
    
    public $name;
    public $email;
    public $loginToken;
    public $authToken;
    public $timezone = 'UTC';
    public $timeOffset = 0;
    public $role; // In the context of an Account

    public function __construct($data = array())
    {
        parent::__construct($data);

        
    }

    public function save()
    {
        // Set tokens if this is a new user
        if (!@$this->id) {
            $this->loginToken = static::generateToken();
            $this->authToken = static::generateToken();
        }

        return parent::save();
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

    public function expireAuthCookie(Cookie $cookie)
    {
        $tokenCookie = $cookie::generate();
        $tokenCookie
            ->setName('auth_token')
            ->setValue(null)
            ->setExpire(time() - 1)
            ->setPath('/')
            ->send()
        ;
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
    public function getAccounts($showBlocked = false)
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
            if (!$showBlocked && $row->role == 'blocked') continue;
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
        $this->cacheGravatar($size);

        return '/avatar/' . md5($this->email) . '/' . $size;
    }

    private function cacheGravatar($size)
    {
        $cacheDir = static::gravatarCachePath() . '/'.md5($this->email);
        $cachePath = $cacheDir . '/' . $size;

        if (!file_exists($cachePath)) {
            @mkdir($cacheDir);
            $gravatarUrl = "https://www.gravatar.com/avatar/"
                         . md5($this->email)
                         . "?s=" . $size
            ;
            file_put_contents($cachePath, file_get_contents($gravatarUrl));
        }
    }

    public static function gravatarCachePath()
    {
        return APP_ROOT . '/cache/gravatar';
    }

    public static function getByAuthCookie(Cookie $cookie)
    {
        return static::findOneBy('authToken', $cookie->get('auth_token'));
    }

    public function shortName()
    {
        $names = explode(' ', $this->name);

        $firstName = array_shift($names);

        $callback = function($name) {
            return strtoupper($name[0]).'.';
        };

        $remainingNames = implode('', array_map($callback, $names));

        return trim(implode(' ', array_merge(array($firstName), array($remainingNames))));
    }
}