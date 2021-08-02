<?php namespace GuildCP;

require_once "include/header.php";

use GuildCP\Blizzard\Guild;
use GuzzleHttp\Exception\ClientException;
use GuildCP\Blizzard\InvalidOptionsException;

$data = explode(";", $_GET["data"]);
$guildNameTest = "";
echo "<div class='user-banner-img'>
    <div class='hero-text pt-4'>
        <h1>{$data[2]}</h1>
        <p class='overflow-mobile'><strong>G</strong>uild <strong>O</strong>verview</p>
    </div>
</div>";

echo "<div class='content'>";

if (isset($_GET["data"])) {
    $die = false;
    $dieReason = "";
    try {
        $blizzardClient->setRegion(strtolower($data[0]));
    } catch (InvalidOptionsException $e) {
        $die = true;
        $dieReason = $e->getMessage();
    }

    $guild = null;
    try {
        $guild = new Guild($blizzardClient, $data[1], $data[2]);
        $guildName = ($guild->isRegistered()) ? ("{$guild->getName()}") : ("Not Found");
        echo "<title>GuildCP &bull; Guild {$guildName} Information</title>";
    } catch (ClientException $e) {
        $die = true;
        $dieReason = "Guild not found, is the realm and guild name spelled correctly?";
    }

    if (!$die && !$guild->isRegistered()) {
        $die = true;
        $dieReason = "Guild is not registered.";
    }

    if (!$die && $guild->getVisibility() != 1 && !Auth::check()) {
        $die = true;
        $dieReason = "The guild is not public, or you are not logged in to see it.";
    }

    if (!$die && $guild->getVisibility() < 1 && Auth::user()->getGuildPermissions($guild) === null) {
        $die = true;
        $dieReason = "User not registered for specified guild.";
    }

    if ($die == true) {
        $box = new Box("Guild not found", Alert::warning("Guild not found", "{$dieReason}", "mt-1 mb-1"));
        $box->setClass("container mt-5 mb-5");
        echo $box->render();
        echo "</div>";
        require_once "include/footer.php";
        die();
    }

    if ($guild->getVisibility() == 1 || ($guild->getVisibility() == 0 && Auth::user()->getGuildPermissions($guild) <= 2) || ($guild->getVisibility() == -1 && Auth::user()->getGuildPermissions($guild) <= 1)) {
        $view = @$_GET["view"];

        $generalActive = (!isset($_GET["view"]) ? ("active") : ("text-light"));
        $rosterActive = ($view == "roster") ? ("active") : ("text-light");
        $infoActive = ($view == "info") ? ("active") : ("text-light");

        $box = new \GuildCP\Box("Guild Information: {$guild->getName()}");
        $box->setClass("container mt-5 mb-5");

        $imgAlly = "<img src='https://images-na.ssl-images-amazon.com/images/I/41Vi4vmvrBL.jpg' class='rounded-circle' alt='Alliance' width='150px' height='150px'>";
        $imgHorde = "<img src='https://images-na.ssl-images-amazon.com/images/I/31TI9yXs4jL.jpg' class='rounded-circle' alt='Horde' style='max-width: 100%'>";

        // $guild = $guildPermission->getGuild();
        $img = ($guild->getFaction()) ? ($imgHorde) : ($imgAlly);
        $faction = ($guild->getFaction()) ? ("Horde") : ("Alliance");

        $box->append(
            "<div class='container'>
                <div class='row'>
                    <div class='col-sm-2 mb-3'>
                        {$img}
                    </div>
                    <div class='col-sm-10'>
                        <ul class='nav nav-tabs' id='myTab' role='tablist'>
                            <li class='nav-item'>
                                <a class='nav-link {$generalActive}' id='general-tab' href='/guild/{$guild->getGuildPath()}' role='tab' aria-selected='true'>General</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link {$rosterActive}' id='characters-tab' href='/guild/{$guild->getGuildPath()}/roster' role='tab' aria-selected='false'>Roster</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link {$infoActive}' id='guild-tab'  href='/guild/{$guild->getGuildPath()}/info' role='tab' aria-selected='false'>Info</a>
                            </li>
                        </ul>
                        <div class='tab-content' id='myTabContent'>"
        );

        switch ($view) {
            case "roster":
                {
                    $stmt = Db::getPdo()->prepare(
                        "SELECT gc.`name`, gc.`thumbnail`, gc.`spec_name`, gr.`role`,
                         (SELECT `user_id` FROM `accounts_characters` 
                         WHERE `name` = gc.`name` AND `realm` = g.`realm`) AS `user_id` 
                         FROM `guilds_characters` gc 
                         INNER JOIN `guilds_roster` gr ON (gc.`name` = gr.`name`) 
                         INNER JOIN `guilds` g ON (g.`id` = gc.`guild_id`) WHERE gr.`guild_id` = :guild_id ORDER BY gr.`role` DESC"
                    );
                    $stmt->execute([":guild_id" => $guild->getId()]);

                    $box->append("
                                <div class='tab-pane fade show active' id='guild' role='tabpanel' aria-labelledby='guild-tab'>
                                    <div class='container'>
                                        <div class='row'>");

                    if ($stmt->rowCount()) {
                        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        $box->append( "<div class='col-sm-12 mb-3'>
                                        <div class='underline mt-3'><h1>Tanks</h1></div>
                                            <div class='row mb-3'>");

                        foreach ($result as $row) {
                            $img = "https://render-{$guild->getRegion()}.worldofwarcraft.com/character/{$row['thumbnail']}";
                            $aStart = "";
                            $aEnd = "";
                            if ($row['user_id'] != null) {
                                $target = Player::fromId($row['user_id']);
                                $aStart = ($target->getVisibility()) ? ("<a href='../../user/{$target->getUsername()}'>") : ("");
                                $aEnd = ($target->getVisibility()) ? ("</a>") : ("");
                            }
                            if ($row['role'] == "TANK") {
                                $box->append("
                                            <div class='col-sm-12 col-md-6'>
                                                {$aStart}
                                                    <div class='card mb-5 mt-5'>
                                                        <div class='card-body'>
                                                            <div class='box'>
                                                                <div><img src='{$img}' class='rounded-circle' alt='{$faction}' width='75px' height='70px'></div>
                                                                <div style='padding-left: 10px'>
                                                                    <h5 class='card-title text-dark font-weight-bold'>{$row['name']} - {$row['spec_name']}</h5>
                                                                    <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {$aEnd}
                                            </div>");
                            }
                        }
                        $box->append("</div></div>");

                        $box->append( "<div class='col-sm-12 mb-3'>
                                            <div class='underline mt-3'><h1>Healers</h1></div>
                                                <div class='row mb-3'>");

                        foreach ($result as $row) {
                            $img = "https://render-{$guild->getRegion()}.worldofwarcraft.com/character/{$row['thumbnail']}";
                            $aStart = "";
                            $aEnd = "";
                            if ($row['user_id'] != null) {
                                $target = Player::fromId($row['user_id']);
                                $aStart = ($target->getVisibility()) ? ("<a href='../../user/{$target->getUsername()}'>") : ("");
                                $aEnd = ($target->getVisibility()) ? ("</a>") : ("");
                            }
                            if ($row['role'] == "HEALING") {
                                $box->append("
                                        
                                            <div class='col-sm-12 col-md-6 col-lg-4'>
                                                {$aStart}
                                                    <div class='card mb-5 mt-5'>
                                                        <div class='card-body'>
                                                            <div class='box'>
                                                                <div><img src='{$img}' class='rounded-circle' alt='{$faction}' width='75px' height='70px'></div>
                                                                <div style='padding-left: 10px'>
                                                                    <h5 class='card-title text-dark font-weight-bold'>{$row['name']} - {$row['spec_name']}</h5>
                                                                    <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {$aEnd}
                                            </div>");
                            }
                        }
                        $box->append("</div></div>");

                        $box->append( "<div class='col-sm-12 mb-3'>
                                            <div class='underline mt-3'><h1>Damage dealers</h1></div>
                                                <div class='row mb-3'>");

                        foreach ($result as $row) {
                            $img = "https://render-{$guild->getRegion()}.worldofwarcraft.com/character/{$row['thumbnail']}";
                            $aStart = "";
                            $aEnd = "";
                            if ($row['user_id'] != null) {
                                $target = Player::fromId($row['user_id']);
                                $aStart = ($target->getVisibility()) ? ("<a href='../../user/{$target->getUsername()}'>") : ("");
                                $aEnd = ($target->getVisibility()) ? ("</a>") : ("");
                            }
                            if ($row['role'] == "DPS") {
                             $box->append( "
                                        <div class='col-sm-12 col-md-6 col-lg-4'>
                                            {$aStart}
                                                <div class='card mb-5 mt-5'>
                                                    <div class='card-body'>
                                                        <div class='box'>
                                                            <div><img src='{$img}' class='rounded-circle' alt='{$faction}' width='75px' height='70px'></div>
                                                            <div style='padding-left: 10px'>
                                                                <h5 class='card-title text-dark font-weight-bold'>{$row['name']} - {$row['spec_name']}</h5>
                                                                <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            {$aEnd}
                                        </div>");
                            }
                        }
                        $box->append("</div></div>");
                    }
                        
                        $box->append("
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>");
                    break;
                }
            case "info":
                {

                    $box->append("
                                <div class='tab-pane fade show active' id='characters' role='tabpanel' aria-labelledby='characters-tab'>
                                    <p>{$guild->getInformation()}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>");
                    break;
                }
            default:
                {
                    $applyButton = ($guild->getCanApply()) ? ("<div class='push mobile-button'><a role='button' href='/apply-for-guild/{$guild->getId()};{$guild->getGuildPath()}' class='btn btn-primary'>Apply</a></div>") : ("");
                    $box->append("
                                <div class='tab-pane fade show active' id='general' role='tabpanel' aria-labelledby='general-tab'>
                                    <table class='table table-striped table-dark mt-2'>
                                        <tbody>
                                            <tr>
                                                <th scope='row'>Guild name</th>
                                                <td>{$guild->getName()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Guild Master</th>
                                                <td>{$guild->getGuildMasterCharacter()->getName()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Realm</th>
                                                <td>{$guild->getRealm()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Faction</th>
                                                <td>{$faction}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Guild level</th>
                                                <td>{$guild->getGuildLevel()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Achievement points</th>
                                                <td>{$guild->getAchievement()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Registered members over lvl 110</th>
                                                <td>{$guild->getMemberCount()}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    {$applyButton}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>");
                }
        }
        echo $box->render();
    } else {
        $box = new Box("Guild not found", Alert::warning("Guild not found", "{$dieReason}", "mt-1 mb-1"));
        $box->setClass("container mt-5 mb-5");
        echo $box->render();
        echo "</div>";
        require_once "include/footer.php";
        die();
    }
}

echo "</div>";
require_once "include/footer.php";
