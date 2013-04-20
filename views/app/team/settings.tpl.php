<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='/<?= $account->slug ?>'>Chat rooms</a>
    <a href='/<?= $account->slug ?>/team' class='team-manage'>Team members</a>
    <? if ($user->isAdmin()): ?>
        <a href='/<?= $account->slug ?>/team/settings'>Team settings</a>
    <? endif ?>
</p>

<h3>
    Team settings
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


<hr>
<h3>Billing</h3>

<p>
    Clarity is free for the first 15 days, then it costs only 
    <strong>9$/month</strong> per team for <strong>unlimited</strong> users.
</p>
<p>
    Your money goes towards making Clarity better.
    Thank you for your support.
</p>

<?
if (APP_ENV == 'dev') {
    $sandbox = "sandbox";
    $business = "5YYT252HWEKRA"; // use sandbox business here
} else {
    $sandbox = "";
    $business = "5YYT252HWEKRA";
}
?>
<form action="https://www<?= $sandbox ?>.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_xclick-subscriptions">
    <input type="hidden" name="business" value="<?= $business ?>">
    <input type="hidden" name="item_name" value="Clarity Chat Team Account">
    <input type="hidden" name="item_number" value="100">
    <input type="hidden" name="no_shipping" value="1">
    <input type="hidden" name="return" value="<?= $account->url(true) ?>/team/subscribed">
    <input type="hidden" name="cancel_return" value="<?= $account->url(true) ?>/team/settings">
    <? /* Free trial: 15 days */ ?>
    <input type="hidden" name="a1" value="0">
    <input type="hidden" name="p1" value="15">
    <input type="hidden" name="t1" value="D">
    <? /* Regular rate: 9$USD per month */ ?>
    <input type="hidden" name="a3" value="9.00">
    <input type="hidden" name="p3" value="1">
    <input type="hidden" name="t3" value="M">
    <? /* Is this a subscription? Yes it is. */ ?>
    <input type="hidden" name="src" value="1">
    <? /* Auto retry billing on failure? Yes. */ ?>
    <input type="hidden" name="sra" value="1">

    <input type="hidden" name="no_note" value="1">
    <? /* Account and User Ids */ ?>
    <input type="hidden" name="custom" value="a<?= $account->id ?>|u<?= $user->id ?>">
    <? /* Invoice number 
    <input type="hidden" name="invoice" value="invoicenumber">
    */ ?>
    <input type="hidden" name="usr_manage" value="1">
    <input type="hidden" name="page_style" value="clarity1">
    <input type="image"
        src="https://www.paypalobjects.com/en_US/i/btn/btn_subscribeCC_LG.gif"
        border="0" name="submit" 
        alt="Subscribe to Clarity Chat"
    >
</form>



<? include __DIR__ . '/../_footer.tpl.php' ?>