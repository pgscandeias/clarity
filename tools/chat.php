<?php
/**
 * Feeds messages into a chat room
 */
$accountId = 1;  // Our main dev account


// Bootstrap
define("APP_ROOT", __DIR__ . "/..");
define("CACHE_DIR", __DIR__ . "/../cache");
define("APP_ENV", "dev");
require_once __DIR__ . '/../bootstrap.php';
AppModel::connect();


// Setup
$account = Account::find($accountId);
$users = @$account->getUsers();
$rooms = @$account->getRooms();
$room = @$rooms[0];
if (!$account || !$users || !$room) die("\nNo account, users or room\n");


// Feed
while (1) {
    $txt = "Here’s to the crazy ones. The misfits. The rebels. The troublemakers. The round pegs in the square holes. The ones who see things differently. They’re not fond of rules. And they have no respect for the status quo. You can quote them, disagree with them, glorify or vilify them. But the only thing you can’t do is ignore them. Because they change things. They push the human race forward. While some may see them as the crazy ones, we see genius. Because the people who are crazy enough to think they can change the world, are the ones who do.";
    $strings = explode('.', $txt);
    $lines = rand(0, 2);

    for ($i=0; $i<$lines; $i++) {
        $string = trim($strings[rand(0, count($strings) - 1)]);
        if (!$string) continue;
        $m = new Message(array(
            'user' => $users[rand(0, count($users)-1)],
            'room' => $room,
            'message' => $string,
        ));
        $m->save();
        echo $m->user->name.": ".$m->message."\n";
    }

    sleep(2);
}
