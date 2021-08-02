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

echo "<title>GuildCP &bull; {$guild->getName()} Events</title>";
echo "<div class='content'>";

if (isset($_POST['editSubmit'])) {
    try {
        $event = new GuildEvent($guild, $_POST['name'], $_POST['description'], $_POST['time'], $_POST['rosterOnly']);
    } catch (\InvalidArgumentException $e) {
        $event = null;
        echo Alert::danger("Invalid event argument specified", $e->getMessage(), "container mt-5 mb-5");
    }

    if ($event != null) {
        $event->save();
        echo Alert::success("{$event->getName()} has been saved", "You have successfully scheduled an event for {$event->getTime()}", "container mt-5 mb-5");
    }
}

$roster = $guild->getRosterMembers();
$characters = Auth::user()->getCharacters();
$rosterAccess = false;

foreach ($roster as $rosterMember) {
    foreach ($characters as $character) {
        if ($character->getName() == $rosterMember && $character->getGuildName() == $guild->getName() && $character->getGuildRealm() == $guild->getRealm()) {
            $rosterAccess = true;
            break;
        }
    }
}

if (isset($_GET['id'])) {
    $eventId = @$_GET['id'];

    if (GuildEvent::getEventById($eventId) != null) {
        $event = GuildEvent::getEventById($eventId);

        $action = @$_GET['action'];
        // Make sure event ID is correct
        if ($event->getGuild()->__toString() != $guild->__toString() || ($event->getRosterOnly() && !$rosterAccess) || ($event->isAttending(Auth::user()))) {
            echo Alert::danger(
                "Wrong event specified or action already complete",
                "The wrong event has been specified, or an error has occcured. This could be caused by refreshing the page after joining an event
                (signing for an event you already are signed for). 
                If the problem persists, do not hesitate to contact a site administrator.",
                "container mt-5 mb-5"
            );
        } else {
            if ($action == 'view') {
                $box = new Box("{$event->getName()} - {$event->getDisplayTime()}", "", true);
                $box->setClass("container mt-5 mb-5");

                $box->append("<p>{$event->getDescription()}</p>");

                if (!$event->isAttending(Auth::user())) {
                    $box->append("<p class='text-warning'>You have not signed for this event, or declined the invitation.</p>");
                }

                $delete = (Auth::user()->getManageGuildPermissions() == 0) ? ("<a href='?p=events&id={$event->getId()}&action=delete' role='button' class='btn btn-primary'>Delete</a>") : ("");

                $box->append(
                    "<a href='?p=events&id={$event->getId()}&action=join' role='button' class='btn btn-primary'>Sign</a>
                    <a href='?p=events&id={$event->getId()}&action=decline' role='button' class='btn btn-primary'>Decline</a>
                    {$delete}"
                );

                echo $box->render();
            } else if ($action == 'join') {
                $stmt = Db::getPdo()->prepare(
                    "INSERT INTO `guilds_events_attendance`(`event_id`, `user_id`, `attending`)
                    VALUES (:event_id, :user_id, 1) 
                    ON DUPLICATE KEY UPDATE `attending` = 1;"
                );

                $stmt->execute([
                    ":event_id" => $event->getId(),
                    ":user_id"  => Auth::user()->getId()
                ]);

                echo Alert::success("You have successfully signed for {$event->getName()}", "You can check the attendance tab for upcoming events.", "container mt-5 mb-5");
            } else if ($action == 'decline') {
                $stmt = Db::getPdo()->prepare(
                    "INSERT INTO `guilds_events_attendance`(`event_id`, `user_id`, `attending`)
                    VALUES (:event_id, :user_id, 0) 
                    ON DUPLICATE KEY UPDATE `attending` = 0;"
                );

                $stmt->execute([
                    ":event_id" => $event->getId(),
                    ":user_id"  => Auth::user()->getId()
                ]);

                echo Alert::success("You have declined to sign for {$event->getName()}", "You can check the attendance tab for upcoming events.", "container mt-5 mb-5");
            } else if ($action == 'delete') {
                if (Auth::user()->getManageGuildPermissions() == 0) {
                    $stmt = Db::getPdo()->prepare("DELETE FROM `guilds_events` WHERE `id` = :id");

                    $stmt->execute([":id" => $event->getId()]);

                    echo Alert::success("You have successfully deleted {$event->getName()}", "The event has been deleted from the database.", "container mt-5 mb-5");
                }
            } else {
                echo Alert::warning("Wrong action specified", "An error has occured. If the problem persists, please do not hesitate to contact a site administrator.", "container mt-5 mb-5");
            }
        }
    }
}

if ($rosterAccess) {
    $events = GuildEvent::getAllEventsByGuild($guild->getId(), 1);
    $displayEvents = array();

    // Only display events the player isn't attending
    foreach ($events as $event) {
        if (!$event->isAttending(Auth::user())) {
            $displayEvents[] = $event;
        }
    }
    
    $box = new Box("Roster Events");
    $box->setClass("container mt-5 mb-5");
    if (count($displayEvents)) {
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
                    <td><a href='?p=events&id={$event->getId()}&action=view' role='button' class='btn btn-primary btn-block'>View</a></td>
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
        $box->append(Alert::warning("No unanswered upcoming events found", "There are no upcoming events, or you have already replied to them. Check out the attendance tab."));
        echo $box->render();
    }
}

$events = GuildEvent::getAllEventsByGuild($guild->getId(), 0);
$displayEvents = array();

// Only display events the player isn't attending
foreach ($events as $event) {
    if (!$event->isAttending(Auth::user())) {
        $displayEvents[] = $event;
    }
}

$box = new Box("Non-Roster Events");
$box->setClass("container mt-5 mb-5");
if (count($displayEvents)) {
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
                <td><a href='?p=events&id={$event->getId()}&action=view' role='button' class='btn btn-primary btn-block'>View</a></td>
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
    $box->append(Alert::warning("No unanswered upcoming events found", "There are no upcoming events, or you have already replied to them. Check out the attendance tab."));
    echo $box->render();
}

if ($access) {
    $box = new Box("Add event");
    $box->setClass("container mt-5 mb-5");

    $date = new \DateTime();
    $dateTimeMin = $date->format("Y-m-d H:i");
    $dateTimeMin = str_replace(" ", "T", $dateTimeMin);

    $box->append(
        "<form method='POST' action=''>
        <div class='form-group>
            <label for='rosterOnly'>Roster only?</label>
            <select class='form-control' id='rosterOnly' name='rosterOnly'>
                <option value='1'>Yes</option>
                <option value='0'>No</option>
            </select>
            <label for='eventName'>The name of the event</label>
            <input type='text' class='form-control' id='eventName' placeholder='Event name' name='name' maxlength='32' required>
            <label for='eventDescription'>The description of the event</label>
            <textarea maxlength='255' class='form-control' name='description'>Event description</textarea>
            <label for='eventTime'>The date of the event</label>
            <input type='datetime-local' class='form-control' id='eventTime' name='time' min='{$dateTimeMin}' required>
            <button type='submit' class='btn btn-primary mt-1 btn-block' name='editSubmit'>Submit Event</button>
        </div>
    </form>"
    );

    echo $box->render();
}

echo "</div>";
