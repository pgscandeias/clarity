<?php
class View
{
    public $tpl_dir = '';

    public function __construct($tpl_dir)
    {
        $this->tpl_dir = $tpl_dir;
    }

    public function render($template, array $data = array())
    {
        extract($data);

        ob_start();
        include $this->tpl_dir . $template;

        return ob_get_clean();
    }
}

// Escape string for html
function e($string)
{
    return htmlspecialchars($string);
}