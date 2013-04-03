<? include __DIR__ . '/../_header.tpl.php' ?>

<h1><?= e($room->title) ?></h1>
<p class='lead'><?= nl2br(e($room->description)) ?></p>


<script src="/js/rooms.show.js"></script>
<? include __DIR__ . '/../_footer.tpl.php' ?>