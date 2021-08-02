<?php namespace GuildCP;

require_once "../include/header.php";

echo "<title>GuildCP &bull; Register</title>";

$box = new \GuildCP\Box("Register here", "", true);

if (isset($_POST['register'])) {
    $credentials = [
        "username" => $_POST["username"],
        "password" => $_POST["password"],
        "email"    => $_POST["email"]
    ];
    try {
        if (Auth::register($credentials)) {
            Redirect::to("/ucp");
        }
    } catch (\InvalidArgumentException $e) {
        $box->append(Alert::danger("Registration failed", $e->getMessage()));
    }
}

$box->setClass("container mt-5 mb-5");
$box->append(
    "
    <div id='login-wrapper' class='container justify-content-center'>
        <form method='POST' action=''>
            <div class='form-group'>
                <label for='username'>Username</label>
                <p id='pUsername' class='text-danger' style='display:none;'>This username is already taken</p>
                <input type='text' class='form-control' id='regName' name='username' aria-describedby='emailHelp' required placeholder='Username'>
            </div>
            <div class='form-group'>
                <label for='email'>Email</label>
                <p id='pEmail' class='text-danger' style='display:none;'>This e-mail is already taken</p>
                <input type='email' id='regEmail' class='form-control' name='email' required placeholder='Email'>
            </div>
            <div class='form-group'>
                <label for='password'>Password</label>
                <div class='input-group-append' id='show_password'>
                    <input type='password' class='form-control' name='password' required placeholder='Password'>
                    <div class='input-group-text'>
                        <a href=''><i class='fa fa-eye-slash' aria-hidden='true'></i></a>
                    </div>
                </div>
            </div>

            <button id='regSubmit' name='register' type='submit' class='btn btn-primary btn-block'>Register</button>
        </form>
    </div>"
);

echo $box->render();
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

    /**
     * Following code was only meant to examplify AJAX usage (task requirement), it will be removed
     * in the future and we will likely use AJAX other locations anyway. This poses as a potential
     * security flaw, as we don't really want people to be able to check if a username or e-mail is taken
     * unless necessary in case of exploits. For now, it does not matter though.
     */
    $("#regName").focusout(function() {
        console.log("regName focus out");
        var curName = $("#regName").val();
        $.ajax({
            url: "/../include/ajax/username_availability.php?u=" + curName,
            dataType: 'json',
            success: function(result) {
                console.log(result);
                if (result.username_exists) {
                    console.log("This username is already taken!");
                    $("#pUsername").show();
                    $("#regSubmit").attr("disabled", "disabled");
                } else {
                    $("#pUsername").hide();
                    if (!$("#pEmail").is(":visible")) {
                        $("#regSubmit").removeAttr("disabled");
                    }
                }
            }
        });
    });

    $("#regEmail").focusout(function() {
        console.log("RegEmail focus out");
        var curEmail = $("#regEmail").val();
        $.ajax({
            url: "/../include/ajax/email_availability.php?e=" + curEmail,
            dataType: 'json',
            success: function(result) {
                console.log(result);
                if (result.email_exists) {
                    console.log("This email is already taken!");
                    $("#pEmail").show();
                    $("#regSubmit").attr("disabled", "disabled");
                } else {
                    $("#pEmail").hide();
                    if (!$("#pUsername").is(":visible")) {
                        $("#regSubmit").removeAttr("disabled");
                    }
                }
            }
        });
    });
</script>

<?php
require_once "../include/footer.php";
