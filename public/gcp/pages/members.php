<?php namespace GuildCP;

use GuildCP\Blizzard\GuildPermission;

if (!defined("IN_GCP")) {
    die();
}

if (!Auth::user()->isManagingGuild()) {
    Redirect::to("/");
}

if (Auth::user()->getManageGuildPermissions() != 0) {
    Redirect::to("/");
}

$guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());

echo "<title>GuildCP &bull; {$guild->getName()} Members</title>";
echo "<div class='content'>";
if (isset($_POST["editSubmit"])) {
    // Make sure POST data hasn't been tampered with
    $stmt = Db::getPdo()->prepare("SELECT a.`id`, a.`username`, a.`battletag`, gp.`permissions` FROM `guilds_permissions` gp INNER JOIN `accounts` a ON (gp.`user_id` = a.`id`) WHERE gp.`guild_id` = :guild_id AND gp.`user_id` = :user_id");
    $stmt->execute([
        ":guild_id" => $guild->getId(),
        ":user_id" => $_POST['userId']
    ]);

    $permissions = @$_POST['permissions'];
    if ($permissions < -1 || $permissions > 2) {
        $permissions = null;
    }

    if ($stmt->rowCount() && $permissions != null) {
        if ($permissions != -1) {
            $stmt = Db::getPdo()->prepare("UPDATE `guilds_permissions` SET `permissions` = :permissions WHERE `guild_id` = :guild_id AND `user_id` = :user_id");
            $stmt->execute([
                ":permissions" => $permissions,
                ":guild_id"    => $guild->getId(),
                ":user_id"     => $_POST['userId']
            ]);

            echo Alert::success("User updated", "You have successfully changed the user's permissions.", "container");
        } else {
            $stmt = Db::getPdo()->prepare("DELETE FROM `guilds_permissions` WHERE `user_id` = :user_id AND `guild_id` = :guild_id");
            $stmt->execute([
                ":user_id"  => $_POST['userId'],
                ":guild_id" => $guild->getId()
            ]);
            echo Alert::danger("User kicked", "You have successfully removed the user's permissions.", "container");
        }
    } else {
        echo Alert::danger("User not found", "Failed to find the user specified. Please try again, or contact a site administrator if the issue persists.", "container");
    }
}

if (isset($_GET['edit'])) {
    $editId = @$_GET['edit'];

    $stmt = Db::getPdo()->prepare("SELECT a.`id`, a.`username`, a.`battletag`, gp.`permissions` FROM `guilds_permissions` gp INNER JOIN `accounts` a ON (gp.`user_id` = a.`id`) WHERE gp.`guild_id` = :guild_id AND gp.`user_id` = :user_id");
    $stmt->execute([
        ":guild_id" => $guild->getId(),
        ":user_id" => $editId
    ]);

    if ($stmt->rowCount()) {
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $row) {
            $box = new Box("Edit {$row['username']}/{$row['battletag']}");
            $box->setClass("container mt-5 mb-5");

            $select = "
            <select class='form-control' id='permissions' name='permissions'>
                <option value='0'>GM Access</option>
                <option value='1'>Officer</option>
                <option value='2'>Member</option>
                <option value='-1'>Kick</option>
            </select>
            ";

            $box->append(
                "<form action='' method='POST'>
                    <table class='table table-borderless table-striped text-light'>
                        <tr><th>Username</th><td>{$row['username']}</td></tr>
                        <tr><th>Battletag</th><td>{$row['battletag']}</td></tr>
                        <tr><th>Permissions</th><td>{$select}</td></tr>
                        <tr><th>Submit changes</th><td><button type='submit' name='editSubmit' class='btn btn-primary btn-block'>Submit</button></td></tr>
                    </table>
                    <input type='hidden' name='userId' value='{$row['id']}'>
                </form>"
            );

            $box->append(
                "<script>
                    $(function() {
                        $('#permissions').val('{$row['permissions']}');
                    });
                </script>"
            );

            echo $box->render();
        }
    } else {
        echo Alert::warning("User not found", "Failed to find the user specified. Please try again, or contact a site administrator if the issue persists.", "container");
    }
}

$box = new Box("Members");
$box->setClass("container mt-5 mb-5");

$box->append(
    "<div class='table-responsive'>
        <table class='table table-borderless table-striped text-light'>
            <input class='form-control' id='myInput' type='text' placeholder='Search . . '>
            <br>
            <thead>
                <tr>
                    <th scope='col'>Username</th>
                    <th scope='col'>Battletag</th>
                    <th scope='col'>Permissions</th>
                    <th scope='col'>Edit</th>
                </tr>
            </thead>
            <tbody id='myTable'>
    "
);

$stmt = Db::getPdo()->prepare("SELECT a.`id`, a.`username`, a.`battletag`, gp.`permissions` FROM `guilds_permissions` gp INNER JOIN `accounts` a ON (gp.`user_id` = a.`id`) WHERE gp.`guild_id` = :guild_id");
$stmt->execute([":guild_id" => $guild->getId()]);

$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $permission = GuildPermission::getRankName($row['permissions']);
    $box->append(
        "<tr>
            <td>{$row['username']}</td>
            <td>{$row['battletag']}</td>
            <td>{$permission}</td>
            <td><a href='?p=members&edit={$row['id']}' class='btn btn-primary'>Edit</a></td>
        </tr>"
    );
}

$box->append(
    "</tbody>
</table>
</div>"
);

echo $box->render();
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