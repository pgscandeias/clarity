<?php


class RoomTest extends \PHPUnit_Framework_TestCase
{
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

    private function postMessage(Room $room, User $user, $msg)
    {
        $m = new Message;
        $m->room = $room;
        $m->user = $user;
        $m->message = $msg;
        $m->save();

        return $m;
    }


    public function testLoaded()
    {
        $room = new Room;
        $this->assertInstanceOf('Room', $room);
    }

    public function testInstantiate()
    {
        $room = new Room;
        $this->assertNotEmpty($room->created);
        $this->assertEquals(date('Y-m-d H:i:s'), $room->created); // XXX: Temporal coupling. This could fail
    }

    public function testCreate()
    {
        $account = $this->account;
        $user = $this->user;

        $room = $this->createRoom($user, $account, 'Test title', 'Test description');

        $dbRoom = Room::find($room->id);
        $this->assertInstanceOf('Room', $dbRoom);
        $this->assertEquals($user->id, $dbRoom->user_id);
        $this->assertEquals($account->id, $dbRoom->account_id);
        $this->assertEquals('Test title', $dbRoom->title);
        $this->assertEquals('Test description', $dbRoom->description);
    }

    public function testGet()
    {
        $account = $this->account;
        $user = $this->user;

        $room = $this->createRoom($user, $account, 'Test title', 'Test description');

        $dbRoom = Room::get($account, $room->id);
        $this->assertInstanceOf('Room', $dbRoom);
        $this->assertEquals($account, $dbRoom->account);
    }

    public function testEdit()
    {
        $account = $this->account;
        $user = $this->user;

        $room = $this->createRoom($user, $account, 'Test title', 'Test description');

        $dbRoom = Room::get($account, $room->id);
        $this->assertNotNull($dbRoom);

        $dbRoom->title = 'New title';
        $dbRoom->description = 'New description';
        $dbRoom->save();

        $updatedRoom = Room::get($account, $room->id);
        $this->assertEquals('New title', $updatedRoom->title);
        $this->assertEquals('New description', $updatedRoom->description);
    }

    public function testDelete()
    {
        $account = $this->account;
        $user = $this->user;
        $room = $this->createRoom($user, $account, 'Test title', 'Test description');

        for ($i=0; $i<3; $i++) {
            $this->postMessage($room, $user, 'Bla bla bla');
        }
        $this->assertEquals(3, count($room->getMessages()));

        $room->delete();
        $this->assertEquals(0, count($room->getMessages()));
        $this->assertEmpty(Room::find($room->id));
    }
}