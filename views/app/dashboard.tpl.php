<? include '_header.tpl.php' ?>

<h3><?= $user->name ?>'s dashboard</h3>

<ul>
    <? foreach ($user->getAccounts() as $a): ?>
    <li>
        <a href='/<?= $a->slug ?>'><?= e($a->name) ?></a>
    </li>
    <? endforeach ?>
</ul>


<? include '_footer.tpl.php' ?>