<?php namespace GuildCP;

require_once __DIR__."/../classes/db.class.php";

$response = array();

if (!isset($_REQUEST["u"])) {
    $response["status"] = 0;
} else {
    $stmt = Db::getPdo()->prepare("SELECT NULL FROM `accounts` WHERE `username` = :username");
    $stmt->execute([":username" => $_REQUEST["u"]]);

    if ($stmt->rowCount()) {
        $response["status"] = 1;
        $response["username_exists"] = 1;
    } else {
        $response["status"] = 1;
        $response["username_exists"] = 0;
    }
}

echo json_encode($response);
