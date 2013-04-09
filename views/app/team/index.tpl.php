<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
</p>

<table class='team-roster'>
    <tbody>

        <? foreach ($account->getUsers() as $u): ?>
        <tr class='<?= $u->role ?> <?= $u->id == $user->id ? 'me' : '' ?>'>
            <td class='avatar'>
                <img src='<?= $u->gravatar(120) ?>' class='avatar'>
            </td>
            <td class='name'>
                <strong><?= e($u->name) ?></strong>
            </td>
            <td class='role'>
                <? if ($u->role == 'admin'): ?>
                    <span class='label label-purple'><?= $u->role ?></span>
                <? else: ?>
                    <?= $u->role ?>
                <? endif ?>
            </td>
            <td class='controls'>
                <? if ($u->id == $user->id): ?>
                    It's you!
                <? elseif ($user->role->role == 'admin'): ?>
                    <? if ($u->role == 'blocked'): ?>
                        <a href='/<?= $account->slug ?>/team/<?= $u->id ?>/unblock'>unblock</a>
                    <? else: ?>
                        <a href='/<?= $account->slug ?>/team/<?= $u->id ?>/block'>block</a>
                    <? endif ?>
                <? endif ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>

<script src="/js/rooms.index.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>