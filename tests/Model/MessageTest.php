<?php


class MessageTest extends \PHPUnit_Framework_TestCase
{
    public $account;
    public $room;
    public $user;

    public function setUp()
    {
        parent::setUp();
        $this->initDb();

        $account = new Account;
        $account->name = 'Acme';
        $account->generateSlug();
        $account->save();
        $this->account = $account;

        $user = new User;
        $user->name = 'Valentine';
        $user->email = 'valentine@mail.com';
        $user->save();
        $user->addAccount($account);
        $this->user = $user;

        $room = $this->createRoom($user, $account, 'Title', 'Description');
        $this->room = $room;
    }

    private function initDb()
    {
        Room::$db->exec(file_get_contents(APP_ROOT . '/schema.sql'));
    }

    private function createRoom($user, $account, $title, $description)
    {
        $room = new Room;
        $room->account = $account;
        $room->user = $user;
        $room->title = 'Test title';
        $room->description = 'Test description';
        $room->save();
        $this->assertNotNull($room->id);

        return $room;
    }

    public function testInstantiate()
    {
        $msg = new Message;
        $this->assertInstanceOf('Message', $msg);
    }

    public function testCreate()
    {
        $user = $this->user;
        $room = $this->room;

        $msg = new Message;
        $msg->user = $user;
        $msg->room = $room;
        $msg->message = 'Howdy!';
        $msg->save();

        $this->assertNotEmpty($msg->created);
        // $this->assertEquals(date('Y-m-d H:i:s'), $msg->created); // XXX: Temporal coupling. This could fail

        $dbMsg = Message::find($msg->id);
        $this->assertInstanceOf('Message', $dbMsg);
        $this->assertEquals($user->id, $dbMsg->user_id);
        $this->assertEquals($room->id, $dbMsg->room_id);
        $this->assertEquals('Howdy!', $dbMsg->message);
    }

    public function testListRoomMessages()
    {
        $strings = array(
            'Good evening Gentlemen',
            'All your base are belong to us',
            'There is no escape, make your time'
        );
        foreach ($strings as $s) {
            $m = new Message;
            $m->user = $this->user;
            $m->room = $this->room;
            $m->message = $s;
            $m->save();
            $this->assertNotNull($m->id);
            $this->assertNotNull($m->created);
        }

        // Messages are retrieved indirectly from the Room instance
        $messages = $this->room->getMessages(0);
        $this->assertNotEmpty($messages);

        foreach ($messages as $k=>$msg) {
            $this->assertInstanceOf('Message', $msg);
            $this->assertEquals($strings[$k], $msg->message);
            $this->assertEquals($this->user->id, $msg->user->id);
            $this->assertEquals($this->user->name, $msg->user->name);
            $this->assertEquals($this->user->email, $msg->user->email);
            $this->assertNull(@$msg->user->authToken);
            $this->assertNull(@$msg->user->loginToken);
            $this->assertEquals($this->room, $msg->room);
        }
    }

    public function testCreatedMicro()
    {
        $m = new Message;
        $m->user = $this->user;
        $m->room = $this->room;
        $m->message = 'Foobar';
        $m->save();

        $messages = $this->room->getMessages($m->id);
        $this->assertEquals(0, count($messages));

        $m2 = new Message;
        $m2->user = $this->user;
        $m2->room = $this->room;
        $m2->message = 'Foobar';
        $m2->save();

        $messages = $this->room->getMessages($m->id);
        $this->assertEquals(1, count($messages));
    }
}