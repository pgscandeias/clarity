<? include __DIR__ . '/../_header.tpl.php' ?>

<div class='room-header'>
    <p class='tools-top'>
        <a href='#' class='room-edit'>Change settings</a>
    </p>

    <strong class='room-title lead'><?= e($room->title) ?></strong>
    <p class='room-description'><?= nl2br(e($room->description)) ?></p>
</div>

<form action='/<?= $account->slug ?>/rooms/<?= $room->id ?>/edit' method='post' class='form-room-edit form-block' style='display: none;'>
    <label>Room title</label>
    <input type='text' name='title' value='<?= e($room->title) ?>'>

    <label>Description (optional)</label>
    <textarea name='description'><?= e($room->description) ?></textarea>

    <div class='controls'>
        <button type='submit'>Update</button>
        or
        <a href='#' class='room-cancel'>cancel</a>
    </div>
</form>

<ul id='chat' class='chat'></ul>


<script>var room = <?= $room->id ?>;</script>
<script src="/js/rooms.show.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>