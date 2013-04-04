<li class='room-message'>
    <span class='user'><?= e($m->user->name) ?></span>
    <span class='date'><?= $m->created ?></span>
    :
    <?= nl2br(e($m->message)) ?>
</li>