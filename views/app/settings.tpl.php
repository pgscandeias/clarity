<? include '_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/dashboard'>Dashboard</a>
    <a href='/logout'>Log out (end session)</a>
</p>

<h3>
    <?= e($user->name) ?><small>'s settings</small>
</h3>


<form action='' method='post'>
    <label>Name</label>
    <input type='text' name='name' value='<?= e($user->name) ?>'>

    <label>Email</label>
    <input type='email' name='email' value='<?= e($user->email) ?>'>

    <label>Timezone</label>
    <select name='timezone'>
        <? foreach (TimeZone::all() as $zone): ?>
        <option value='<?= $zone->name ?>' <?= $zone->name == $user->timeZone ? 'selected' : ''?>>
            <?= $zone->name ?> (<?= $zone->offsetLabel ?>)
        </option>
        <? endforeach ?>
    </select>

    <div class='controls'>
        <button class='btn btn-green'>Confirm changes</button>
    </div>
</form>

<? include '_footer.tpl.php' ?>