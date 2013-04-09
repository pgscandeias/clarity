<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
</p>

<ul class='unstyled team-roster'>
    <? foreach ($account->getUsers() as $u): ?>
    <li>
        <img src='<?= $u->gravatar(120) ?>' class='avatar'>
        <?= e($u->name) ?>
        <span class='label <?= $u->role == 'admin' ? 'label-red' : '' ?>'><?= $u->role ?></span>
    </li>
    <? endforeach ?>
</ul>

<script src="/js/rooms.index.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>