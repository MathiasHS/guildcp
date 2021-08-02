<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

if (!Auth::user()->isManagingGuild()) {
    Redirect::to("/");
}

if (Auth::user()->getManageGuildPermissions() != 0) {
    Redirect::to("/");
}

if (isset($_POST['editSubmit'])) {
    $visibility = @$_POST['visibility'];
    $canApply = @$_POST['canApply'];
    $information = @$_POST['information'];
    // Check POST data hasn't been tampered with
    if (!is_int($visibility) && $visibility >= -1 && $visibility <= 1) {
        if (!is_int($canApply) && $canApply >= 0 && $canApply <= 1) {
            if (strlen($information) < 512) {
                $stmt = Db::getPdo()->prepare("UPDATE `guilds_settings` SET `visibility` = :visibility, `can_apply` = :can_apply, `information` = :information WHERE `guild_id` = :guild_id");
    
                $stmt->execute([
                    ":visibility" => $visibility,
                    ":can_apply"  => $canApply,
                    ":information"  => $information,
                    ":guild_id"   => Auth::user()->getManageGuildId()
                ]);
    
                echo Alert::success("Settings updated", "You have successfully changed the settings.");
            } else {
                echo Alert::danger(
                    "Maximum character limit exceeded",
                    "You have exceeded the maximum information length of 512 characters.
                    <br>Consider enabling JavaScript for HTML5 form validation to prevent this issue.
                    If you believe the 512 character limit to be insufficient, please do not hesitate to contact a site administrator",
                    "container mt-5 mb-5"
                );
            }
        } else {
            echo Alert::danger("Failed to update settings", "Please try again. If the issue persists, please contact a site administrator.");
        }
    } else {
        echo Alert::danger("Failed to update settings", "Please try again. If the issue persists, please contact a site administrator.");
    }
}

$guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());

echo "<title>GuildCP &bull; {$guild->getName()} Settings</title>";
echo "<div class='content'>";

$box = new Box("Settings");
$box->setClass("container mt-5 mb-5");

if ($guild->getVisibility() == -1) {
    $visibility = "Hidden for everyone except moderators";
} else {
    $visibility = ($guild->getVisibility() == 0) ? ("Hidden for non-members") : ("Open to everyone");
}

$canApply = ($guild->getCanApply()) ? ("Yes") : ("No");
$canApplyInt = (int) $guild->getCanApply();

$box->append(
    "<p>Guild profile visibility: <strong>{$visibility}</strong></p>
     <p>Guild accepting applications: <strong>{$canApply}</strong></p>
     <form method='POST' action=''>
        <div class='form-group>
            <label for='visibility'>Guild profile visibility</label>
            <select class='form-control' id='visibility' name='visibility'>
                <option value='-1'>Hidden for everyone except moderators</option>
                <option value='0'>Hidden for non-members</option>
                <option value='1'>Open to everyone</option>
            </select>
            <label for='canapply'>Guild accepting applicants (profile must be public)</label>
            <select class='form-control' id='canapply' name='canApply'>
                <option value='0'>No</option>
                <option value='1'>Yes</option>
            </select>
            <label for='information'>Information to display on guild profile if public</label>
            <textarea maxlength='512' class='form-control' name='information' id='information'>{$guild->getInformation()}</textarea>
            <button type='submit' class='btn btn-primary mt-1 btn-block' name='editSubmit'>Submit Changes</button>
        </div>
    </form>"
);

echo $box->render();

echo "
<script>
    $(function() {
        $('#visibility').val('{$guild->getVisibility()}');
        $('#canapply').val('{$canApplyInt}');
        console.log('CanApply: {$canApplyInt}');
    });
</script>
";
echo "</div>";
