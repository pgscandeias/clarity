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
        $this->assertNotEmpty($msg->created);
        $this->assertEquals(date('Y-m-d H:i:s'), $msg->created); // XXX: Temporal coupling. This could fail
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

        $dbMsg = Message::find($msg->id);
        $this->assertInstanceOf('Message', $dbMsg);
        $this->assertEquals($user->id, $dbMsg->user_id);
        $this->assertEquals($room->id, $dbMsg->room_id);
        $this->assertEquals('Howdy!', $dbMsg->message);
    }
}