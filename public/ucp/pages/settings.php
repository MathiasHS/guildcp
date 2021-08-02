<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

if (isset($_GET["toggle"])) {
    $toggle = @$_GET["toggle"];

    switch ($toggle) {
        case 'visible':
            Auth::user()->setVisibility(!Auth::user()->getVisibility());
            Redirect::to("../settings");
            break;
        
        case 'showEmail':
            Auth::user()->setShowEmail(!Auth::user()->getShowEmail());
            Redirect::to("../settings");
            break;

        case 'showBattleTag':
            Auth::user()->setShowBattleTag(!Auth::user()->getShowBattleTag());
            Redirect::to("../settings");
            break;

        default:
            Redirect::too("../settings");
            break;
    }
}

echo "<title>GuildCP &bull; UCP Settings</title>";
echo"<div class='content'>";
$box = new Box("Settings", "");
$box->setClass("container mt-5 mb-5");

$user = Auth::user();
if ($user->hasBattleNet()) {
    $url = "https://region.battle.net/oauth/authorize";
    $url = str_replace("region", Auth::user()->getRegion(), $url);
    $url .= "?client_id=" . Config::get("blizzard.client.id") . "&scope=wow.profile";

    $ip = new Ip("get");
    $state = base64_encode($ip->getIp());
    $url .= "&state={$state}&redirect_uri=" . Config::get("blizzard.redirect.uri") . "&response_type=code";
    $visible = ($user->getVisibility()) ? ("Your user profile is <strong>visible</strong> to others.") : ("Your user profile is <strong>not visible</strong> to others.");
    $showEmail = ($user->getShowEmail()) ? ("Your e-mail is <strong>visible</strong> to others.") : ("Your e-mail is <strong>not visible</strong> to others.");
    $showBtag = ($user->getShowBattleTag()) ? ("Your battle-tag is <strong>visible</strong> to others.") : ("Your battle-tag is <strong>not visible</strong> to others.");
    $box->append("
    <div class='text-center'>
        <h4 class='text-light'>Synchronization info</h4>
        <p class='text-light'>You last synchronized your account on <strong>{$user->getLastSyncDate()}</strong>.</p>
        <p class='text-light'>You have <strong>{$user->getCharacterCount()}</strong> characters associated to your account.</p>
        <p class='text-light'>You have <strong>{$user->getGuildPermissionsCount()}</strong> guilds associated to your account.</p>
        <a class='btn btn-primary' href='{$url}' role='button'>Sync Battle.net account</a>
    </div>
    <div class='text-center mt-3 mb-1'>
        <h4 class='text-light'>Privacy settings</h4>
        <p class='text-light'>{$visible}</p>
        <p class='text-light'>{$showEmail}</p>
        <p class='text-light'>{$showBtag}</p>
        <a class='btn btn-primary' href='settings/visible' role='button'>Toggle user profile visibility</a>
        <a class='btn btn-primary' href='settings/showEmail' role='button'>Toggle e-mail visibility</a>
        <a class='btn btn-primary' href='settings/showBattleTag' role='button'>Toggle battle-tag visibility</a>
    </div>
    ");
} else {
    $box->append("
    <form action='' method='POST'>
        <div class='form-group'>
            <div class='container mx-auto justify-content-center text-center'>
                <label for='region'>Select region</label>
                <select class='form-control' name='region'>
                    <option value='eu'>EU</option>
                    <option value='us'>US</option>
                    <option value='apac'>Korea/Taiwan</option>
                    <option value='cn'>China</option>
                </select>
                <button class='btn btn-primary btn-block mt-2' type='submit' name='submit'>Link Battle.net account</button>
            </div>
        </div>
      </form>
    ");
}

if (isset($_POST["submit"]) && isset($_POST['region'])) {
    $url = "https://region.battle.net/oauth/authorize";
    $url = str_replace("region", $_POST["region"], $url);
    $url .= "?client_id=" . Config::get("blizzard.client.id") . "&scope=wow.profile";
    
    $ip = new Ip("get");
    $state = base64_encode($ip->getIp());
    $url .= "&state={$state}&redirect_uri=" . Config::get("blizzard.redirect.uri") . "&response_type=code";
    
    Auth::user()->setRegion($_POST['region']);

    $upperRegion = strtoupper($_POST["region"]);
    $box->append("
    <div class='text-center mt-1 mb-1'>
        <p class='text-light text-center'>Confirm synchronization for region: {$upperRegion}</p>
        <a class='btn btn-primary' href='{$url}' role='button'>Confirm Battle.Net Linking</a>
    </div>
    ");
}
echo $box->render();
echo"</div>";
