<?php

class SettingsAction
{
    public $app;
    public $user;
    public $errors;

    public function __construct($app, $user)
    {
        $this->app = $app;
        $this->user = $user;
        $this->run();
    }

    private function run()
    {
        $app = $this->app;
        $user = $this->user;

        $this->errors = $this->validate();
        if ($this->errors) return;

        $user->name = $this->app->request->post('name');
        $user->email = $this->app->request->post('email');
        $user->timezone = $this->app->request->post('timezone');
        $user->save();
    }

    public function validate() {
        $app = $this->app;

        $request = $app->request;
        $errors = array();
        if (!@$request->post('name'))
            $errors['name'] = 'Name is required';
        if (!@$request->post('email'))
            $errors['email'] = 'Email address is required';
        if (!filter_var($request->post('email'), FILTER_VALIDATE_EMAIL)) 
            $errors['email'] = 'Email must be a valid email address';
        if (!$this->emailIsUnique($request->post('email')))
            $errors['email'] = 'That email address is already in use by someone else';
        if (!TimeZone::get($request->post('timezone')))
            $errors['timezone'] = 'Invalid timezone';

        return $errors;
    }

    private function emailIsUnique($email)
    {
        $q = 'SELECT id FROM '.User::$_table.' WHERE id != :uid AND email = :email';
        $sth = User::$db->prepare($q);
        $sth->execute(array(
            ':uid' => $this->user->id,
            ':email' => $email,
        ));
        $row = $sth->fetch();

        return !$row;
    }
}