<!DOCTYPE html>
<html>
    <head>
        <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:200,400' rel='stylesheet' type='text/css'>

        <title><?= @$title ? e($title) : 'Beautiful Team Chat' ?> | Clarity</title>

        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />

        <link rel="stylesheet" type="text/css" href="/css/main.css">
    </head>

    <body>
        <div class='title'>
            <div class='container'>
                <ul class='public-top-nav inline unstyled'>
                    <? if ($user): ?>
                        <li><a href='/dashboard'>Dashboard</a></li>
                        <li><a href='/logout'>Logout</a></li>
                    <? else: ?>
                        <li><a href='/'>Home</a></li>
                        <li><a href='/login'>Login</a></li>
                    <? endif ?>
                </ul>

                <h1>
                    Clarity
                </h1>
                <h2>
                    beautiful team chat
                </h2>
            </div>
        </div>

        <div class='container'>
