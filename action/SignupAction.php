<?php

class SignupAction
{
    public $app;
    public $user;
    public $account;
    public $role;
    public $errors;

    public function __construct($app)
    {
        $this->app = $app;
        $this->run();
    }

    private function run()
    {
        $app = $this->app;
        
        $this->errors = $this->validateSignup();
        if ($this->errors) return;

        $account = new Account(array(
            'name' => $app->request->post('account_name'),
        ));
        $account->generateSlug();

        $user = User::findOneBy('email', $app->request->post('user_email'));
        if (!$user) {
            $user = new User(array(
                'name' => $app->request->post('user_name'),
                'email' => $app->request->post('user_email'),
            ));
            $user->save();
        }
        
        $account->save();
        $role = $user->addAccount($account, 'admin');

        $this->user = $user;
        $this->account = $account;
        $this->role = $role;
    }

    public function validateSignup() {
        $app = $this->app;

        $rules = array(
            'user_name' => array('name' => 'Your name', 'required' => true),
            'user_email' => array('name' => 'Your email', 'required' => true, 'email' => true),
            'account_name' => array('name' => 'Project name', 'required' => true)
        );

        $errors = array();
        foreach ($rules as $field => $rule) {
            $value = $app->request->post($field);
            $app->session->form->set($field, $value);

            if (isset($rule['required']) && !$value) {
                $errors[$field] = $rule['name'] . ' is required';
            }
            elseif (isset($rule['email']) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = $rule['name'] . ' must be a valid email address';
            }
        }

        return $errors;
    }
}