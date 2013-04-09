<?php
set_exception_handler('App::exception'); // bootstrap
require_once 'router.php';
require_once 'Session.php';

class App {
  protected $_server = array();

  public $request;
  public $session;
  public $cookie;
  public $mail;

  public $user;     // User instance (active user)
  public $account;  // Account instance (current account)

  public $_control_key = 'ctrl';

  public function __construct() {
    // skipped mocking here
    $this->_server = $_SERVER;
    $this->request = new Request;
    $this->session = new Session;
    $this->cookie = new Cookie;
    $this->mail = new Mail;

    $this->view = new View(APP_ROOT . '/views/');
    $this->view->assign('session', $this->session);

    if (strstr(@$this->_server['HTTP_USER_AGENT'], 'Macintosh')) {
      $this->_control_key = '⌘';
    }
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

    // Ugly ugly mess of spaghetti code :(
    public function auth($accountSlug = null, $role = null) {
        $token = $this->cookie->get('auth_token');
        $user = User::findOneBy('authToken', $token);

        if ($user) {
            $this->user = $user;
            $this->view->assign('user', $user);

            if ($accountSlug) {
                $account = Account::findOneBy('slug', $accountSlug);
                if (!$account) $this->redirect('/');

                $r = Role::get($user->id, $account->id);
                if (
                  !$r
                  || $r->role == 'blocked'
                  || ($role && $role != $r->role)
                ) {
                  $this->redirect('/login');
                }

                $user->role = $r;
                $this->account = $account;
                $this->view->assign('account', $account);
            }

            $user->renewAuthCookie($this->cookie);
            return $user;
        }

        if ($redirect) $app->redirect('/');
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


class Cookie {
  public $name = '';
  public $value = '';
  public $expire = 0;
  public $path = '/';
  public $domain = false;
  public $secure = false;   // Must be true in production (use TLS)
  public $httponly = true;  // Should never be false

  public function __construct()
  {
    $this->domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false;
    $this->secure = APP_ENV == 'prod';
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

// Check production environment
function isProduction()
{
  return APP_ENV == 'prod';
}