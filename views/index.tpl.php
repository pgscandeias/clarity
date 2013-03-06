<? include '_header.tpl.php' ?>

<form action='/signup' method='post' class='signup'>
    <? if ($session->get('errors')): ?>
        <ul class='flash warning'>
            <? foreach ($session->get('errors', false) as $error): ?>
            <li><?= $error ?></li>
            <? endforeach ?>
        </ul>
    <? endif; ?>
    <fieldset>
        <label>Your name</label>
        <input type='text' name='user_name' value='<?= $session->form->get('user_name', false) ?>' required>

        <label>Your email address</label>
        <input type='email' name='user_email' value='<?= $session->form->get('user_email', false) ?>' required>

        <label>Team or Project name</label>
        <input type='text' name='account_name' value='<?= $session->form->get('account_name', false) ?>' required>
    </fieldset>

    <button type='submit'>Start 15-day free trial</button>
    <p>
        Just <strong>$9/month</strong> after that.
        <br>
        No commitment, cancel anytime.
    </p>
</form>


<? include '_footer.tpl.php' ?>