<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

$guild = null;

if (!Auth::user()->isManagingGuild()) {
    echo "<title>GuildCP &bull; GCP Dashboard</title>";
} else {
    $guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());
    echo "<title>GuildCP &bull; {$guild->getName()} Dashboard</title>";
}

echo "<div class='content'>";
$box = new Box("Guild Control Panel");
$box->addClass("container mt-5 mb-5");

if ($guild !== null) {
    $box->append("<p class='text-center'>You are currently managing guild: <strong>{$guild->getName()}</strong>.</p>");
}

// Set manage guild ID for user or null it
if (isset($_GET["manage"])) {
    $manage = $_GET["manage"];
    if (\GuildCP\Blizzard\Guild::byId($manage) === null) {
        Auth::user()->setManageGuildId();
        Redirect::to("../");
    } else {
        $guild = \GuildCP\Blizzard\Guild::byId($manage);

        if (Auth::user()->getGuildPermissions($guild) !== null) {
            Auth::user()->setManageGuildId($guild->getId());
            Redirect::to("../");
        } else {
            Auth::user()->setManageGuildId();
            Redirect::to("../");
        }
    }
}

$permissions = Auth::user()->getGuildPermissionsArray();
$imgAlly = "<img src='https://images-na.ssl-images-amazon.com/images/I/41Vi4vmvrBL.jpg' class='rounded-circle' alt='Alliance' width='75px' height='70px'>";
$imgHorde = "<img src='https://images-na.ssl-images-amazon.com/images/I/31TI9yXs4jL.jpg' class='rounded-circle' alt='Horde' width='75px' height='70px'>";

foreach ($permissions as $guildPermissions) {
    $guild = $guildPermissions->getGuild();
    $img = ($guild->getFaction()) ? ($imgHorde) : ($imgAlly);
    $role = ($guildPermissions->getPermissions() == 0) ? ("Guild Master") : (($guildPermissions->getPermissions() == 1) ? ("Officer") : ("Member"));
    //$box->append("<p>Guild: {$guild->getName()} (ID: {$guild->getId()}), permissions: {$guildPermissions->getPermissions()}</p>");
    $box->append(
        "<div class='card pl-4 mb-4 mt-4' style='width: 70%; margin-left: 15%;'>
            <div class='card-body'>
                <div class='box'>
                    <div>{$img}</div>
                    <div style='padding-left: 10px'>
                        <h5 class='card-title text-dark font-weight-bold'>{$guild->getName()} - {$role}</h5>
                        <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                    </div>
                    <div class='push mobile-button'><a role='button' href='manage/{$guild->getId()}' class='btn btn-primary'>Manage</a></div>
                </div>
            </div>
        </div>"
    );
}

if (Auth::user()->isManagingGuild()) {
    $box->append("<div class='container text-center'><a role='button' href='manage/0' class='btn btn-primary'>Cancel Manage</a></div>");
}

echo $box->render();
echo "</div>";

