<?php


class AccountTest extends BaseTestCase
{
    public function testLoaded()
    {
        $a = new Account;
        $this->assertInstanceOf('Account', $a);
    }

    public function testGetRooms()
    {
        $u = $this->createSampleUser();
        $a = $this->createSampleAccount();
        $u->addAccount($a);

        $titles = array('Uno', 'Dos', 'Tres');
        foreach ($titles as $title) {
            $r = new Room;
            $r->user = $u;
            $r->account = $a;
            $r->title = $title;
            $r->description = "$title description";
            $r->save();
        }

        $rooms = $a->getRooms();
        $this->assertEquals(count($titles), count($rooms));
        $titles = array_reverse($titles);
        foreach ($rooms as $k=>$room) {
            $this->assertInstanceOf('Room', $room);
            $this->assertEquals($titles[$k], $room->title);
            $this->assertEquals($titles[$k].' description', $room->description);
            $this->assertEquals($u->id, $room->user->id);
            $this->assertEquals($u->name, $room->user->name);
            $this->assertEquals($u->email, $room->user->email);
            $this->assertEquals($a, $room->account);
        }
    }
}