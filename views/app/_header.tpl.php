<!DOCTYPE html>
<html>
    <head>
        <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>

        <title>Beautiful Team Chat | Clarity</title>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" type="text/css" href="/css/main.css">
        <link rel="stylesheet" type="text/css" href="/css/app.css">

        <script src='/js/jquery-1.9.1.min.js'></script>
    </head>

    <body>
        <div class='title'>
            <div class='container'>
                <nav class='nav-main inline lead'>
                    <?= @$account ? $account->name : 'Clarity' ?>
                </nav>

                <nav class='nav-user inline'>
                    <ul class='unstyled inline'>
                        <li><a href='/dashboard'>Dashboard</a></li>
                        <li><a href='/settings'>Settings</a></li>
                        <li><a href='/logout'>Logout</a></li>
                        <li><img class='avatar' src='<?= $user->gravatar(50) ?>'></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class='container'>
