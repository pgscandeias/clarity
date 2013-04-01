<?php


class SignupTest extends \PHPUnit_Framework_TestCase
{
    private $app;

    public function setUp()
    {
        parent::setUp();
        $this->app = new App;
    }

    private function overridePost(array $post)
    {
        foreach ($post as $k => $v) {
            $_POST[$k] = $v;
        }
    }

    public function testLoaded()
    {
        $s = new SignupAction($this->app);
        $this->assertInstanceOf('SignupAction', $s);
    }

    public function testGoodSignup()
    {
        $this->overridePost(array(
            'user_name' => 'Pedro',
            'user_email' => 'pgscandeias@gmail.com',
            'account_name' => 'ACME',
        ));

        $s = new SignupAction($this->app);

        $this->assertNotNull($s->user->id);
        $this->assertNotNull($s->account->id);
        $this->assertNotNull($s->role->id);
    }

    public function testRepeatedSignup()
    {
        $this->overridePost(array(
            'user_name' => 'Pedro',
            'user_email' => 'pgscandeias@gmail.com',
            'account_name' => 'ACME',
        ));

        $s = new SignupAction($this->app);
        $this->assertNotNull($s->user->id);
        $this->assertNotNull($s->account->id);
        $this->assertNotNull($s->role->id);

        $s2 = new SignupAction($this->app);
        $this->assertNotNull($s2->user->id);
        $this->assertNotNull($s2->account->id);
        $this->assertNotNull($s2->role->id);
        $this->assertNotEquals($s->account->slug, $s2->account->slug);
    }

    public function testBadEmails()
    {
        $this->overridePost(array(
            'user_name' => 'Pedro',
            'user_email' => '',
            'account_name' => 'ACME',
        ));

        $s = new SignupAction($this->app);
        $this->assertNotNull($s->errors['user_email']);

        $this->overridePost(array(
            'user_email' => 'i am not an email',
        ));
        $s2 = new SignupAction($this->app);
        $this->assertNotNull($s2->errors['user_email']);
    }

    public function testEmptyRequest()
    {
        $this->overridePost(array(
            'user_name' => '',
            'user_email' => '',
            'account_name' => '',
        ));

        $s = new SignupAction($this->app);
        $this->assertNotNull($s->errors['user_name']);
        $this->assertNotNull($s->errors['user_email']);
        $this->assertNotNull($s->errors['account_name']);
    }
}