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
        $box->append(Alert::danger("Failed to register guild", "The guild specified: \"{$blizzardClient->getRegion()}-{$data[1]}-{$data[0]}\" could not be found.{$back}"));
        echo $box->render();
        require_once "include/footer.php";
        die();
    }

    if ($guild->hasGuildMasterCharacter(Auth::user()) || Auth::user()->getGuildPermissions($guild) == 0) {
        if (!$guild->isRegistered()) {
            $box->append(Alert::success("Successfully registered {$data[0]}", "The guild has been registered and saved.{$back}"));
        } else {
            $box->append(Alert::success("Successfully synchronized {$data[0]}", "The guild has been synchronized to the database.{$back}"));
        }
        $guild->save(Auth::user());
    } else {
        $box->append(Alert::danger("Failed to register {$data[0]}", "You need to own the Guild Master character of the guild in order to register it!{$back}"));
    }

    echo $box->render();
} else {
    Redirect::to("../");
}

require_once "include/footer.php";
