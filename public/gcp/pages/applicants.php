<?php namespace GuildCP;

use GuildCP\Blizzard\GuildApplication;

if (!Auth::user()->isManagingGuild()) {
    Redirect::to("/");
}

// Officer or higher only, else redirect
if (Auth::user()->getManageGuildPermissions() >= 2) {
    Redirect::to("/");
}

$guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());

echo "<title>GuildCP &bull; {$guild->getName()} Applicants</title>";
echo "<div class='content'>";

if (isset($_GET['id'])) {
    $applicationId = @$_GET['id'];
    if (GuildApplication::getApplicationById($applicationId) != null) {
        $application = GuildApplication::getApplicationById($applicationId);
        if ($application->getGuild() == $guild) {
            $action = @$_GET['action'];
            if ($action == 'view') {
                $box = new Box("{$application->getPlayer()->getUsername()}");
                $box->setClass("container mt-5 mb-5");
    
                $appendButtons = "";
                if (Auth::user()->getManageGuildPermissions() == 0) {
                    $appendButtons =
                            "<tr>
                                <th scope='col'>Mark as accepted</th>
                                <td><a href='?p=applicants&id={$application->getId()}&action=accept' role='button' class='btn btn-primary btn-block'>Accept</a></td>
                            </tr>
                            <tr>
                                <th scope='col'>Mark as denied</th>
                                <td><a href='?p=applicants&id={$application->getId()}&action=deny' role='button' class='btn btn-primary btn-block'>Deny</a></td>
                            </tr>";
                }

                $box->append( "
                    <table class='table table-borderless table-striped text-light'>
                        <tbody>
                            <tr>
                                <th scope='col'>User</th>
                                <td>{$application->getPlayer()->getUsername()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Character</th>
                                <td>{$application->getCharacterName()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Age</th>
                                <td>{$application->getAge()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>BattleTag</th>
                                <td>{$application->getPlayer()->getBattleTag()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Email</th>
                                <td>{$application->getPlayer()->getEmail()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Question Addition</th>
                                <td>{$application->getQuestionAddition()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Question Attendance</th>
                                <td>{$application->getQuestionAttend()}</td>
                            </tr>
                            <tr>
                                <th scope='col'>Question Extra</th>
                                <td>{$application->getQuestionExtra()}</td>
                            </tr>
                            {$appendButtons}
                        </tbody>
                    </table>
                ");
    
                echo $box->render();
            } else {
                if (Auth::user()->getManageGuildPermissions() == 0) {
                    if ($action == "deny") {
                        $application->setState('DENIED');
                        echo Alert::success("{$application->getPlayer()->getUsername()} has been denied", "You have marked the application as denied.", "container mt-5 mb-5");
                    } else if ($action == "accept") {
                        $application->setState('ACCEPTED');
                        echo Alert::success("{$application->getPlayer()->getUsername()} has been accepted", "You have marked the application as accepted.", "container mt-5 mb-5");
                    }
                }
            }
        }
    }
}

$applications = GuildApplication::getAllApplicants($guild->getId());

$box = new Box("Pending applicants (".count($applications).")");
$box->setClass("container mt-5 mb-5");
if (count($applications)) {
    $box->append(
        "<div class='table-responsive'>
            <input class='form-control' id='myInput' type='text' placeholder='Search . . '>
            <br>
            <table class='table table-borderless table-striped text-light'>
                <thead>
                    <tr>
                        <th scope='col'>Name</th>
                        <th scope='col'>Character</th>
                        <th scope='col'>View</th>
                    </tr>
                </thead>
            <tbody id='myTable'>"
    );
    
    foreach ($applications as $application) {
        $box->append(
            "<tr>
                <td>{$application->getPlayer()->getUserName()}</td>
                <td>{$application->getCharacterName()}</td>
                <td><a href='?p=applicants&id={$application->getId()}&action=view' role='button' class='btn btn-primary btn-block'>View</a></td>
            </tr>"
        );
    }
    
    $box->append(
                "</tbody>
            </table>
        </div>"
    );
} else {
    $box->append(Alert::warning("No applications found", "Your guild has no pending applications."));
}

echo $box->render();

$applications = GuildApplication::getAllApplications($guild->getId());
$box = new Box("Archived applications (".count($applications).")");
$box->setClass("container mt-5 mb-5");
if (count($applications)) {
    $box->append(
        "<div class='table-responsive'>
                <table class='table table-borderless table-striped text-light'>
                <thead>
                    <tr>
                        <th scope='col'>Name</th>
                        <th scope='col'>State</th>
                        <th scope='col'>View</th>
                    </tr>
                </thead>
                <tbody>"
    );

    foreach ($applications as $application) {
        $box->append(
            "<tr>
                <td>{$application->getPlayer()->getUserName()}</td>
                <td>{$application->getState()}</td>
                <td><a href='?p=applicants&id={$application->getId()}&action=view' role='button' class='btn btn-primary btn-block'>View</a></td>
            </tr>"
        );
    }

    $box->append(
        "</tbody>
            </table>
        </div>"
    );
} else {
    $box->append(Alert::warning("No archived applications found", "Your guild has no archived applications."));
}

echo $box->render();
echo "</div>";

echo "
<script>
    $(document).ready(function(){
        $(' #myInput').on('keyup', function() {
            var value = $ (this).val().toLowerCase();
            $('#myTable tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>
";