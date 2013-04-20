<?php


class RoleTest extends BaseTestCase
{
    public function testFindRoleByArray()
    {
        $user = new User;
        $user->name = 'Cavaco';
        $user->email = 'test@threddie.com';
        $user->save();
        $this->assertNotEmpty($user->id);

        $account = new Account;
        $account->name = 'ACME';
        $account->generateSlug(); 
        $account->save();
        $this->assertNotEmpty($account->id);

        $user->addAccount($account, 'admin');

        $dbRole = Role::findOneBy(array(
            'user_id' => $user->id,
            'account_id' => $account->id,
        ));
        $this->assertNotEmpty($dbRole);
    }

    public function testInvite()
    {
        $admin = $this->createSampleUser();
        $this->assertNotEmpty($admin->id);

        $account = $this->createSampleAccount();
        $this->assertNotEmpty($account->id);

        $admin->addAccount($account, 'admin');
        $user = new User;
        $user->name = 'John User';
        $user->email = 'user@claritychat.com';
        $user->save();
        $this->assertNotEmpty($user->id);

        $role = $account->invite($user);
        $this->assertNotEmpty($role->id);
        $this->assertNotEmpty($role->joinToken);

        $dbRole = Role::findOneBy('joinToken', $role->joinToken);
        $this->assertNotEmpty($dbRole);
    }
}