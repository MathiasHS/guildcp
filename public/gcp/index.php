<?php namespace GuildCP;

require_once "../include/header.php";

if (!Auth::check()) {
    Redirect::to("../ucp/login");
}

$submenu = new Submenu("");
$submenu->addItem("default", "Dashboard", "<span style='font-size: 2.5rem;' class='fas d-inline-block fa-tachometer-alt'></span>");

$user = Auth::user();

if ($user->isManagingGuild()) {
    $submenu->addItem("events", "Events", "<span style='font-size: 2.5rem;' class='far fa-calendar-alt d-inline-block '></span>")
            ->addItem("roster", "Roster", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-user-friends'></span>")
            ->addItem("attendance", "Attendance", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-hourglass-start'></span>");

    if ($user->getManageGuildPermissions() <= 1) {
        $submenu->addItem("applicants", "Applicants", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-mail-bulk'></span>");
    }
            
    if ($user->getManageGuildPermissions() == 0) {
        $submenu->addItem("members", "Members", "<span style='font-size: 2.5rem;' class='d-inline-block fas fa-users'></span>")
            ->addItem("settings", "Settings", "<span style='font-size: 2.5rem;' class='fas d-inline-block fa-user-cog'></span>", true);
    }

    // In case a guild has been deleted from the database, this could also be done in the player class
    if (\GuildCP\Blizzard\Guild::byId($user->getManageGuildId()) === null) {
        Auth::user()->setManageGuildId(null);
    }
}

echo "<div class='hero-image'>
    <div class='hero-text'>
        <h1>{$user->getUsername()}<br>GCP</h1>
        <p class='overflow-mobile'><strong>G</strong>uild <strong>C</strong>ontrol <strong>P</strong>anel</p>
    </div>
</div>";

echo $submenu->render();

// Surpress is not set errors, fall to default
$page = @$_GET["p"];
switch ($page) {
    case 'attendance':
        require "pages/attendence.php";
        break;

    case 'events':
        require "pages/events.php";
        break;

    case 'roster':
        require "pages/roster.php";
        break;

    case 'settings':
        require "pages/settings.php";
        break;

    case 'members':
        require "pages/members.php";
        break;

    case 'applicants':
        require "pages/applicants.php";
        break;
    
    default:
        require "pages/default.php";
        break;
}

require_once "../include/footer.php";
