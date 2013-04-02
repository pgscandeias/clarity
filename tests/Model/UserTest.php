<?php


class UserTest extends \PHPUnit_Framework_TestCase
{
    private $sampleData = array(
        'name' => 'Pedro',
        'email' => 'pgscandeias@gmail.com',
        'loginToken' => 'LT',
        'authToken' => 'AT',
    );

    public function setUp()
    {
        parent::setUp();
        $this->initDb();
    }

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
        $user = User::findOneBy('email', uniqid().'@host.com');
        $this->assertNull($user);
    }

    public function testFindOneByEmail_Found()
    {
        $this->createSampleUser();

        $email = $this->sampleData['email'];
        $user = User::findOneBy('email', $email);
        $this->assertInstanceOf('User', $user);
        $this->assertNotNull($user->id);
        $this->assertEquals($email, $user->email);
    }

    public function testInsert()
    {
        $user = $this->createSampleUser();
        $this->assertNotNull($user->id);
    }

    public function testFind()
    {
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

    public function testFindAll()
    {
        $ud1 = array(
            'name' => 'Pedro',
            'email' => 'pgscandeias@gmail.com',
        );
        $ud2 = array(
            'name' => 'Petra',
            'email' => 'petracandeias@gmail.com',
        );
        $ud3 = array(
            'name' => 'Armando',
            'email' => 'armandocandeias@gmail.com',
        );

        $u1 = new User($ud1); $u1->save();
        $u2 = new User($ud2); $u2->save();
        $u3 = new User($ud3); $u3->save();

        $users = User::all();
        $this->assertEquals(3, count($users));
    }

    public function testUpdateDoesntInsertNew()
    {
        $user = $this->createSampleUser();
        $this->assertEquals(1, count(User::all()));

        $user->name = 'New Name';
        $user->save();

        $this->assertEquals(1, count(User::all()));
    }

    public function testUpdateOnlyIntendedRow()
    {
        $udata = array(
            array(
                'name' => 'Pedro',
                'email' => 'pgscandeias@gmail.com',
            ),
            array(
                'name' => 'Petra',
                'email' => 'petracandeias@gmail.com',
            ),
            array(
                'name' => 'Armando',
                'email' => 'armandocandeias@gmail.com',
            ),
        );
        foreach ($udata as $k => $ud) {
            $u = new User($ud);
            $u->save();
            $u->name = $u->name . $k;
            $u->save();
        }

        $users = User::all();
        foreach ($users as $k => $user) {
            $this->assertEquals($user->name, $udata[$k]['name'] . $k);
        }
    }
}