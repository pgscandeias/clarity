<!DOCTYPE html>
<html>
    <head>
        <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>

        <title><?= @$title ? e($title) : 'Beautiful Team Chat' ?> | Clarity</title>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" type="text/css" href="/css/main.css">
        <link rel="stylesheet" type="text/css" href="/css/app.css">

        <script src='/js/jquery-1.9.1.min.js'></script>
    </head>

    <body>
        <div class='title'>
            <div class='container'>
                <nav class='nav-main inline lead'>
                    <? if (@$account): ?>
                        <a href='<?= $account->url() ?>'><?= e($account->name) ?></a>

                    <? else: ?>
                        Clarity

                    <? endif ?>
                </nav>

                <nav class='nav-user inline'>
                    <a href='/dashboard'><img class='avatar' src='<?= $user->gravatar(50) ?>'></a>
                </nav>
            </div>
        </div>

        <div class='container'>
