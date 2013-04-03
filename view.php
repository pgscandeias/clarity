<?php
class View
{
    public $tpl_dir = '';

    protected $vars = array();

    public function assign($var, $value)
    {
        $this->vars[$var] = $value;
        return $this;
    }

    public function __construct($tpl_dir)
    {
        $this->tpl_dir = $tpl_dir;
    }

    public function render($template, array $vars = array())
    {
        extract($vars);
        extract($this->vars);

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