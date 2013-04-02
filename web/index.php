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
    $token = User::generateToken();
    $link = 'http://'.$_SERVER['HTTP_HOST'].'/auth?t='.$token;

    $user = User::findOneBy('email', $email);
    if ($user) {
        $emailBody = $view->render('auth/email.tpl.php', array('link' => $link));
        $user->authToken = $token;
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
    $authToken = (string) trim($app->request->get('t'));
    if ($authToken) {
        $user = User::findOneBy('authToken', $authToken);
    }
    if (!$user) { $app->redirect('/'); }

    // regenerate Auth Cookie token
    $user->renewAuthCookie($app->cookie)->save();

    // Go to dashboard
    $app->redirect('/dashboard');
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


// Help

$app->get('/help/:page', function($page) use ($app, $view) {
    $tpl = file_exists(__DIR__ . '/../views/help/'.$page.'.tpl.php') ? 
           'help/'.$page.'.tpl.php' :
           '404.tpl.php'
    ;
    echo $view->render($tpl);
});


// Signup

$app->get('/signup/welcome', function() use($app, $view) {
    echo $view->render('signup/welcome.tpl.php', array(
        'email' => $app->session->get('user_email')
    ));
});

$app->post('/signup', function() use($app, $view) {
    $action = new SignupAction($app);

    if ($action->errors) {
        $app->session->set('errors', $action->errors);
        $app->redirect('/');
    }

    $link = 'http://'.$_SERVER['HTTP_HOST'].'/auth?t='.$action->user->authToken;
    $emailBody = $view->render('signup/email.tpl.php', array('link' => $link));

    try {
        $app->mail->send($action->user->email, 'Access link', $emailBody);
        $app->session->set('wasLoginMailSent', true);
        $app->session->set('user_email', $action->user->email);
        $app->redirect('/signup/welcome');
    } catch (Exception $e) {
        echo $view->render('signup/email_error.tpl.php');
    }
});


// Dashboard

$app->get('/dashboard', function() use ($app, $view) {
    $user = activeUser($app);

    echo $view->render('app/dashboard.tpl.php');
});


// DEV: Show a list of access tokens

if (APP_ENV != 'prod') {
    $app->get('/admin/users', function() use ($app, $view) {
        echo $view->render('admin/users.tpl.php', array(
            'users' => $users = User::all()
        ));
    });
}

#
# 404
#
echo $view->render('404.tpl.php');
die;


function activeUser($app) {
    $token = $app->cookie->get('auth_token');
    $user = User::findOneBy('authToken', $token);
    if (!$user) {
        $app->redirect('/');
        die;
    }

    $user->renewAuthCookie($app->cookie);

    return $user;
}