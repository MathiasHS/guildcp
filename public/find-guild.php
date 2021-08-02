<?php namespace GuildCP;

use GuildCP\Blizzard\Guild;

require_once "include/header.php";

echo"<title>GuildCP &bull; Guild Information</title>";
echo"
<div class='find-guild-image'>
    <div class='hero-text'>
        <h1>Find a guild!</h1>
        <p>Find your rightful place amongst the best of the best</p>
    </div>
</div>
";

echo "<div class='content'>";

$box = new \GuildCP\Box("Guilds");
$box->setClass("container mt-5 mb-5");

$box->append( "<!--
        <h1 class='text-center'>Filters</h1>
        <div class='container pt-2 pb-4'>
            <div class='btn-group d-flex justify-content-center'>
                <div class='dropdown'>
                    <button class='btn btn-secondary dropdown-toggle' type='button' id='dropdownMenuButton'
                        data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        Region
                    </button>
                    <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                        <a class='dropdown-item' href='#'>Americas</a>
                        <a class='dropdown-item' href='#'>Asia</a>
                        <a class='dropdown-item' href='#'>Europe</a>
                        <a class='dropdown-item' href='#'>Korea</a>
                    </div>
                </div>

                <div class='dropdown'>
                    <button class='btn btn-secondary dropdown-toggle ml-3' type='button' id='dropdownMenuButton'
                        data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        Realm
                    </button>
                    <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                        <a class='dropdown-item' href='#'>Realm array</a>
                    </div>
                </div>

                <div class='dropdown'>
                    <button class='btn btn-secondary dropdown-toggle ml-3' type='button' id='dropdownMenuButton'
                        data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                        Faction
                    </button>
                    <div class='dropdown-menu' aria-labelledby='dropdownMenuButton'>
                        <a class='dropdown-item' href='#'>Alliance</a>
                        <a class='dropdown-item' href='#'>Horde</a>
                    </div>
                </div>
            </div>
        </div>-->");

$box->append("<div class='row justify-content-center'>");

$stmt = Db::getPdo()->query("SELECT `id`, `region`, `name`, `realm`, `level`, `faction` FROM `guilds`");

while ($row = $stmt->fetch()) {
    $guild = Guild::byId($row['id']);
    
    // Only show guilds with visibility set to 1
    if ($guild->getVisibility() != 1) {
        continue;
    }
    
    $buttonFaction = ($guild->getFaction()) ? ("btn-danger") : ("btn-primary");
    $buttonApply = (!$guild->getCanApply()) ? ("") : "<a href='../apply-for-guild/{$row['id']};{$row['region']};{$row['realm']};{$row['name']}'>
                                <button type='button' class='btn {$buttonFaction} ml-2 mt-2'>Apply</button></a>";
    if (!$row['faction']) {
        $box->append(
            "<div class='col-sm-5'>
                <div class='card pl-4 mb-4 mt-4' style='width: 100%;'>
                    <div class='card-body'>
                        <div class='box'>
                            <div><img src='https://images-na.ssl-images-amazon.com/images/I/41Vi4vmvrBL.jpg' class='rounded-circle' alt='Alliance' width='75px' height='70px'></div>
                                <div style='padding-left: 10px'>
                                    <h5 class='card-title font-weight-bold text-dark'>{$row['name']}</h5>
                                    <h6 class='card-subtitle mb-2 text-muted'>{$row['realm']}</h6>
                                </div>
                            </div>
                        <div>
                            <a href='../guild/{$row['region']};{$row['realm']};{$row['name']}'>
                                <button type='button' class='btn btn-primary mt-2'>View guild</button>
                            </a>
                            {$buttonApply}
                        </div>
                    </div>
                </div>
            </div>"
        );
    } else {
        $box->append( "
            <div class='col-sm-5'>
                <div class='card pl-4 mb-4 mt-4' style='width: 100%;'>
                    <div class='card-body'>
                        <div class='box'>
                            <div><img src='https://images-na.ssl-images-amazon.com/images/I/31TI9yXs4jL.jpg' class='rounded-circle' alt='Horde' width='75px' height='70px'></div>
                                <div style='padding-left: 10px'>
                                    <h5 class='card-title font-weight-bold text-dark'>{$row['name']}</h5>
                                    <h6 class='card-subtitle mb-2 text-muted'>{$row['realm']}</h6>
                                </div>
                            </div>
                        <div>
                            <a href='../guild/{$row['region']};{$row['realm']};{$row['name']}'>
                                <button type='button' class='btn btn-danger mt-2'>View guild</button>
                            </a>
                            {$buttonApply}
                        </div>
                    </div>
                </div>
            </div>");
    }
}

$box->append("</div>");

echo $box->render();
echo "</div>";
require_once "include/footer.php";