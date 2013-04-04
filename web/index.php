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
    $link = PROTOCOL . '://'.$_SERVER['HTTP_HOST'].'/auth?t='.$token;

    $user = User::findOneBy('email', $email);
    if ($user) {
        $emailBody = $view->render('auth/email.tpl.php', array('link' => $link));
        $user->authToken = $token;
        $user->save();

        try {
            $app->mail->send($email, 'Access link', $emailBody);
            $app->session->set('wasLoginMailSent', true);
            $app->session->set('user_email', $app->request->post('email'));
            $app->redirect('/login/sent');
        } catch (Exception $e) {
            echo $view->render('auth/email_error.tpl.php');
        }

    } else {
        $app->redirect('/login');
    }
});

$app->get('/login/sent', function() use ($app, $view) {
    echo $view->render('auth/email_sent.tpl.php', array(
        'email' => $app->session->get('user_email')
    ));
    die;
    if ($app->session->get('wasLoginMailSent')) {
        $app->session->remove('wasLoginMailSent');
        echo $view->render('auth/email_sent.tpl.php', array(
            'email' => $app->session->get('user_email')
        ));

    } else { $app->redirect('/'); }
});

$app->get('/auth', function() use ($app) {
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


#
# App
#

// Avatar
$app->get('/avatar/:hash/:size', function($hash, $size) use ($app, $view) {
    $filePath = User::gravatarCachePath() . '/' . $hash . '/' . $size;
    $info = @getimagesize($filePath);
    if (!$info) die(show404($view));

    header('Content-type: ' . $info['mime']);
    die(file_get_contents($filePath));
});


// App - Dashboard

$app->get('/dashboard', function() use ($app, $view) {
    $user = activeUser($app);

    echo $view->render('app/dashboard.tpl.php', array(
        'title' => $user->name,
        'user' => $user,
    ));
});


// App - Rooms

// Create room
$app->post('/:slug/rooms/add', function($slug) use ($app, $view) {
    $user = activeUser($app);
    $account = Account::findOneBy('slug', $slug);
    if (!$account || !$user->hasAccount($account)) die(show404($view));

    $room = new Room;
    $room->user = $user;
    $room->account = $account;
    $room->title = $app->request->post('title');
    $room->description = $app->request->post('description');
    $room->save();

    $app->redirect('/' . $account->slug . '/rooms/' . $room->id);
});

// Edit room
$app->post('/:slug/rooms/:id/edit', function($slug, $id) use ($app, $view) {
    $user = activeUser($app);
    $account = Account::findOneBy('slug', $slug);
    $room = Room::get($account, $id);

    if (!$account || !$user->hasAccount($account) || !$room) die(show404($view));

    $room->title = $app->request->post('title');
    $room->description = $app->request->post('description');
    $room->save();

    $app->redirect($room->url());
});

// Show room
$app->get('/:slug/rooms/:id', function($slug, $id) use ($app, $view) {
    $idFragments = explode('.', $id);
    if (count($idFragments) == 1) {
        $id = $idFragments[0];
    } else {
        $format = array_pop($idFragments);
        $id = implode('.', $idFragments);
    }

    $user = activeUser($app);
    $account = Account::findOneBy('slug', $slug);
    $room = Room::get($account, $id);

    if (!$account || !$user->hasAccount($account) || !$room) die(show404($view));

    // XXX: Generate a chat using lines from Apple's Think Different commercial
    $since = $app->request->get('since');
    $lines = $since ? $i = rand(0,10) > 3 ? rand(1,2) : 0 : 200;
    $response = array(
        'timestamp' => time(),
        'messages' => array(),
    );
    for ($i=0; $i<$lines; $i++) {
        $txt = "Here’s to the crazy ones. The misfits. The rebels. The troublemakers. The round pegs in the square holes. The ones who see things differently. They’re not fond of rules. And they have no respect for the status quo. You can quote them, disagree with them, glorify or vilify them. But the only thing you can’t do is ignore them. Because they change things. They push the human race forward. While some may see them as the crazy ones, we see genius. Because the people who are crazy enough to think they can change the world, are the ones who do.";
        $strings = explode('.', $txt);

        $dummies = array(
            'dummy1@threddie.com' => 'Jim Raynor',
            'dummy2@threddie.com' => 'Sarah Kerrigan',
            'dummy3@threddie.com' => 'Zeratul',
            'dummy4@threddie.com' => 'Arcturus Mengsk',
        );
        @$users[] = $user;
        foreach ($dummies as $email => $name) {
            $users[] = new User(array('email' => $email, 'name' => $name));
        }
        $m = new Message(array(
            'id' => $i+1,
            'user' => $users[rand(0, count($users)-1)],
            'room' => $room->id,
            'message' => trim($strings[rand(0, count($strings) - 1)]),
        ));
        $response['messages'][] = $view->render('app/rooms/_message.tpl.php', array(
            'my' => $user,
            'm' => $m
        ));
    }


    echo @$format == 'json' ?
        json_encode($response)
        :
        $view->render('app/rooms/show.tpl.php', array(
            'body' => 'chat-room',
            'title' => $room->title,
            'user' => $user,
            'account' => $account,
            'room' => $room,
        ))
    ;
});

// List rooms
$app->get('/:slug', function($slug) use ($app, $view) {
    $user = activeUser($app);
    $account = Account::findOneBy('slug', $slug);
    if (!$account || !$user->hasAccount($account)) die(show404($view));

    echo $view->render('app/rooms/index.tpl.php', array(
        'title' => $account->name,
        'user' => $user,
        'account' => $account,
        'rooms' => $account->getRooms(),
    ));
});


#
# DEV: Show a list of access tokens
#

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
die(show404($view));


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

function show404($view)
{
    header("HTTP/1.1 404 Not Found");
    return $view->render('404.tpl.php', array(
        'title' => 'Page Not Found',
    ));
}