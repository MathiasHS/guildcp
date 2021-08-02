<?php namespace GuildCP;

use GuildCP\Blizzard\GuildEvent;

if (!defined("IN_GCP")) {
    die();
}

if (!Auth::user()->isManagingGuild()) {
    Redirect::to("/");
}

$guild = \GuildCP\Blizzard\Guild::byId(Auth::user()->getManageGuildId());
$access = false;
// Officers will have access
if (Auth::user()->getManageGuildPermissions() <= 1) {
    $access = true;
}

echo "<title>GuildCP &bull; {$guild->getName()} Attendance</title>";
echo "<div class='content'>";

$roster = $guild->getRosterMembers();
$characters = Auth::user()->getCharacters();
$rosterAccess = false;

if (isset($_GET['id'])) {
    $eventId = @$_GET['id'];

    if (GuildEvent::getEventById($eventId) != null) {
        $event = GuildEvent::getEventById($eventId);

        foreach ($roster as $rosterMember) {
            foreach ($characters as $character) {
                if ($character->getName() == $rosterMember && $character->getGuildName() == $guild->getName() && $character->getGuildRealm() == $guild->getRealm()) {
                    $rosterAccess = true;
                    break;
                }
            }
        }

        $action = @$_GET['action'];
        // Make sure event ID is correct
        if ($event->getGuild()->__toString() != $guild->__toString() || ($event->getRosterOnly() && !$rosterAccess) || (!$event->isAttending(Auth::user()) && !$access)) {
            echo Alert::danger("Wrong event specified or action already complete", "If the problem persists, do not hesitate to contact a site administrator.", "container mt-5 mb-5");
        } else {
            $box = new Box("{$event->getName()} - {$event->getDisplayTime()}", "", true);
            $box->setClass("container mt-5 mb-5");

            $box->append("<p>Description: {$event->getDescription()}</p>");
            $box->append("<p>Participant count: {$event->getParticipantCount()}</p>");
            
            $box->append("<p>Participants: ");
            $append = "";
            foreach ($event->getParticipants() as $participant) {
                $append .= "{$participant}, ";
            }
            $append = trim($append, ", ");

            $box->append("{$append}</p>");

            if ($access) {
                $box->append("<p>Auto-generated message for copy below:</p>");
                $box->append(
                    "<code>{$event->getName()} has been scheduled for {$event->getDisplayTime()}!<br>
                    {$event->getDescription()}<br>
                    Make sure to sign up on the Guild Control Panel! Thank you.
                    </code>"
                );
            }
            echo $box->render();
        }
    }
}

if (isset($_GET['view']) && $access) {
    if (Player::fromName($_GET['view']) != null) {
        $target = Player::fromName($_GET['view']);

        $stmt = Db::getPdo()->prepare(
            "SELECT ev.`attending` FROM `guilds_events_attendance` ev INNER JOIN `guilds_events` e ON (e.`id` = ev.`event_id`) WHERE ev.`user_id` = :user_id AND e.`guild_id` = :guild_id"
        );
        $stmt->execute([
            ":user_id" => $target->getId(),
            ":guild_id" => $guild->getId()
        ]);

        if ($stmt->rowCount()) {
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $yesCount = 0;
            $noCount = 0;
            foreach ($result as $row) {
                if ($row['attending'] == 1) {
                    $yesCount++;
                } else {
                    $noCount++;
                }
                $attendanceRate = @($yesCount / $noCount);
            }

            $box = new Box("{$target->getUsername()}/{$target->getBattleTag()} attendance statistics");
            $box->setClass("container mt-5 mb-5");

            $box->append(
                "<div class='table-responsive'>
                    <table class='table table-borderless table-striped text-light'>
                        <thead>
                            <tr>
                                <th scope='col'>Yes</th>
                                <th scope='col'>No</th>
                                <th scope='col'>Attendance Rate (Yes/No)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{$yesCount}</td>
                                <td>{$noCount}</td>
                                <td>{$attendanceRate}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>"
            );

            echo $box->render();
        } else {
            echo Alert::warning("No attendance statistics for {$target->getUsername()}", "The target specified has not signed for any guild events.", "container mt-5 mb-5");
        }
    } else {
        echo Alert::warning("The specified player could not be found", "If the problem persists, do not hesitate to contact a site administrator.", "container mt-5 mb-5");
    }
}

$events = GuildEvent::getUpcomingEventsByGuild($guild->getId());
$displayEvents = array();

// Only display upcoming events that the player has signed for
foreach ($events as $event) {
    if ($event->isAttending(Auth::user())) {
        $displayEvents[] = $event;
    }
}

if (count($displayEvents)) {
    $box = new Box("Upcoming events");
    $box->setClass("container mt-5 mb-5");

    $box->append(
        "<div class='table-responsive'>
            <table class='table table-borderless table-striped text-light'>
            <thead>
                <tr>
                    <th scope='col'>Name</th>
                    <th scope='col'>Time</th>
                    <th scope='col'>View</th>
                </tr>
            </thead>
            <tbody>"
    );

    foreach ($displayEvents as $event) {
        $box->append(
            "<tr>
                <td>{$event->getName()}</td>
                <td>{$event->getDisplayTime()}</td>
                <td><a href='?p=attendance&id={$event->getId()}' role='button' class='btn btn-primary btn-block'>View</a></td>
            </tr>"
        );
    }

    $box->append(
        "</tbody>
        </table>
    </div>"
    );

    echo $box->render();
} else {
    echo Alert::info("You have no upcoming events", "Upcoming events that you have signed for would normally be displayed here.", "container mt-5 mb-5");
}

$stmt = Db::getPdo()->prepare(
    "SELECT ev.`attending` FROM `guilds_events_attendance` ev INNER JOIN `guilds_events` e ON (e.`id` = ev.`event_id`) WHERE ev.`user_id` = :user_id AND e.`guild_id` = :guild_id"
);
$stmt->execute([
    ":user_id" => Auth::user()->getId(),
    ":guild_id" => $guild->getId()
]);

if ($stmt->rowCount()) {
    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $yesCount = 0;
    $noCount = 0;
    foreach ($result as $row) {
        if ($row['attending'] == 1) {
            $yesCount++;
        } else {
            $noCount++;
        }
        $attendanceRate = @($yesCount / $noCount);
    }

    $box = new Box("Your attendance statistics");
    $box->setClass("container mt-5 mb-5");

    $box->append(
        "<div class='table-responsive'>
            <table class='table table-borderless table-striped text-light'>
                <thead>
                    <tr>
                        <th scope='col'>Yes</th>
                        <th scope='col'>No</th>
                        <th scope='col'>Attendance Rate (Yes/No)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{$yesCount}</td>
                        <td>{$noCount}</td>
                        <td>{$attendanceRate}</td>
                    </tr>
                </tbody>
            </table>
        </div>"
    );

    echo $box->render();
}

if ($access) {
    $stmt = Db::getPdo()->prepare(
        "SELECT a.`username`, a.`battletag` FROM `guilds_permissions` gp INNER JOIN `accounts` a ON (gp.`user_id` = a.`id`) WHERE gp.`guild_id` = :guild_id ORDER BY gp.`permissions` DESC"
    );

    $stmt->execute([":guild_id" => $guild->getId()]);

    if ($stmt->rowCount()) {
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $yesCount = 0;
        $noCount = 0;

        $box = new Box("Guild attendance statistics");
        $box->setClass("container mt-5 mb-5");

        $box->append(
            "<div class='table-responsive'>
                <table class='table table-borderless table-striped text-light'>
                <input class='form-control' id='myInput' type='text' placeholder='Search . . '>
                <br>
                <thead>
                    <tr>
                        <th scope='col'>Username</th>
                        <th scope='col'>BattleTag</th>
                        <th scope='col'>View</th>
                    </tr>
                </thead>
                <tbody id='myTable'>"
        );
        foreach ($result as $row) {
            $box->append(
                "<tr>
                    <td>{$row['username']}</td>
                    <td>{$row['battletag']}</td>
                    <td><a href='?p=attendance&view={$row['username']}' role='button' class='btn btn-primary btn-block'>View</a></td>
                </tr>"
            );
        }
        $box->append(
            "</tbody>
        </table>
        </div>"
        );

        echo $box->render();
    }
}

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