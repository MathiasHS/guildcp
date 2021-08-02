<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

echo "<title>GuildCP &bull; UCP Characters</title>";

$user = Auth::user();

$characters = $user->getCharacters();

$box = new Box("Your characters", "", true);
$box->setClass("container mt-5 mb-5");

foreach ($characters as $character) {
    $b = new Box("<strong>{$character->getName()}</strong> ({$character->getLevel()})", $character->getRealm());
    
    $guild = htmlentities("<{$character->getGuildName()}>");
    if ($guild != null && strlen($guild)) {
        $b->append("<br><strong>{$guild}</strong>");
    }

    $b->setImage($character->getImageURL("main"), $character->getName() . "'s avatar.");
    $box->append($b->render());
}
echo $box->render();