<?php
set_exception_handler('App::exception'); // bootstrap
require_once 'router.php';

class App {
  protected $_server = array();

  public $request;
  public $session;
  public $cookie;
  public $mail;

  public function __construct() {
    // skipped mocking here
    $this->_server = $_SERVER;
    $this->request = new Request;
    $this->session = new Session;
    $this->cookie = new Cookie;
    $this->mail = new Mail();
  }

  public function redirect($url, $status = 200)
  {
    header('Status: '.$status);
    header('Location: '.$url);
  }

  public function ifEmpty404($var, $view) {
    if (empty($var)) {
      echo $view->render('404.tpl.php');
      die();
    }
  }

  public function firewall()
  {
    $user = User::getByAuthCookie($this->cookie);
    if ($user) {
      $user->renewAuthCookie($this->cookie)->save();
      return true;
    }
    
    $this->redirect('/');
  }

  public function get($pattern, $callback) {
    $this->_route('GET', $pattern, $callback);
  }

  public function delete($pattern, $callback) {
    $this->_route('DELETE', $pattern, $callback);
  }

  public function post($pattern, $callback) {
    $this->_route('POST', $pattern, $callback);
  }


  protected function _route($method, $pattern, $callback) {
    if ($this->_server['REQUEST_METHOD']!=$method) return;

    $r = new Router;

    $match = $r->matchPattern($pattern, $_SERVER['REQUEST_URI']);
    if (!$match) return;
    
    $this->_exec($callback, $match['arguments']);
  }

  protected function _exec(&$callback, &$args) {
    foreach ((array)$callback as $cb) call_user_func_array($cb, $args);
    throw new Halt(); // Exception instead of exit;
  }

  // Stop execution on exception and log as E_USER_WARNING
  public static function exception($e) {
    if ($e instanceof Halt) return;
    trigger_error($e->getMessage()."\n".$e->getTraceAsString(), E_USER_WARNING);
    $app = new App();
    $app->display('exception.php', 500);
  }

  public function quote($str) {
    return htmlspecialchars($str, ENT_QUOTES);
  }
}

class AppJson extends App {
  protected function _exec(&$callback, &$args) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(call_user_func_array($callback, $args));
    throw new Halt(); // Exception instead of exit;
  }
}

class Request {
  public function post($var)
  {
    return isset($_POST[$var]) ? $_POST[$var] : null;
  }

  public function get($var)
  {
    return isset($_GET[$var]) ? $_GET[$var] : null;
  }
}

class Session {
  public function get($var)
  {
    return isset($_SESSION[$var]) ? $_SESSION[$var] : null;
  }

  public function set($var, $value)
  {
    $_SESSION[$var] = $value;
  }

  public function remove($var)
  {
    unset($_SESSION[$var]);
  }
}

class Cookie {
  public $name = '';
  public $value = '';
  public $expire = 0;
  public $path = '/';
  public $domain = false;
  public $secure = false; // XXX This needs SSL/TLS. Get it.
  public $httponly = true;

  public function __construct()
  {
    $this->domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false;
  }

  public static function generate()
  {
    return new static;
  }

  public function setName($value) { $this->name = $value; return $this; }
  public function setValue($value) { $this->value = $value; return $this; }
  public function setExpire($value) { $this->expire = $value; return $this; }
  public function setPath($value) { $this->path = $value; return $this; }
  public function setDomain($value) { $this->domain = $value; return $this; }
  public function setSecure($value) { $this->secure = $value; return $this; }
  public function setHttponly($value) { $this->httponly = $value; return $this; }

  public function send()
  {
    setcookie(
      $this->name,
      $this->value,
      $this->expire,
      $this->path,
      $this->domain,
      $this->secure,
      $this->httponly
    );
  }

  public function get($value)
  {
    return isset($_COOKIE[$value]) ? $_COOKIE[$value] : null;
  }
}

class Mail {
  public $config;

  public function send($to, $subject, $body)
  {
    $transport = Swift_SmtpTransport::newInstance()
                ->setHost(Config::get('smtp_host'))
                ->setPort(Config::get('smtp_port'))
                ->setEncryption(Config::get('smtp_encryption'))
                ->setUsername(Config::get('smtp_username'))
                ->setPassword(Config::get('smtp_password'))
    ;
    $mailer = Swift_Mailer::newInstance($transport);
    $message = Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array(Config::get('address') => Config::get('name')))
                ->setTo(array($to))
                ->setBody($body)
    ;

    return $mailer->send($message);
  }
}

// use Halt-Exception instead of exit;
class Halt extends Exception {}

