<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
    <a href='/<?= $account->slug ?>/team' class='team-manage'>Team members</a>
    <? if ($user->isAdmin()): ?>
        <a href='/<?= $account->slug ?>/team/settings'>Team settings</a>
    <? endif ?>
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
                        <a href='<?= $account->url() ?>/team/<?= $u->id ?>/unblock'>unblock</a>
                    <? else: ?>
                        <a href='<?= $account->url() ?>/team/<?= $u->id ?>/block'>block</a>
                    <? endif ?>
                <? endif ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>


<? if ($user->role->role == 'admin'): ?>
    <a href='#' class='show-invite'>+ Invite a new team member</a>
    <form action='<?= $account->url() ?>/team/invite' method='post' class='form-team-add' style='display: none'>

        <label>Name:</label>
        <input type='text' name='name' required>

        <label>Email address:</label>
        <input type='email' name='email' required>

        <div class='control'>
            <button>Send invitation</button>
            or
            <a href='#' class='cancel-invite'>cancel</a>
        </div>
    </form>
<? endif ?>

<script src="/js/team.index.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>