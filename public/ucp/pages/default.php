<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

echo "<title>GuildCP &bull; UCP Dashboard</title>";
echo"<div class='content'>";
$box = new Box("User Control Panel", "");
$box->addClass("container mt-5 mb-5");

$user = Auth::user();

$box->append(
"Your username: <strong>{$user->getUsername()}</strong>, your user ID is: <strong>{$user->getId()}</strong><br>
Your e-mail address: <strong>{$user->getEmail()}</strong><br>
You registered on <strong>{$user->getJoinDate()}</strong><br>
You last logged in on: <strong>{$user->getLastLoginDate()}</strong><br>
Your last registered IP is: <strong>{$user->getRegisterIp()}</strong><br>
You initially registered your account from IP: <strong>{$user->getLastIp()}</strong><br>"
);

if ($user->hasBattleNet()) {
    $box->append("<br>Your account is associated to Battle.Net account: <strong>{$user->getBattleTag()}.</strong><br>");
}

if ($user->hasCharacters()) {
    $characterCount = count($user->getCharacters());
    $box->append("<br>You have <strong>{$characterCount}</strong> characters associated to your account.");
}
echo $box->render();
echo"</div>";
