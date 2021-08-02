<?php
require_once "include/header.php";

$page = @$_GET['page'];

switch ($page) {
    case 'main':
        require_once 'pages/default.php';
        break;

    case 'about':
        require_once 'pages/about.php';
        break;

    case 'privacy-policy':
        require_once 'pages/privacy-policy.php';
        break;

    case '404':
        require_once 'pages/404.php';
        break;

    default:
        require_once 'pages/default.php';
        break;
}


require_once "include/footer.php";
