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

        $room = new Room;
        $room->account = $account;
        $room->user = $user;
        $room->title = 'Test title';
        $room->description = 'Test description';
        $room->save();
        $this->assertNotNull($room->id);

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

        $room = new Room;
        $room->account = $account;
        $room->user = $user;
        $room->title = 'Test title';
        $room->description = 'Test description';
        $room->save();
        $this->assertNotNull($room->id);

        $dbRoom = Room::get($account, $room->id);
        $this->assertInstanceOf('Room', $dbRoom);
        $this->assertEquals($account->id, $dbRoom->account_id);
    }
}