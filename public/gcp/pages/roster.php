<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

if (!Auth::user()->isManagingGuild()) {
    Redirect::to("/");
}
$access = false;
if (Auth::user()->getManageGuildPermissions() < 1) {
    $access = true;
}
$guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());

echo "<title>GuildCP &bull; {$guild->getName()} Roster</title>";
echo "<div class='content'>";

if (isset($_POST['editSubmit']) && $access) {
    $stmt = Db::getPdo()->prepare("SELECT `name`, `level`, `class`, `race` FROM `guilds_characters` WHERE `guild_id` = :guild_id AND `name` = :name");

    $stmt->execute([
        ":guild_id" => $guild->getId(),
        ":name"     => $_POST['charName']
    ]);

    $allowed_roles = [
        "TANK",
        "HEALING",
        "DPS",
        "REMOVE"
    ];

    if (in_array($_POST['role'], $allowed_roles)) {
        if ($stmt->rowCount()) {
            if ($_POST['role'] == 'REMOVE') {
                $stmt = Db::getPdo()->prepare("DELETE FROM `guilds_roster` WHERE `guild_id` = :guild_id AND `name` = :name");

                $stmt->execute([
                    ":guild_id" => $guild->getId(),
                    ":name"     => $_POST['charName']
                ]);

                echo Alert::success("{$_POST['charName']} removed", "You have successfully removed {$_POST['charName']} from the roster", "container mt-5 mb-5");
            } else {
                $stmt = Db::getPdo()->prepare("INSERT INTO `guilds_roster`(`guild_id`, `name`, `role`) VALUES (:guild_id, :name, :role) ON DUPLICATE KEY UPDATE `role` = :role1");
    
                $stmt->execute([
                    ":guild_id" => $guild->getId(),
                    ":name"     => $_POST['charName'],
                    ":role"     => $_POST['role'],
                    ":role1"    => $_POST['role']
                ]);
    
                echo Alert::success("{$_POST['charName']} added as {$_POST['role']}", "You have successfully added {$_POST['charName']} to the roster", "container mt-5 mb-5");
            }
        } else {
            echo Alert::warning("Error - {$_POST['charName']} not found", "The specified player could not be found. Please try again, if the error occurs, please do not hesitate to contact a site administrator", "container mt-5 mb-5");
        }
    } else {
        echo Alert::warning("Error - {$_POST['role']} is invalid", "The role specified could not be found. If the errors occurs, please do not hesitate to contact a site administrator.", "container mt-5 mb-5");
    }
}

if (isset($_GET['edit']) && $access) {
    $target = @$_GET['edit'];

    $stmt = Db::getPdo()->prepare("SELECT `name`, `level`, `class`, `race` FROM `guilds_characters` WHERE `guild_id` = :guild_id AND `name` = :name");

    $stmt->execute([
        ":guild_id" => $guild->getId(),
        ":name"     => $target
    ]);

    if ($stmt->rowCount()) {
        $box = new Box("Edit {$target}");
        $box->setClass("container mt-5 mb-5");

        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $class = \GuildCP\Blizzard\WowCharacter::getClassStringById($row['class']);
            $race = \GuildCP\Blizzard\WowCharacter::getRaceStringById($row['race']);

            $select = "
            <select class='form-control' id='role' name='role'>
                <option value='TANK'>TANK</option>
                <option value='HEALING'>HEALING</option>
                <option value='DPS'>DPS</option>
                <option value='REMOVE'>REMOVE</option>
            </select>
            ";

            $box->append(
                "<form action='' method='POST'>
                    <table class='table table-borderless table-striped text-light'>
                        <tr><th>Name</th><td>{$row['name']}</td></tr>
                        <tr><th>Level</th><td>{$row['level']}</td></tr>
                        <tr><th>Race</th><td>{$race}</td></tr>
                        <tr><th>Class</th><td>{$class}</td></tr>
                        <tr><th>Role</th><td>{$select}</td></tr>
                        <tr><th>Assign</th><td><button type='submit' name='editSubmit' class='btn btn-primary btn-block'>Submit</button></td></tr>
                    </table>
                    <input type='hidden' name='charName' value='{$row['name']}'>
                </form>"
            );
        }

        echo $box->render();
    } else {
        echo Alert::warning("Error - {$target} not found", "The specified player could not be found. Please try again, if the error occurs, please do not hesitate to contact a site administrator", "container mt-5 mb-5");
    }
}

$stmt = Db::getPdo()->prepare("SELECT `name`, `role` FROM `guilds_roster` WHERE `guild_id` = :guild_id ORDER BY `role` DESC");
$stmt->execute([":guild_id" => $guild->getId()]);

if ($stmt->rowCount()) {
    $box = new Box("Characters - Roster");
    $box->setClass("container mt-5 mb-5");

    $editHeader = ($access) ? ("<th scope='col'>Edit</th>") : ("");

    $box->append(
        "<div class='table-responsive'>
            <table class='table table-borderless table-striped text-light'>
            <thead>
                <tr>
                    <th scope='col'>Name</th>
                    <th scope='col'>Role</th>
                    {$editHeader}
                </tr>
            </thead>
            <tbody>"
    );

    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $editData = ($access) ? ("<td><a role='button' class='btn btn-primary btn-block' href='?p=roster&edit={$row['name']}'>Edit</a></td>") : ("");
        $box->append(
            "<tr>
                <td>{$row['name']}</td>
                <td>{$row['role']}</td>
                {$editData}
            </tr>"
        );
    }

    $box->append(
        "</tbody>
    </table>
    </div>"
    );

    echo $box->render();
}

if ($access) {
    $characters = \GuildCP\Blizzard\WowCharacter::getGuildCharacters($guild->getId());
    
    $box = new Box("Characters - Non-Roster");
    $box->setClass("container mt-5 mb-5");
    
    $box->append(
        "<div class='table-responsive'>
            <input class='form-control' id='myInput' type='text' placeholder='Search . . '>
            <br>
            <table class='table table-borderless table-striped text-light'>
                <thead>
                    <tr>
                        <th scope='col'>Name</th>
                        <th scope='col'>Class</th>
                        <th scope='col'>Edit</th>
                    </tr>
                </thead>
                <tbody id='myTable'>"
    );
    
    foreach ($characters as $character) {
        $box->append(
            "<tr>
                <td>{$character->getName()}</td>
                <td>{$character->getClassString()}</td>
                <td><a role='button' class='btn btn-primary btn-block' href='?p=roster&edit={$character->getName()}'>Edit</a></td>
            </tr>"
        );
    }
    $box->append(
        "</tbody>
    </table>
    </div>"
    );
    
    echo $box->render();
}
echo "</div>";

echo "
<script>
    $(document).ready(function(){
        $(' #myInput').on('keyup', function() {
            var value = $ (this).val().toLowerCase();
            $('#myTable tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
";