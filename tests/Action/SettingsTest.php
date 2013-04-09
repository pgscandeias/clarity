<?php


class SettingsTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $user;

    public function setUp()
    {
        parent::setUp();
        $this->app = new App;
        $this->user = new User(array(
            'name' => 'Pedro',
            'email' => 'pgscandeias@gmail.com',
            'timezone' => 'UTC',
        ));
        $this->user->save();
        $this->assertNotNull($this->user->id);
    }

    private function overridePost(array $post)
    {
        foreach ($post as $k => $v) {
            $_POST[$k] = $v;
        }
    }

    public function testLoaded()
    {
        $s = new SettingsAction($this->app, $this->user);
        $this->assertInstanceOf('SettingsAction', $s);
    }

    public function testGoodSettings()
    {
        $newData = array(
            'name' => 'Pedro Gil Candeias',
            'email' => 'mail@threddie.com',
            'timezone' => 'Lisbon',
        );
        $this->overridePost($newData);

        $s = new SettingsAction($this->app, $this->user);

        $dbUser = User::find($this->user->id);
        foreach ($newData as $field => $value) {
            $this->assertEquals($value, $this->user->{$field});
        }
    }

    public function testBadEmails()
    {
        $this->overridePost(array(
            'name' => 'Pedro',
            'email' => '',
            'timezone' => 'UTC',
        ));

        $s = new SettingsAction($this->app, $this->user);
        $this->assertNotNull($s->errors['email']);

        $this->overridePost(array(
            'email' => 'i am not an email',
        ));
        $s2 = new SettingsAction($this->app, $this->user);
        $this->assertNotNull($s2->errors['email']);

        $dupeMail = 'newmail@threddie.com';
        $newUser = new User(array('name' => 'John', 'email' => $dupeMail));
        $newUser->save();
        $this->overridePost(array(
            'email' => $dupeMail,
        ));
        $s3 = new SettingsAction($this->app, $this->user);
        $this->assertNotNull($s3->errors['email']);
    }

    public function testBadTimezone()
    {
        $this->overridePost(array(
            'name' => 'Pedro',
            'email' => 'pgscandeias@gmail.com',
            'timezone' => 'LeMons',
        ));

        $s = new SettingsAction($this->app, $this->user);
        $this->assertNotNull($s->errors['timezone']);
    }
    
    // public function testEmptyRequest()
    // {
    //     $this->overridePost(array(
    //         'user_name' => '',
    //         'user_email' => '',
    //         'account_name' => '',
    //     ));

    //     $s = new SettingsAction($this->app);
    //     $this->assertNotNull($s->errors['user_name']);
    //     $this->assertNotNull($s->errors['user_email']);
    //     $this->assertNotNull($s->errors['account_name']);
    // }
}