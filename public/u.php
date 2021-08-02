<?php namespace GuildCP;
require_once "include/header.php";

$username = @$_GET["name"];

echo"<div class='content'>";
echo "
<div class='user-banner-img'>
    <div class='hero-text pt-5'>
        <h1>{$username}</h1>
        <p class='overflow-mobile'><strong>U</strong>ser <strong>C</strong>ontrol <strong>P</strong>anel</p>
    </div>
</div>";
    
if (isset($_GET["name"])) {
    $target = Player::fromName($username);
    $userName = ($target !== null) ? ("{$target->getUsername()} Information") : ("Not Found");
    echo "<title>GuildCP &bull; User {$userName}</title>";

    $box = new \GuildCP\Box("User Information &bull; {$_GET['name']}");
    if ($target !== null && $target->isDefined() && $target->getVisibility()) {
        $view = @$_GET["view"];
        
        $generalActive = (!isset($_GET["view"]) ? ("active") : ("text-light"));
        $guildActive = ($view == "guilds") ? ("active") : ("text-light");
        $charactersActive = ($view == "characters") ? ("active") : ("text-light");

        $email = ($target->getShowEmail()) ? ($target->getEmail()) : ("Private");
        $userBattleTag = ($target->getShowBattleTag()) ? ($target->getBattleTag()) : ("Private");
        $characterCount = $target->getCharacterCount();
        $userImg = $target->getCharacters();
        
        $box->setClass("container mt-5 mb-5");
        $box->append(
            "<div class='container'>
                <div class='row'>
                    <div class='col-sm-3 mb-3'>
                        <div class='card text-dark'>
                            <img class='card-img-top' src='{$userImg[2]->getImageURL()}' alt='{$userImg[2]->getName()}'>
                            <div class='card-block'>
                                <h5 class='text-bold text-center'>{$userImg[2]->getName()}</h5>
                            </div>
                        </div>
                    </div>
                    <div class='col-sm-9'>
                        <ul class='nav nav-tabs' id='myTab' role='tablist'>
                            <li class='nav-item'>
                                <a class='nav-link {$generalActive}' id='general-tab' href='/user/{$target->getUsername()}' role='tab'>General</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link {$guildActive}' id='guild-tab'  href='/user/{$target->getUsername()}/guilds' role='tab'>Guilds</a>
                            </li>
                            <li class='nav-item'>
                                <a class='nav-link {$charactersActive}' id='characters-tab' href='/user/{$target->getUsername()}/characters' role='tab'>Characters</a>
                            </li>
                        </ul>
                        <div class='tab-content' id='myTabContent'>");

        switch ($view) {
            case "guilds": {
                $box->append( "<div class='tab-pane fade show active' id='guild' role='tabpanel' aria-labelledby='guild-tab'>");

                $guildPermissions = $target->getGuildPermissionsArray();
                $imgAlly = "<img src='https://images-na.ssl-images-amazon.com/images/I/41Vi4vmvrBL.jpg' class='rounded-circle' alt='Alliance' width='75px' height='70px'>";
                $imgHorde = "<img src='https://images-na.ssl-images-amazon.com/images/I/31TI9yXs4jL.jpg' class='rounded-circle' alt='Horde' width='75px' height='70px'>";

                foreach ($guildPermissions as $guildPermission) {
                    $guild = $guildPermission->getGuild();
                    $img = ($guild->getFaction()) ? ($imgHorde) : ($imgAlly);
                    $role = ($guildPermission->getPermissions() == 0) ? ("Guild Master") : (($guildPermission->getPermissions() == 1) ? ("Officer") : ("Member"));
                    $box->append("<div class='card pl-4 mb-4 mt-4'>
                                    <div class='card-body'>
                                        <div class='box'>
                                            <div>{$img}</div>
                                            <div style='padding-left: 10px'>
                                                <h5 class='card-title text-dark font-weight-bold'>{$guild->getName()} - {$role}</h5>
                                                <h6 class='card-subtitle mb-2 text-muted'>{$guild->getRealm()}</h6>
                                            </div>
                                            <div class='push mobile-button'><a role='button' href='../../guild/{$guild->getGuildPath()}' class='btn btn-primary'>Visit</a></div>
                                        </div>
                                    </div>
                                </div>");
                }

                $box->append( "
                                </div>
                            </div>
                        </div>
                    </div>
                </div>");

                break;
            }
            case "characters": {
                $box->append(
                        "<div class='tab-pane fade show active' id='characters' role='tabpanel' aria-labelledby='characters-tab'>
                            <p><i>We only save characters from level 110-120 in our database</i></p>
                            <div class='row mt-3'>");
                        foreach ($userImg as $img) {
                            $content = "
                                <div class='col-sm-4 mt-3'>
                                    <div class='card text-dark'>
                                        <img class='card-img-top' src='{$img->getImageURL()}' alt='{$img->getName()}'>
                                        <div class='card-block'>
                                            <h5 class='text-bold text-center'>{$img->getName()} - {$img->getLevel()}</h5>
                                            <div class='text-center'>
                                                <div style='display:inline-block' class='mb-2'><a role='button' href='https://raider.io/characters/{$img->getRegion()}/{$img->getRealm()}/{$img->getName()}' target='_blank' class='btn btn-primary'>Raider IO</a></div>
                                                <div style='display:inline-block' class='mb-2'><a role='button' href='https://www.warcraftlogs.com/character/{$img->getRegion()}/{$img->getRealm()}/{$img->getName()}' target='_blank' class='btn btn-primary'>Warcraft Logs</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ";
                            $box->append($content);
                        }

                $box->append(
                    "
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>");
                break;
            }
            default: {
                $box->append( "
                                <div class='tab-pane fade show active' id='general' role='tabpanel' aria-labelledby='general-tab'>
                                    <table class='table table-striped table-dark mt-2'>
                                        <tbody>
                                            <tr>
                                                <th scope='row'>Username</th>
                                                <td>{$target->getUsername()}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>BattleTag</th>
                                                <td>{$userBattleTag}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Email</th>
                                                <td>{$email}</td>
                                            </tr>
                                            <tr>
                                                <th scope='row'>Characters over lvl 110</th>
                                                <td>{$characterCount}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>");
            }
        }
        echo $box->render();
    } else {
        $stmt = Db::getPdo()->prepare("SELECT `username` FROM `accounts` WHERE `username` LIKE :username");
        $stmt->execute(["username" => "%{$username}%"]);

        if ($stmt->rowCount()) {
            $suggestion = $stmt->fetch();

            $box->setClass("container mt-5 mb-5");
            $box->append(Alert::danger("Could not find any user named: {$username} ", "Did you mean: <i>{$suggestion[0]}</i>"));
            echo $box->render();
        } else {
            $box->setClass("container mt-5 mb-5");
            $box->append(Alert::danger("Could not find any user named: ", "{$username}"));
            echo $box->render();
        }
    }
}

echo "</div>";





require_once "include/footer.php";