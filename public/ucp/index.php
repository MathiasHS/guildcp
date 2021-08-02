<?php namespace GuildCP;

require_once "../include/header.php";

if (!Auth::check()) {
    Redirect::to("login");
}

$submenu = new Submenu("");
$submenu
        ->addItem("default", "Dashboard", "<span style='font-size: 2.5rem;' class='fas d-inline-block fa-tachometer-alt'></span>")
        ->addItem("settings", "Settings", "<span style='font-size: 2.5rem;' class='fas d-inline-block fa-user-cog'></span>", true);
        

if (Auth::user()->hasCharacters()) {
    $submenu->addItem("characters", "Characters", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-dungeon'></span>", true);
    $submenu->addItem("guilds", "Guilds", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-user-friends'></span>", true);
}

$submenu->addItem("logout", "Log out", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-sign-out-alt'></span>", true);

$user = Auth::user();

echo "<div class='hero-image'>
    <div class='hero-text'>
        <h1>{$user->getUsername()}<br>UCP</span></h1>
        <p class='overflow-mobile'><strong>U</strong>ser <strong>C</strong>ontrol <strong>P</strong>anel</p>
    </div>
</div>";

echo $submenu->render();

// Surpress is not set errors, fall to default
$page = @$_GET["p"];
switch ($page) {
    case 'settings':
        require "pages/settings.php";
        break;

    case 'characters':
        require "pages/characters.php";
        break;

    case 'guilds':
        require "pages/guilds.php";
        break;

    case 'logout':
        Auth::logout();
        Redirect::to("login");
        break;

    default:
        require "pages/default.php";
        break;
}

require_once "../include/footer.php";
