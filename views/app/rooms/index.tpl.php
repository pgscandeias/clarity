<? include __DIR__ . '/../_header.tpl.php' ?>

<p class='tools-top'>
    <a href='#' class='room-create'>Create new chat room</a>
    <a href='/<?= $account->slug ?>/team' class='team-manage'>Team members</a>
    <? if ($user->isAdmin()): ?>
        <a href='/<?= $account->slug ?>/team/settings'>Team settings</a>
    <? endif ?>
</p>

<form action='/<?= $account->slug ?>/rooms/add' method='post' class='form-room form-room-create form-block' style='display: none;'>
    <label>Room title</label>
    <input type='text' name='title'>

    <label>Description (optional)</label>
    <textarea name='description'></textarea>

    <div class='controls'>
        <button type='submit'>Create the room</button>
        or
        <a href='#' class='room-cancel'>cancel</a>
    </div>

    <p>
        <strong>Hint:</strong>
        You can create chat rooms for each of your team's topics of
        discussion, like
        projects, meetings, reviews, ideas, and so on.
    </p>
</form>

<? if (@$rooms): ?>
    <ul class='rooms-list unstyled'>
        <? foreach ($rooms as $r): ?>
            <li>
                <a href='/<?= $account->slug ?>/rooms/<?= $r->id ?>'>
                    <strong class='room-title lead'><?= e($r->title) ?></strong>
                    <p class='room-description'><?= nl2br(e($r->description)) ?></p>
                    <span class='room-updated'>
                        last updated
                        <strong><?= date('Y-m-d H:i', strtotime($r->getUpdated($user))) ?></strong>
                    </span>
                </a>
            </li>
        <? endforeach ?>
    </ul>

<? else: ?>
    <p class='lead'>
        You don't have any chat rooms yet :(
    </p>

    <p>
        Click on <strong>Create new chat room</strong>
        on the top right of this page to create one now.
    </p>

<? endif ?>


<script src="/js/rooms.index.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>