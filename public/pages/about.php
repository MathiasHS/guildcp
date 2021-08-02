<?php namespace GuildCP;

if (!defined("IN_GCP")) {
    die();
}

echo "<title>GuildCP &bull; About us</title>";

$box = new Box();
$box->setClass("container mt-5 mb-5");
$box->append("
            <h1>About the creators</h1>
            <p>We are four students from the University of South-Eastern Norway. This website was created due to a school project in application development, in 2019.</p>
            <p>Because of interest in API usage, we decided to create a website for guild masters and guild officers. Initially, it was only meant for them to control their guild, but the project has been expanding, and it likely will in the future.</p>
            <p>A couple of our students, have experience from being guild officers and have been part of the hardcore raiding scene. This project was initially developed behind these ideas, but we have also reached out to several guild masters and officers in search for more information.</p>
            <div style='text-align: center'>
                <img src='/include/images/USN.jpg' alt='usn school' class ='mb-5 mt-3 img-fluid'>
            </div>"
            );

echo $box->render();

?>

