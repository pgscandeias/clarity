<tr class='room-message <?= $m->user->email == $my->email ? 'my-msg' : '' ?>'>
    <td class='name'>
        <span><?= e($m->user->shortName()) ?></span>
    </td>
    <td class='message'><?= nl2br(e($m->message)) ?></td>
    <td class='date'><?= date('H:i', strtotime($m->getCreated($my))) ?></td>
</tr>