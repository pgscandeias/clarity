<h1>Admin / Users</h1>

<ul>
<? foreach ($users as $u): ?>
    <li>
        #<?= $u->id ?>
        <?= $u->name; ?>
        <a href='/auth?t=<?= $u->authToken ?>'><?= $u->authToken ?></a>
    </li>
<? endforeach; ?>
</ul>


<hr>
Session
<? var_dump($_SESSION) ?>
Cookies
<? var_dump($_COOKIE) ?>
Server
<? var_dump($_SERVER) ?>