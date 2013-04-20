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
$app->get('/login', function() use($app) {
    echo $app->view->render('auth/login.tpl.php');
});

$app->post('/login', function() use ($app) {
    $email = (string) $app->request->post('email');
    $token = User::generateToken();
    $link = PROTOCOL . '://'.$_SERVER['HTTP_HOST'].'/auth?t='.$token;

    $app->user = User::findOneBy('email', $email);
    if ($app->user) {
        $emailBody = $view->render('auth/email.tpl.php', array('link' => $link));
        $app->user->authToken = $token;
        $app->user->save();

        try {
            $app->mail->send($email, 'Access link', $emailBody);
            $app->session->set('wasLoginMailSent', true);
            $app->session->set('user_email', $app->request->post('email'));
            $app->redirect('/login/sent');
        } catch (Exception $e) {
            echo $app->view->render('auth/email_error.tpl.php');
        }

    } else {
        $app->redirect('/login');
    }
});

$app->get('/login/sent', function() use ($app) {
    if ($app->session->get('wasLoginMailSent')) {
        $app->session->remove('wasLoginMailSent');
        echo $app->view->render('auth/email_sent.tpl.php', array(
            'email' => $app->session->get('user_email')
        ));

    } else {
        $app->redirect('/');
    }
});

$app->get('/auth', function() use ($app) {
    $app->user = false;
    $authToken = (string) trim($app->request->get('t'));
    if ($authToken) {
        $app->user = User::findOneBy('authToken', $authToken);
    }
    if (!$app->user) { $app->redirect('/'); }

    // regenerate Auth Cookie token
    $app->user->renewAuthCookie($app->cookie)->save();

    // Go to dashboard
    $app->redirect('/dashboard');
});

$app->get('/logout', function() use ($app) {
    $app->user = activeUser($app);
    session_destroy();

    if ($app->user) {
        $app->user->expireAuthCookie($app->cookie);
    }

    $app->redirect('/');
});


#
# Public
#

// Homepage

$app->get('/', function() use ($app) {
    $app->auth();

    $html = $app->view->render('index.tpl.php');
    file_put_contents(cachePath('/'), $html);
    echo $html;

    return;
});


// Help

$app->get('/help/:page', function($page) use ($app) {
    $tpl = file_exists(__DIR__ . '/../views/help/'.$page.'.tpl.php') ? 
           'help/'.$page.'.tpl.php' :
           '404.tpl.php'
    ;
    echo $app->view->render($tpl);
});


// Signup

$app->get('/signup/welcome', function() use($app) {
    echo $app->view->render('signup/welcome.tpl.php', array(
        'email' => $app->session->get('user_email')
    ));
});

$app->post('/signup', function() use($app) {
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
        echo $app->view->render('signup/email_error.tpl.php');
    }
});


#
# App
#

// Avatar
$app->get('/avatar/:hash/:size', function($hash, $size) use ($app) {
    $filePath = User::gravatarCachePath() . '/' . $hash . '/' . $size;
    $info = @getimagesize($filePath);
    if (!$info) die(show404($app->view));

    header('Content-type: ' . $info['mime']);
    die(file_get_contents($filePath));
});


// App - Dashboard

$app->get('/dashboard', function() use ($app) {
    $app->auth();

    echo $app->view->render('app/dashboard.tpl.php', array(
        'title' => $app->user->name,
    ));
});


// App - User settings

$app->get('/settings', function() use ($app) {
    $app->auth();

    echo $app->view->render('app/settings.tpl.php', array(
        'title' => 'Settings',
    ));
});

$app->post('/settings', function() use ($app) {
    $app->auth();

    $action = new SettingsAction($app, $app->user);

    if ($action->errors) {
        $app->session->set('flash', array('error' => $action->errors));
    } else {
        $app->session->set('flash', array('success' => 'Settings changed successfully'));
    }

    $app->redirect('/settings');
});


// App - Rooms

// Create room
$app->post('/:slug/rooms/add', function($slug) use ($app) {
    $app->auth($slug);

    $room = new Room;
    $room->user = $app->user;
    $room->account = $app->account;
    $room->title = $app->request->post('title');
    $room->description = $app->request->post('description');
    $room->save();

    $app->redirect('/' . $app->account->slug . '/rooms/' . $room->id);
});

// Edit room
$app->post('/:slug/rooms/:id/edit', function($slug, $id) use ($app) {
    $app->auth($slug);
    $room = Room::get($app->account, $id);
    if (!$app->account || !$app->user->hasAccount($app->account) || !$room) 
        die(show404($app->view));

    $room->title = $app->request->post('title');
    $room->description = $app->request->post('description');
    $room->save();

    $app->redirect($room->url());
});

// Delete room
$app->get('/:slug/rooms/:id/delete', function($slug, $id) use ($app) {
    $format = Router::getFormat($id);

    $app->auth($slug);
    if ($app->account->role->role != 'admin') $app->redirect("/$slug/rooms/$id");

    $room = Room::get($app->account, $id);
    if ($room) $room->delete();

    $app->redirect("/$slug");
});

// Show room
$app->get('/:slug/rooms/:id', function($slug, $id) use ($app) {
    $format = Router::getFormat($id);

    $app->auth($slug);
    $room = Room::get($app->account, $id);

    if (!$app->account || !$app->user->hasAccount($app->account) || !$room) 
        die(show404($app->view));

    $since = $app->request->get('since') ?: 0;
    $response = array(
        'timestamp' => time(),
        'lastMessageId' => 0,
        'messages' => array(),
    );
    $messages = $room->getMessages($since);
    foreach ($messages as $m) {
        $response['messages'][] = $app->view->render('app/rooms/_message.tpl.php', array(
            'my' => $app->user,
            'm' => $m
        ));
    }
    // XXX: Clean this up
    $response['timestamp'] = @$m->created_micro ?: microtime(true) * 10000;
    $response['lastMessageId'] = @$m->id ?: $since;

    echo @$format == 'json' ?
        json_encode($response)
        :
        $app->view->render('app/rooms/show.tpl.php', array(
            'body' => 'chat-room',
            'title' => $room->title,
            'room' => $room,
            '_control_key' => $app->_control_key,
        ))
    ;
});

// Post message
$app->post('/:slug/rooms/:id', function($slug, $id) use ($app) {
    $format = Router::getFormat($id);

    $app->auth($slug);
    $room = Room::get($app->account, $id);

    if (!$app->account || !$app->user->hasAccount($app->account) || !$room) 
        die(show404($app->view));

    $m = new Message;
    $m->user = $app->user;
    $m->room = $room;
    $m->message = $app->request->post('message');
    $m->save();

    if ($format == 'json') {
        echo json_encode(array(
            'timestamp' => time(),
            'lastMessageId' => $m->id,
            'message' => $app->view->render('app/rooms/_message.tpl.php', array(
                'my' => $app->user,
                'm' => $m
            )),
        ));
    } else {
        $app->redirect("/$slug/rooms/$id");
    }
});

// List rooms
$app->get('/:slug', function($slug) use ($app) {
    $app->auth($slug);

    echo $app->view->render('app/rooms/index.tpl.php', array(
        'title' => $app->account->name,
        'rooms' => $app->account->getRooms(),
    ));
});


// App - team

// List team members
$app->get('/:slug/team', function($slug) use ($app) {
    $app->auth($slug);

    if (!$app->account || !$app->user->hasAccount($app->account)) 
        die(show404($app->view));

    echo $app->view->render('app/team/index.tpl.php', array(
        'title' => 'Team members',
    ));
});

// Block
$app->get('/:slug/team/:id/block', function($slug, $id) use ($app) {
    $app->auth($slug, 'admin');

    $role = Role::get($app->account->id, $id);
    if ($role && $app->user->id != $id) { // Prevent blocking oneself
        $role->role = 'blocked';
        $role->save();
    }

    $app->redirect("/$slug/team");

});

// Unblock
$app->get('/:slug/team/:id/unblock', function($slug, $id) use ($app) {
    $app->auth($slug, 'admin');

    $role = Role::get($app->account->id, $id);
    if ($role && $app->user->id != $id) { // Prevent blocking oneself
        $role->role = 'user';
        $role->save();
    }

    $app->redirect("/$slug/team");

});

// Invite
$app->post('/:slug/team/invite', function($slug) use ($app) {
    $app->auth($slug, 'admin');

    $email = @$_POST['email'];
    $name = @$_POST['name'];
    if (!$email || $name) $app->redirect("/$slug/team");

    // Find user, create it if none found
    $user = User::findOneBy('email', $email);
    if (!$user) {
        $user = new User;
        $user->name = $name;
        $user->email = $email;
        $user->save();
    }

    // Make sure user doesn't belong to this account yet
    $role = Role::get($app->account->id, $user->id);
    if (!$role) {
        // Create a role for this user in this account
        $role = $this->account->invite($user);

        // Send an invitation email
        $emailBody = $app->view->render('email/team_invite.tpl.php', array(
            'user' => $app->user,
            'account' => $app->account,
            'link' => $app->account->url(true) . '/join/' . $role->joinToken,
        ));

        try {
            $app->mail->send($email, 'Join '.$app->account->name, $emailBody);
            $app->redirect($app->account->url() . '/team');
        } catch (Exception $e) {
            echo $app->view->render('app/error.tpl.php');
        }
    }

    $app->redirect("/$slug/team");
});

// Join
$app->get('/:slug/join/:token', function($slug, $token) use ($app) {
    $app->getAccount($slug);
    $role = Role::findOneBy(array(
        'joinToken' => trim($token),
        'hasJoined' => false,
    ));

    if ($role && $user = User::find($role->user_id)) {
        $role->role = 'user';
        $role->hasJoined = true;
        $role->save();

        $user->renewAuthCookie($app->cookie);

        $app->redirect("/$slug/team");
    }

    die("fail");

    $app->redirect('/');
});


#
# DEV: Show a list of access tokens
#

if (APP_ENV != 'prod') {
    $app->get('/admin/users', function() use ($app) {
        $users = $app->users = User::all();
        foreach ($users as $u) {
            if (!$u->authToken) {
                $u->authToken = User::generateToken();
                $u->save();
            }
        }

        echo $app->view->render('admin/users.tpl.php', array(
            'users' => $users,
        ));
    });
}

#
# 404
#
die(show404($app->view));

function show404($view)
{
    header("HTTP/1.1 404 Not Found");
    return $view->render('404.tpl.php', array(
        'title' => 'Page Not Found',
    ));
}