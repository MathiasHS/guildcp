<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

echo "<title>GuildCP &bull; UCP Guilds</title>";
echo "<div class='content'>";
use GuildCP\Blizzard\Guild;

$blizzardClient->setRegion(Auth::user()->getRegion());

$registeredGuilds = array();
$registerableGuilds = array();
$checkedGuilds = array();

foreach (Auth::user()->getCharacters() as $character) {
    if ($character->hasGuild()) {
        // Checking a guild and finding the GM can cost time depending on its size, only check a guild once in case of several characters in it
        if (!in_array($character->getGuildName() . ";" . $character->getGuildRealm(), $checkedGuilds)) {
            $playerGuild = new Guild($blizzardClient, $character->getGuildRealm(), $character->getGuildName());
            $checkedGuilds[] = $playerGuild->__toString();

            if ($playerGuild->isRegistered()) {
                $registeredGuilds[] = $playerGuild;
            } else {
                if ($playerGuild->hasGuildMasterCharacter(Auth::user())) {
                    $registerableGuilds[] = $playerGuild;
                }
            }
        }
    }
}

$box = new Box();
$box->setClass("container mt-5 mb-5");

$imgAlly = "<img src='https://images-na.ssl-images-amazon.com/images/I/41Vi4vmvrBL.jpg' class='rounded-circle' alt='Alliance' width='75px' height='70px'>";
$imgHorde = "<img src='https://images-na.ssl-images-amazon.com/images/I/31TI9yXs4jL.jpg' class='rounded-circle' alt='Horde' width='75px' height='70px'>";

if (count($registerableGuilds)) {
    $box->append(
        "<div class='hero-image2 mt-5 mb-5'>
            <div class='hero-text'>
                <h1>Registerable guilds</h1>
            </div>
        </div>"
    );


    foreach ($registerableGuilds as $guild) {
        $img = ($guild->getFaction()) ? ($imgHorde) : ($imgAlly);
        $box->append(
            "<div class='card pl-4 mb-4 mt-4' style='width: 70%; margin-left: 15%;'>
            <div class='card-body'>
                <div class='box'>
                    <div>{$img}</div>
                    <div style='padding-left: 10px'>
                        <h5 class='card-title text-dark font-weight-bold'>{$guild->getName()}</h5>
                        <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                    </div>
                    <div class='push mobile-button'><a role='button' href='../register-guild/{$guild}' class='btn btn-primary'>Register</a></div>
                </div>
            </div>
        </div>"
        );
    }
} else {
    $box->append(
        Alert::danger(
            "No registerable guilds found",
            "You need to be a Guild Master in order to register a guild!<br>
            You can contact your Guild Master if you wish for your guild to be registered.",
            "mt-5 mb-5"
        )
    );
}

if (count($registeredGuilds)) {
    $box->append(
        "<div class='hero-image2 mt-5 mb-5'>
            <div class='hero-text'>
                <h1>Registered guilds</h1>
            </div>
        </div>"
    );

    foreach ($registeredGuilds as $guild) {
        $img = ($guild->getFaction()) ? ($imgHorde) : ($imgAlly);
        $regGuildLink = (Auth::user()->getGuildPermissions($guild) === null) ? ("../register-for-guild/{$guild}") : ("../guild/{$guild->getRegion()};{$guild->getRealm()};{$guild->getName()}");
        $regGuildName = (Auth::user()->getGuildPermissions($guild) === null) ? ("Register") : ("Dashboard");
        $syncGuild = (Auth::user()->getGuildPermissions($guild) == 0) ?
            ("<a role='button' href='../register-guild/{$guild}' class='btn btn-primary'>Synchronize</a>") : ("");
        $box->append(
            "<div class='card pl-4 mb-4 mt-4' style='width: 70%; margin-left: 15%;'>
                <div class='card-body'>
                    <div class='box'>
                        <div>{$img}</div>
                        <div style='padding-left: 10px'>
                            <h5 class='card-title text-dark font-weight-bold'>{$guild->getName()}</h5>
                            <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                        </div>
                        <div class='push mobile-button'>{$syncGuild}<a role='button' href='{$regGuildLink}' class='btn btn-primary ml-1'>{$regGuildName}</a></div>
                    </div>
                </div>
            </div>"
        );
    }
} else {
    $box->append(
        Alert::danger(
            "No registered guilds found",
            "You need to be part of a guild where your Guild Master has registered it here!<br>
        You can contact a Guild Master if you wish for a guild to be registered.",
            "mt-5 mb-5"
        )
    );
}
echo $box->render();
echo "</div>";
