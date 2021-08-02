<?php namespace GuildCP;

use GuildCP\Blizzard\Guild;
use GuildCP\Blizzard\GuildApplication;

require_once "include/header.php";

$guild = null;

if (isset($_GET['data'])) {
    $data = explode(";", $_GET["data"]);

    $guild = Guild::byId($data[0]);
    // Null the object if the guild does not take applications
    if ($guild == null || !$guild->getCanApply()) {
        $guild = null;
    }
}

if ($guild == null) {
    echo"<title>GuildCP &bull; Apply For Guild - Guild Not Found</title>";
    echo "<div class='content'>";

    $box = new Box();
    $box->setClass("container mt-5 mb-5");
    $box->append(Alert::warning("{$data[0]}-{$data[1]}-{$data[2]}-{$data[3]} not found", "The guild does not exist, does not take applications, or the guild was not found in the database."));

    echo $box->render();
    echo "</div>";
    require_once "include/footer.php";
    return;
}

echo "<title>GuildCP &bull; Apply For Guild - {$guild->getName()}</title>";

echo "
<div class='text-white' style='background-image:url(/include/images/11136.jpg); background-size: cover; background-position: center;'>
    <div class='container justify-content-center'>
        <div class='pt-5 pb-5'>
            <h1 class='text-center'>{$guild->getName()}</h1>
            <p class='text-center'>Write an application to {$guild->getName()}</p>
        </div>
    </div>
</div>";

echo "<div class='content'>";

if (isset($_POST['editSubmit'])) {
    try {
        $application = new GuildApplication($guild, Auth::user(), $_POST["charName"], $_POST["age"], $_POST["questionAddition"], $_POST["questionAttend"], $_POST["questionExtra"]);
    } catch (\InvalidArgumentException $e) {
        $application = null;
        echo Alert::danger("Invalid argument", $e->getMessage(), "container mt-5 mb-5");
    }

    if ($application != null) {
        $application->save();
        echo Alert::success("Your application to {$guild->getName()} has been sent", "The Guild Master or officers will get back to you in game or via email", "container mt-5 mb-5");
    }
}

$box = new Box();
$box->setClass("container mt-5 mb-5");

$characters = Auth::user()->getCharacters();

$select = "<select class='form-control' id='characterName' name='charName'>";

foreach ($characters as $character) {
    $select .= "<option value='{$character->getName()}'>{$character->getName()}-{$character->getRealm()} ({$character->getLevel()})</option>";
}

$select .= "</select>";

$box->append(
    "<div class='text-white bg-dark rounded mt-3'>
    <form class='pb-2 mb-3' action='' method='POST'>
        <div class='form-group'>
            <label for='characterName'>Character name:</label>
            {$select}
        </div>
        <div class='form-group'>
            <label for='age'>How old are you?</label>
            <input type='number' class='form-control' id='age' name='age' min='13' max='120' required placeholder='Your age (13-120)'>
        </div>
        <div class='form-group'>
            <label for='roster'>Why are you a good addition to our roster?</label>
            <textarea class='form-control' id='roster' name='questionAddition' maxlength='512' required>Why am I?</textarea>
        </div>
        <div class='form-group'>
            <label for='raid'>Will you be able to attend all our raid days?</label>
            <textarea class='form-control' id='raid' name='questionAttend' maxlength='256' required>Will I?</textarea>
        </div>
        <div class='form-group'>
            <label for='raid'>Anything else you want to add?</label>
            <textarea class='form-control' id='extra' name='questionExtra' maxlength='256'></textarea>
        </div>
        <div class='form-group'>
            <label for='submit'>When submitting, your battle.net tag, characters and your e-mail will be visible to guild officers, even if your profile settings are different.</label>
            <button type='submit' id='submit' name='editSubmit' class='btn btn-primary btn-block'>Submit</button>
        </div>
        <input type='hidden' name='guildId' value='{$guild->getId()}'>
    </form>
</div>"
);

echo $box->render();
echo "</div>";
require_once "include/footer.php";

