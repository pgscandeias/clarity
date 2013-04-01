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

    $user = User::findOneBy('email', $email);
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
        $user = User::findOneBy('loginToken', $loginToken);
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
    $action = new SignupAction($app);

    if ($action->errors) {
        $app->session->set('errors', $action->errors);
        $app->redirect('/');
    }

    var_dump($action->user, $action->account, $action->role);
    die("all good");
});


#
# 404
#
echo $view->render('404.tpl.php');
die;
