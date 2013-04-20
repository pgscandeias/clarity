<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
    <a href='/<?= $account->slug ?>/team' class='team-manage'>Team members</a>
    <? if ($user->isAdmin()): ?>
        <a href='/<?= $account->slug ?>/team/settings'>Team settings</a>
    <? endif ?>
</p>

<h3>
    Thank you!
</h3>

<p>
    Your support goes towards making Clarity better.
</p>

<p>
    We're waiting for PayPal to confirm your subscription.
    This will happen automatically, so you're free to navigate away from this page.
</p>

<p>
    <a href='<?= $account->url() ?>'>Click here</a>
    to go back to the chat rooms index.
</p>


<? include __DIR__ . '/../_footer.tpl.php' ?>