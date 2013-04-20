<?php


class AccountTest extends BaseTestCase
{
    private function createUserForAccount($name, $email, $account)
    {
        $u = new User;
        $u->email = $email;
        $u->name = $name;
        $u->save();
        $u->addAccount($account);
        $this->assertNotNull($u->id);

        return $u;
    }

    private function checkUserAgainstDummy(User $user, $dummies, $k)
    {
        $this->assertInstanceOf('User', $user);
        $i=0;
        foreach ($dummies as $email => $name) {
            if ($i == $k) {
                $this->assertEquals($name, $user->name);
                $this->assertEquals($email, $user->email);
                break;
            }
        }
    }


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

    public function testGetUsers()
    {
        $account = $this->createSampleAccount();
        $dummies = array(
            'dummy1@threddie.com' => 'Jim Raynor',
            'dummy2@threddie.com' => 'Sarah Kerrigan',
            'dummy3@threddie.com' => 'Zeratul',
            'dummy4@threddie.com' => 'Arcturus Mengsk',
        );
        sort($dummies);

        foreach ($dummies as $email => $name) {
            $this->createUserForAccount($name, $email, $account);
        }

        $users = $account->getUsers();
        $this->assertEquals(count($dummies), count($users));
        foreach ($users as $k=>$user) {
            $this->checkUserAgainstDummy($user, $dummies, $k);
        }
    }

    public function testAccountHasExpired()
    {
        $a = new Account;
        $this->assertNotNull($a->created);

        $now = new DateTime();
        $fifteenDaysAgo = new DateTime();
        $fifteenDaysAgo->sub(new DateInterval('P15D'));
        $this->assertTrue($now > $fifteenDaysAgo);

        $a->created = $fifteenDaysAgo->format('Y-m-d H:i:s');
        $this->assertTrue($a->trialHasEnded());
    }
}