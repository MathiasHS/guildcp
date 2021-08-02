<?php namespace GuildCP;

require_once "../include/header.php";

echo "<title>GuildCP &bull; Login</title>";

$box = new \GuildCP\Box("Login here", "", true);

if (isset($_POST["submit"])) {
    if (!Auth::isBanned()) {
        try {
            if (Auth::attempt(["username" => $_POST["username"], "password" => $_POST["password"], "keep_logged_in" => isset($_POST["keep_logged_in"])])) {
                Redirect::to("/ucp");
            } else {
                $box->append(Alert::danger("Login failed!", "Invalid username and/or password provided."));
            }
        } catch (AuthException $e) {
            $box->append(Alert::danger("Login failed", $e->getMessage()));
        }
    } else {
        $box->append(Alert::danger("Banned", "You have been banned from the website for too many failed login attempts. Please try again in a few minutes."));
    }
}

echo "<title>GuildCP &bull; Login</title>";

?>
<?php
echo "<div class='content'>";
$box->setClass("container mt-5 mb-5");
$box->append(
    "
    <div id='login-wrapper' class='container justify-content-center'>
        <form method='POST' action=''>
            <div class='form-group'>
                <label for='username'>Enter your username:</label>
                <input type='text' class='form-control' name='username' aria-describedby='emailHelp' required placeholder='Enter username'>
            </div>
            <div class='form-group'>
                <label for='password'>Enter your password:</label>
                <div class='input-group-append' id='show_password'>
                    <input type='password' class='form-control' name='password' required placeholder='Password'>
                    <div class='input-group-text'>
                        <a href=''><i class='fa fa-eye-slash' aria-hidden='true'></i></a>
                    </div>
                </div>
            </div>
            <div class='form-group'>
                <input class='form-control' type='checkbox' name='keep_logged_in'>Stay logged in</input>
            </div>
            <button name='submit' type='submit' class='btn btn-primary btn-block'>Login</button>
        </form>
        <div class='mt-3'>
            <p>You don't have an account yet? Register here:</p>
            <a href='register'><button class='btn btn-primary btn-block'>Register</button></a>
        </div>
    </div>"
);

echo $box->render();
echo "</div>";
?>
<script>
    $(document).ready(function() {
        $('#show_password a').on('click', function(e) {
            e.preventDefault();
            if ($('#show_password input').attr('type') == 'text') {
                $('#show_password input').attr('type', 'password');
                $('show_password i').removeClass('fa-eye');
                $('show_password i').addClass('fa-eye-slash');
            } else {
                $('#show_password input').attr('type', 'text');
                $('show_password i').removeClass('fa-eye-slash');
                $('show_password i').addClass('fa-eye');
            }
        });
    });
</script>
<?php

require_once "../include/footer.php";
