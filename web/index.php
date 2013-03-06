<?php
# Core
session_start();
define("APP_ROOT", __DIR__ . "/..");
define("CACHE_DIR", __DIR__ . "/../cache");


#
# Cache?
#
function cachePath($slug) {
    return CACHE_DIR . '/' . md5($slug);
}
#
# XXX Leave cache off for now - it messes up display of session flash messages
#
# if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
#     $slug = $_SERVER['REQUEST_URI'];
#     $cachedHtml = @file_get_contents(cachePath($slug));
#     if ($cachedHtml) {
#         echo $cachedHtml;
#         die;
#     }
# }


#
# Bootstrap
#
require_once __DIR__ . '/../bootstrap.php';


#
# Auth
#
$app->get('/login', function() use ($view) {
    echo $view->render('auth/login.tpl.php');
});

$app->post('/login', function() use ($app, $view) {
    $email = (string) $app->request->post('email');
    $token = User::generateLoginToken($email);
    $link = 'http://'.$_SERVER['HTTP_HOST'].'/auth?t='.$token;

    $user = User::findOneBy(array('email' => $email));
    if ($user) {
        $emailBody = $view->render('auth/email.tpl.php', array('link' => $link));
        $user->loginToken = $token;
        $user->save();

        try {
            $app->mail->send($email, 'Access link', $emailBody);
            $app->session->set('wasLoginMailSent', true);
            $app->redirect('/login/sent');
        } catch (Exception $e) {
            echo $view->render('auth/email_error.tpl.php');
        }

    } else {
        $app->redirect('/login');
    }
});

$app->get('/login/sent', function() use ($app, $view) {
    if ($app->session->get('wasLoginMailSent')) {
        $app->session->remove('wasLoginMailSent');
        echo $view->render('auth/email_success.tpl.php');

    } else { $app->redirect('/'); }
});

$app->get('/auth?*', function() use ($app) {
    $user = false;
    $loginToken = (string) trim($app->request->get('t'));
    if ($loginToken) {
        $user = User::findOneBy(array('loginToken' => $loginToken));
    }
    if (!$user) { $app->redirect('/'); }

    // Burn token
    $user->loginToken = null;
    $user->save();

    // Generate Auth Cookie token
    $user->renewAuthCookie($app->cookie)->save();

    // Go to admin entry point
    $app->redirect('/admin/posts');
});

$app->get('/logout', function() use ($app) {
    session_destroy();
    $app->redirect('/');
});


#
# Public
#

// Homepage
$app->get('/', function() use ($app, $view) {
    $html = $view->render('index.tpl.php', array('session' => $app->session));
    file_put_contents(cachePath('/'), $html);
    echo $html;

    return;
});

// Signup
$app->post('/signup', function() use($app, $view) {
    $errors = validateSignup($app);
    if ($errors) {
        $app->session->set('errors', $errors);
        $app->redirect('/');
    }

    $account = new Account(array(
        'name' => $app->request->post('account_name'),
    ));
    $account->generateSlug();

    var_dump($_POST, $account);die;
});
function validateSignup($app) {
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


#
# 404
#
echo $view->render('404.tpl.php');
die;
