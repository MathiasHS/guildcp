<?php namespace GuildCP;

require_once __DIR__ . "/../classes/db.class.php";

$response = array();

if (!isset($_REQUEST["e"])) {
    $response["status"] = 0;
} else {
    $stmt = Db::getPdo()->prepare("SELECT NULL FROM `accounts` WHERE `email` = :email");
    $stmt->execute([":email" => $_REQUEST["e"]]);

    if ($stmt->rowCount()) {
        $response["status"] = 1;
        $response["email_exists"] = 1;
    } else {
        $response["status"] = 1;
        $response["email_exists"] = 0;
    }
}

echo json_encode($response);
