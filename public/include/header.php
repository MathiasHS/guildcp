<?php
session_start();
ob_start();

if (!defined("IN_GCP")) {
    define("IN_GCP", 1);
}

header("Content-Type: text/html; charset=utf-8");

require "config.php";

require __DIR__  . "/../../vendor/autoload.php";
require_once __DIR__ . "/classes/db.class.php";
require_once __DIR__ . "/classes/redirect.class.php";
require_once __DIR__ . "/classes/auth.class.php";
require_once __DIR__ . "/classes/box.class.php";
require_once __DIR__ . "/classes/menu.class.php";
require_once __DIR__ . "/classes/menuitem.class.php";
require_once __DIR__ . "/classes/submenu.class.php";
require_once __DIR__ . "/classes/submenuitem.class.php";
require_once __DIR__ . "/classes/alert.class.php";
require_once __DIR__ . "/classes/blizzard/client.class.php";
require_once __DIR__ . "/classes/blizzard/wowcommunity.class.php";
require_once __DIR__ . "/classes/blizzard/guild.class.php";
require_once __DIR__ . "/classes/blizzard/guildevent.class.php";
require_once __DIR__ . "/classes/blizzard/guildapplication.class.php";
require_once __DIR__ . "/classes/blizzard/guildpermission.class.php";
require_once __DIR__ . "/classes/blizzard/battlenet.class.php";

$db = \GuildCP\Db::getPdo();
$blizzardClient = new \GuildCP\Blizzard\Client(GuildCP\Config::get("blizzard.client.id"), GuildCP\Config::get("blizzard.client.secret"));
?>
<html>

<head>
    <link rel="shortcut icon" href="/include/images/GCPikon.ico" type="image/x-icon" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="This free World of Warcraft tool will help you manage your guild by having your own guild website.
    Recruit teammates or find a guild customized to your playstyle." />
    <meta name="keywords" content="world of warcraft, guild, master, guild control panel, guild page, guild finder, raid, roster, events" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Roboto|Montserrat:400,500,600,700,800,900|Open+Sans:400,700,800|Fira+Sans:400,600|Lato:400,500,600" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/include/css/style.css">
</head>

<body>
    <div id="header">
        <?php
        $main_menu = new \GuildCP\Menu();
        $main_menu
            ->addItem("/", "Home")
            ->addItem("../../find-guild", "Guilds")
            ->addItemObject(
                (new \GuildCP\MenuItem("#", "Information"))
                    ->addChild("../../about", "About us")
                    ->addChild("../../privacy-policy", "Privacy policy")
            
            
            );
            

        if (\GuildCP\Auth::check()) {
            // if (\GuildCP\Auth::user()->isAdmin()) {
            //     $main_menu->addItemObject(
            //         (new \GuildCP\MenuItem("../admin/", "ACP"))
            //             ->addChild("../admin/", "Dashboard")
            //             ->addChild("../admin/", "Users")
            //             ->addChild("../admin/", "Guilds")
            //             ->addChild("../admin/", "Tools")
            //     );
            // }

            if (\GuildCP\Auth::user()->getGuildPermissionsCount() > 0) {
                $gcp_menuItem = new \GuildCP\MenuItem("../gcp/", "GCP");
                $gcp_menuItem->addChild("../../gcp/", "Dashboard");

                if (\GuildCP\Auth::user()->isManagingGuild()) {
                    $gcp_menuItem->addChild("../../gcp/?p=events", "Events")
                        ->addChild("../../gcp/?p=roster", "Roster")
                        ->addChild("../../gcp/?p=attendance", "Attendance");

                    if (\GuildCP\Auth::user()->getManageGuildPermissions() <= 1) {
                        $gcp_menuItem->addChild("../../gcp/?p=applicants", "Applicants");
                    }

                    if (\GuildCP\Auth::user()->getManageGuildPermissions() == 0) {
                        $gcp_menuItem->addChild("../../gcp/?p=members", "Members")
                            ->addChild("../../gcp/settings", "Settings");
                    }
                }

                $main_menu->addItemObject($gcp_menuItem);
            }
            $ucp_menuItem = new \GuildCP\MenuItem("..ucp/", "UCP");
            $ucp_menuItem->addChild("../../ucp/", "Dashboard")
                ->addChild("../../ucp/settings", "Settings");

            if (\GuildCP\Auth::user()->hasCharacters()) {
                $ucp_menuItem->addChild("../../ucp/characters", "Characters");
                $ucp_menuItem->addChild("../../ucp/guilds", "Guilds");
            }

            $ucp_menuItem->addChild("../../ucp/logout", "Log out");

            $main_menu->addItemObject($ucp_menuItem);
        } else {
            $main_menu->addItem("../../ucp/", "Login");
        }

        echo $main_menu->render();
        echo "</div>";
        ?>

        <div class="alert text-center bg-dark cookiealert" role="alert">
            <b>Do you like cookies?</b> &#x1F36A; We use cookies to ensure you get the best experience on our website. <a href="../privacy-policy">Learn more</a>

            <button type="button" class="btn btn-primary btn-sm acceptcookies" aria-label="Close">
                I agree
            </button>
        </div>

        <script>
            $(function() {
                var cookieAlert = $(".cookiealert");
                var acceptCookies = $(".acceptcookies");

                var userAlreadyAcceptedCookies = function() {
                    var userAcceptedCookies = false;
                    var cookies = document.cookie.split(";");
                    for (var i = 0; i < cookies.length; i++) {
                        var c = cookies[i].trim();
                        if (c.indexOf("<?php echo GuildCP\Config::get("cookie.accept_cookies"); ?>") == 0) {
                            userAcceptedCookies = c.substring(<?php echo strlen(GuildCP\Config::get("cookie.accept_cookies")); ?> + 1, c.length);
                        }
                    }

                    return userAcceptedCookies;
                };

                var setUserAcceptCookies = function() {
                    var d = new Date();
                    d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000));
                    var expires = "expires=" + d.toUTCString();

                    document.cookie = "<?php echo GuildCP\Config::get("cookie.accept_cookies"); ?>" + "=" + true + ";" + expires + ";path=/";
                };

                if (!userAlreadyAcceptedCookies()) {
                    cookieAlert.addClass("show");
                }

                acceptCookies.click(function() {
                    setUserAcceptCookies();
                    cookieAlert.removeClass("show");
                });
            });
        </script>