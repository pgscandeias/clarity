<?php


class BaseTestCase extends \PHPUnit_Framework_TestCase
{
    private $app;

    protected $sampleUserData = array(
        'name' => 'Pedro',
        'email' => 'pgscandeias@gmail.com'
    );

    public function setUp()
    {
        parent::setUp();

        $this->app = new App;
        $this->initDb();
    }

    protected function initDb($loadFixtures = false)
    {
        $res = User::$db->exec(file_get_contents(APP_ROOT . '/schema.sql'));
    }

    protected function createSampleUser()
    {
        $user = new User;
        foreach ($this->sampleUserData as $f => $v) $user->{$f} = $v;
        $user->save();

        return $user;
    }

    protected function createSampleAccount()
    {
        $account = new Account;
        $account->name = 'ACME';
        $account->generateSlug();
        $account->save();

        return $account;
    }
}