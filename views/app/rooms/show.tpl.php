<? include __DIR__ . '/../_header.tpl.php' ?>

<div class='room-header'>
    <div class='inner-container'>
        <p class='tools-top'>
            <a href='#' class='room-edit'>Change settings</a>
        </p>

        <strong class='room-title lead'><?= e($room->title) ?></strong>
        <p class='room-description'><?= nl2br(e($room->description)) ?></p>
    </div>
</div>

<form action='/<?= $account->slug ?>/rooms/<?= $room->id ?>/edit' method='post' class='form-room form-room-edit form-block' style='display: none;'>
    <label>Room title</label>
    <input type='text' name='title' value='<?= e($room->title) ?>'>

    <label>Description (optional)</label>
    <textarea name='description'><?= e($room->description) ?></textarea>

    <div class='controls'>
        <button type='submit'>Update</button>
        or
        <a href='#' class='room-cancel'>cancel</a>
    </div>
    <? if ($account->role->role == 'admin'): ?>
    <div class='controls controls-delete'>
        <a href='/<?= $account->slug ?>/rooms/<?= $room->id ?>/delete' class='warning room-delete'>delete room</a>
    </div>
    <? endif; ?>
</form>

<table border='1' id='chat' class='chat unstyled'></table>
<center id='chatFooter'>*</center>


<form action='' method='post' id='form-message' class='form-message'>
    <table>
        <tr>
            <td class='message'>
                <textarea name='message' class='form-control inline' placeholder='type your message here'></textarea>
            </td>
            <td class='button'>
                <button class='submit form-control'>send<br><small><?= $_control_key ?>+enter</small></button>
            </td>
        </tr>
    </table>
</form>

<script>var room = <?= $room->id ?>;</script>
<script src="/js/rooms.show.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>