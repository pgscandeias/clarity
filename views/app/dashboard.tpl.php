<? include '_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/settings'>Settings</a>
    <a href='/logout'>Log out (end session)</a>
</p>

<h3>
    <?= e($user->name) ?>
    <small>'s dashboard</small>
</h3>

<h4>Accounts</h4>
<ul>
    <? foreach ($user->getAccounts() as $a): ?>
    <li>
        <a href='/<?= $a->slug ?>'><?= e($a->name) ?></a>
    </li>
    <? endforeach ?>
</ul>

<? include '_footer.tpl.php' ?>