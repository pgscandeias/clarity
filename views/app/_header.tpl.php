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

    <body class='<?= @$body ?>'>
        <div class='title'>
            <div class='inner-container'>
                <nav class='nav-user inline'>
                    <a href='/dashboard'><img class='avatar' src='<?= $user->gravatar(120) ?>'></a>
                </nav>
                
                <nav class='nav-main inline lead'>
                    <? if (@$account): ?>
                        <a href='<?= $account->url() ?>'><?= e($account->name) ?></a>

                    <? else: ?>
                        Clarity

                    <? endif ?>

                    <?= @$title && $title != @$account->name ? ' / ' . $title : '' ?>
                </nav>

                
            </div>
        </div>

        <div class='container'>
            <? if ($session->get('flash')): ?>
                <ul class='unstyled flash-container'>
                    <? foreach ($session->get('flash') as $type => $flash): ?>
                        <? if (is_array($flash)): ?>
                            <? foreach ($flash as $message): ?>
                                <li class='flash flash-<?= $type ?>'><?= e($message) ?></li>
                            <? endforeach ?>
                        <? else: ?>
                            <li class='flash flash-<?= $type ?>'><?= e($flash) ?></li>
                        <? endif ?>
                    <? endforeach ?>
                </ul>
                <? $session->remove('flash') ?>
            <? endif ?>
