<?php
class Session
{
    public $form;

    public function __construct()
    {
        $this->form = new SessionForm;
    }


    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key, $keep = true) {
        $output = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        if ($output && !$keep) {
            $this->remove($key);
        }

        return $output;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }
}

class SessionForm
{
    public function set($key, $value) {
        $_SESSION['form'][$key] = $value;
    }

    public function get($key, $keep = true) {
        $output = isset($_SESSION['form'][$key]) ? $_SESSION['form'][$key] : null;
        if ($output && !$keep) {
            $this->remove($key);
        }

        return $output;
    }

    public function remove($key)
    {
        unset($_SESSION['form'][$key]);
    }
}