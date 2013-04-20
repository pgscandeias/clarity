<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
    <a href='/<?= $account->slug ?>/team' class='team-manage'>Team members</a>
    <? if ($user->isAdmin()): ?>
        <a href='/<?= $account->slug ?>/team/settings'>Team settings</a>
    <? endif ?>
</p>

<h3>
    <?= e($account->name) ?><small>: settings</small>
</h3>


<form action='' method='post'>
    <label>Team or project name</label>
    <input type='text' name='name' value='<?= e($account->name) ?>'>

    <?
    /* Allow paid teams to change their url slug 

    // ...

    */ ?>

    <div class='controls'>
        <button class='btn btn-green'>Confirm changes</button>
    </div>
</form>

<? include __DIR__ . '/../_footer.tpl.php' ?>