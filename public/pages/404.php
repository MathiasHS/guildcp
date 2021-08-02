<?php if (!defined("IN_GCP")) {
    die();
}
?>
<title>GuildCP &bull; 404</title>

<div class="container mt-1">
    <?php

    $back = "<br><br><a href='javascript:history.back()' class='btn btn-dark'><span>Go back</span></a>";
    $box = new \GuildCP\Box("404 - Page Not Found");
    $box->setClass("container mt-5 mb-5");

    $box->append(\GuildCP\Alert::danger("404 - Page not found", "We failed to find the page you specified.{$back}"));
    echo $box->render();
    ?>
</div>