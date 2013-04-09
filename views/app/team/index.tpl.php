<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
</p>

<table class='team-roster'>
    <tbody>

        <? foreach ($account->getUsers() as $u): ?>
        <tr <? if ($u->id == $user->id): ?>class='me'<? endif ?>>
            <td class='avatar'>
                <img src='<?= $u->gravatar(120) ?>' class='avatar'>
            </td>
            <td class='name'>
                <?= e($u->name) ?>
            </td>
            <td class='role'>
                <? if ($u->role == 'admin'): ?>
                    <span class='label label-red'><?= $u->role ?></span>
                <? else: ?>
                    &nbsp;
                <? endif ?>
            </td>
            <td class='controls'>
                <? if ($u->id == $user->id): ?>
                    It's you!
                <? elseif ($user->role->role == 'admin'): ?>
                    <a href='#'>block</a>
                <? endif ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>

<script src="/js/rooms.index.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>