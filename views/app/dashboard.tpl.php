<? include '_header.tpl.php' ?>

<h3>
    <?= e($user->name) ?>
    <small>'s dashboard</small>
</h3>

<h4>Options</h4>
<ul class='unstyled'>
    <li><a href='/settings'>Settings</a></li>
    <li><a href='/logout'>Log out (end session)</a></li>
</ul>

<h4>Accounts</h4>
<ul>
    <? foreach ($user->getAccounts() as $a): ?>
    <li>
        <a href='/<?= $a->slug ?>'><?= e($a->name) ?></a>
    </li>
    <? endforeach ?>
</ul>

<? include '_footer.tpl.php' ?>