<?php namespace GuildCP;

require_once "include/header.php";

use GuildCP\Blizzard\Guild;
use GuzzleHttp\Exception\ClientException;

if (isset($_GET["guild"]) && strlen($_GET["guild"])) {
    $data = explode(";", $_GET["guild"]);
    $box = new Box("Register {$data[0]}");
    $box->setClass("container mt-5 mb-5");

    echo "<title>GuildCP &bull; Register {$data[0]}</title>";

    $blizzardClient->setRegion(Auth::user()->getRegion());

    $back = "<br><br><a href='javascript:history.back()' class='btn btn-dark'><span>Go back</span></a>";

    try {
        $guild = new Guild($blizzardClient, $data[1], $data[0]);
    } catch (ClientException $e) {
        $box->append(Alert::danger("Failed to register for guild", "The guild specified: \"{$blizzardClient->getRegion()}-{$data[1]}-{$data[0]}\" could not be found.{$back}"));
        echo $box->render();
        require_once "include/footer.php";
        die();
    }

    $registeredGuilds = array();
    $checkedGuilds = array();

    foreach (Auth::user()->getCharacters() as $character) {
        if ($character->hasGuild()) {
            if (!in_array($character->getGuildName() . ";" . $character->getGuildRealm(), $checkedGuilds)) {
                $playerGuild = new Guild($blizzardClient, $character->getGuildRealm(), $character->getGuildName());
                $checkedGuilds[] = $playerGuild->__toString();

                if ($playerGuild->isRegistered()) {
                    $registeredGuilds[] = $playerGuild;
                }
            }
        }
    }

    $canRegister = false;
    foreach ($registeredGuilds as $regGuild) {
        if ($regGuild->__toString() == $guild->__toString()) {
            $canRegister = true;
            break;
        }
    }

    if ($canRegister) {
        $dashboardLink = "<br><br><a href='../guild/{$guild->getRegion()};{$guild->getRealm()};{$guild->getName()}' class='btn btn-dark'><span>Guild Page</span></a>";
        $box->append(Alert::success("Successfully registered for: {$data[0]}", "You have been registered for the guild.{$dashboardLink}"));

        $stmt = $db->prepare("INSERT INTO `guilds_permissions`(`guild_id`, `user_id`, `permissions`) VALUES (:guild_id, :user_id, 2) ON DUPLICATE KEY UPDATE `permissions` = 2");
        $stmt->execute([
            ":guild_id"     => $guild->getId(),
            ":user_id"      => Auth::user()->getId()
        ]);
    } else {
        $box->append(Alert::danger("Failed to register {$data[0]}", "You need to have a registered character in the guild to register for it!{$back}"));
    }

    echo $box->render();
} else {
    Redirect::to("../");
}

require_once "include/footer.php";
