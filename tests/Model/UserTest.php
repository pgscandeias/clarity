<?php


class UserTest extends \PHPUnit_Framework_TestCase
{
    private $sampleData = array(
        'name' => 'Pedro',
        'email' => 'pgscandeias@gmail.com',
        'loginToken' => 'LT',
        'authToken' => 'AT',
    );

    private function initDb($loadFixtures = false)
    {
        User::$db->exec(file_get_contents(APP_ROOT . '/schema.sql'));

        if ($loadFixtures) {
            User::$db->exec('
                INSERT INTO users (name, email, loginToken, authToken) 
                VALUES ("Pedro", "pgscandeias@gmail.com", "x", "x")
            ');
        }
    }

    private function createSampleUser()
    {
        $user = new User;
        foreach ($this->sampleData as $f => $v) $user->{$f} = $v;
        $user->save();

        return $user;
    }

    private function createSampleAccount()
    {
        $account = new Account;
        $account->name = 'ACME';
        $account->generateSlug();
        $account->save();

        return $account;
    }


    public function testLoaded()
    {
        $user = new User;
        $this->assertInstanceOf('User', $user);
    }

    public function testFindOneByEmail_NotFound()
    {
        $this->initDb();

        $user = User::findOneBy('email', uniqid().'@host.com');
        $this->assertNull($user);
    }

    public function testFindOneByEmail_Found()
    {
        $this->initDb(true);

        $email = 'pgscandeias@gmail.com';
        $user = User::findOneBy('email', $email);
        $this->assertInstanceOf('User', $user);
        $this->assertNotNull($user->id);
        $this->assertEquals($email, $user->email);
    }

    public function testInsert()
    {
        $this->initDb();
        $user = $this->createSampleUser();
        $this->assertNotNull($user->id);
    }

    public function testFind()
    {
        $this->initDb();
        $user = $this->createSampleUser();
        
        $id = $user->id;
        $this->assertNotNull($user->id);

        $dbUser = User::find($id);
        $this->assertInstanceOf('User', $user);
        $this->assertNotNull($user->id);
        foreach ($this->sampleData as $f => $v) {
            $this->assertEquals($v, $dbUser->{$f});
        }
    }

    public function testAddAccount()
    {
        $user = $this->createSampleUser();
        $account = $this->createSampleAccount();
        $role = $user->addAccount($account, 'admin');
        $this->assertNotNull($role->id);
        
        $dbRole = Role::get($account->id, $user->id);
        $this->assertInstanceOf('Role', $dbRole);
        $this->assertEquals('admin', $dbRole->role);
    }
}