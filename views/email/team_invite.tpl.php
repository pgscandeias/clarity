Hi,

<?= e($user->name) ?> has invited you to join  "<?= e($account->name) ?>" 
at Clarity, a tool for online chat for teams.

To accept <?= e($user->name) ?>'s invitation, please follow the link below:
<?= e($link) ?>


Hope you enjoy Clarity Chat.

<? include __DIR__ . '/signature.tpl.php' ?>