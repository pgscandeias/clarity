<? include '_header.tpl.php' ?>

<form action='/signup' method='post' class='signup'>
    <fieldset>
        <label>Your name</label>
        <input type='text' name='user[name]' required>

        <label>Your email address:</label>
        <input type='email' name='user[email]' required>

        <label>Project name</label>
        <input type='text' name='account[name]' required>
    </fieldset>

    <button type='submit'>Start 15-day free trial</button>
    <p class='help'>
        just <strong>$9/month</strong> after that
    </p>
</form>


<? include '_footer.tpl.php' ?>