<li class='room-message'>
    <img 
        class='avatar' 
        src='<?= $m->user->gravatar(24) ?>'
        alt='<?= e($m->user->name) ?>'
        title='<?= e($m->user->name) ?>'
    >
    <span class='date'><?= date('H:i', strtotime($m->created)) ?></span>

    <span class='message'><?= nl2br(e($m->message)) ?></span>
</li>