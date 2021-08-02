<?php namespace GuildCP;

require_once __DIR__."/../config.php";
require_once __DIR__."/db.class.php";
require_once __DIR__."/ip.class.php";
require_once __DIR__."/user.class.php";
require_once __DIR__."/random.class.php";
require_once __DIR__."/authexception.class.php";

/**
 * Class for authenciation of users
 */
class Auth
{
    private static $user = null;

    /**
     * Check whether or not the specified email and or username is registered to an account
     * @param string $username The username to check
     * @param string $email The email to check
     * @return boolean True if available, false if not
     */
    public static function isAvailable($username, $email)
    {
        $query_check_availability = "SELECT NULL FROM `accounts` WHERE `username` = :username OR `email` = :email";
        $stmt = Db::getPdo()->prepare($query_check_availability);

        $stmt->execute(
            [":username" => $username,
            ":email"    => $email]
        );

        if ($stmt->rowCount()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Attempt to register a user and then log them in
     *
     * @param Array $credentials An array containing username, password and e-mail
     * For eksempel: $credentials = [
     * "username"   => "navn",
     * "password"   => "password",
     * "email"       => "ola@norge.no
     * ];
     * @return integer User ID of the newly registered user
     */
    public static function register(array $credentials)
    {
        if (!array_key_exists("username", $credentials)) {
            throw new \InvalidArgumentException("A username must be provided!");
        }

        if (!array_key_exists("password", $credentials)) {
            throw new \InvalidArgumentException("A password must be provided!");
        }

        if (!array_key_exists("email", $credentials)) {
            throw new \InvalidArgumentException("An e-mail has to be provided!");
        }

        if (!filter_var($credentials["email"], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("The e-mail has to be a valid e-mail address!");
        }

        if (strlen($credentials["username"]) < 2 || strlen($credentials["username"]) > 31) {
            throw new \InvalidArgumentException("Username needs to be between 2 and 31 characters");
        }

        if (strlen($credentials["email"]) < 2 || strlen($credentials["email"]) > 254) {
            throw new \InvalidArgumentException("Email needs to be between 2 and 254 characters");
        }

        if (strlen($credentials["password"]) < 6 || strlen($credentials[ "password"]) > 175) {
            throw new \InvalidArgumentException("Password needs to be between 6 and 175 characters");
        }

        if (!self::isAvailable($credentials["username"], $credentials["email"])) {
            throw new \InvalidArgumentException("An account has already been registered to the specified username or e-mail address.");
        }

        $ip = (new Ip("get"))->getIp();

        $query_register_user = "
        INSERT INTO 
            `accounts`(`username`, `password`, `email`, `register_ip`, `last_ip`, `joined_date`, `last_login_date`) 
        VALUES 
            (:username, :password, :email, INET_ATON(:register_ip), INET_ATON(:last_ip), NOW(), NOW())";
        $hash = password_hash($credentials["password"], Config::get("password.algo"), ["cost" => Config::get("password.cost")]);

        $db = Db::getPdo();
        $statement = $db->prepare($query_register_user);
        $statement->execute([
            ":username"     => $credentials["username"],
            ":password"     => $hash,
            ":email"         => $credentials["email"],
            ":register_ip"   => $ip,
            ":last_ip"       => $ip
        ]);
        
        $return_id = $db->lastInsertId();

        // Insert into account settings with default settings
        $statement = $db->prepare("INSERT INTO `accounts_settings`(`user_id`) VALUES (:user_id)");
        $statement->execute([":user_id" => $return_id]);

        if (!self::attempt(["username" => $credentials["username"], "password" => $credentials["password"], "keep_logged_in" => 0])) {
            return -1;
        } else {
            return $return_id;
        }
    }


    /**
     * Attempt to authenticate the user using the provided username and password
     *
     * @param Array $credentials An array containing the username, password and key keep_logged_in to create a persistent session
     *
     * e.g. $credentials = [
     *      "username" 			=> "name",
     *      "password" 			=> "secret",
     *      "keep_logged_in" 	=> true
     * ];
     *
     * An array is used to prevent leaking the password to logs in case of an exception.
     *
     * @return boolean True if the attempt was successful, otherwise false
     */
    public static function attempt(array $credentials)
    {
        self::$user = null;

        if (!array_key_exists("username", $credentials)) {
            throw new \InvalidArgumentException("The username must be provided");
        }

        if (!array_key_exists("password", $credentials)) {
            throw new \InvalidArgumentException("The password must be provided");
        }

        if ($user_id = self::validate($credentials)) {
            $keep_logged_in = array_key_exists("keep_logged_in", $credentials) ? (bool)$credentials["keep_logged_in"] : false;

            self::$user = new User($user_id);
            
            // Create session
            $session_id = Random::generateSalt(64);

            $create_session_q =
                "	INSERT INTO
                `auth_sessions` (`session_id`, `user_id`, `ip`, `user_agent`, `started`, `updated`)
            VALUES
                (:session_id, :user_id, INET_ATON(:ip), :user_agent, NOW(), NOW())";

            $stmt = Db::getPdo()->prepare($create_session_q);
            $stmt->execute([
                ":session_id"     => $session_id,
                ":user_id"         => $user_id,
                ":ip"            => (new Ip("get"))->getIp(),
                ":user_agent"    => md5($_SERVER["HTTP_USER_AGENT"])
            ]);

            setCookie(
                Config::get("cookie.session"),
                $session_id,
                ($keep_logged_in ? time() + 3600 * 24 * 21 : 0),
                Config::get("cookie.session.path"),
                Config::get("cookie.session.domain"),
                "",
                true
            );
            if (Config::get("site.development")) {
                var_dump(Config::get("cookie.session.domain"));
            }

            return true;
        }

        return false;
    }

    /**
     * Attempt to authenticate the user using the provided username and password without actually logging in the user
     *
     * @param Array $credentials An array containing the username, password and key keep_logged_in to create a persistent session
     *
     * e.g. $credentials = [
     * 		"username" 			=> "name",
     * 		"password" 			=> "secret"
     * ];
     *
     * @return integer The user ID of the user, or false if the credentials were invalid
     */
    public static function validate(array $credentials)
    {
        if (!array_key_exists("username", $credentials)) {
            throw new \InvalidArgumentException("The username must be provided");
        }

        if (!array_key_exists("password", $credentials)) {
            throw new \InvalidArgumentException("The password must be provided");
        }

        if (self::isBanned()) {
            throw new AuthException('Too many failed login attempts. Please try again in five minutes.');
        }

        $get_user_q = "SELECT `id`, `password` FROM `accounts` WHERE `username` = :username";
        $stmt = Db::getPdo()->prepare($get_user_q);
        $stmt->execute([":username" => $credentials["username"]]);
        $user = $stmt->fetchObject();

        if ($user !== false) {
            $hash = $user->password;

            if (password_verify($credentials["password"], $hash)) {
                // Update hash
                if (password_needs_rehash($hash, Config::get("password.algo"), ["cost" => Config::get("password.cost")])) {
                    $update_hash_q = "UPDATE `accounts` SET `password` = :hash WHERE `id` = :user_id";

                    $stmt = Db::getPdo()->prepare($update_hash_q);
                    $stmt->execute([
                        ":hash" => password_hash($credentials["password"], Config::get("password.algo"), ["cost" => Config::get("password.cost")]),
                        ":user_id" => $user->id
                    ]);
                }

                self::logAttempt($user->id, true);
                return $user->id;
            }
        }

        self::logAttempt(null, false);
        return false;
    }

    /**
     * Check if a valid session exists, i.e. the user is logged in
     * @return boolean True if a valid session exists, otherwise false
     */
    public static function check()
    {
        if (self ::$user !== null) {
            return true;
        }

        if (isset($_COOKIE[Config::get("cookie.session")])) {
            $check_session_q =
                "SELECT
                `id`,
                `user_id`
            FROM
                `auth_sessions`
            WHERE
                (`session_id` = :session_id AND `user_agent` = :user_agent)";

            $stmt = Db::getPdo()->prepare($check_session_q);
            $stmt->execute([":session_id" => @$_COOKIE[Config::get("cookie.session")], ":user_agent" => md5($_SERVER["HTTP_USER_AGENT"])]);
            $session = $stmt->fetchObject();

            if ($session !== false) {
                $update_session_q = "UPDATE `auth_sessions` SET `updated` = NOW() WHERE (`id` = :id)";

                $stmt = Db::getPdo()->prepare($update_session_q);
                $stmt->execute([":id" => $session->id]);

                self::$user = new User($session->user_id);
                return true;
            }
        }
        return false;
    }

    /**
     * Get a User object representing the user associated to the active session
     * @return User A User object representing associated to the active session
     */
    public static function user()
    {
        if (self ::$user !== null) {
            return self::$user;
        } else {
            throw new \Exception("Function Auth::user() cannot be called when there is no valid active session.");
        }
    }

    /**
     * Get the ID of the logged in user, or throw an exception if there is no active session
     * @return Int The User ID of the User
     */
    public static function id()
    {
        if (self ::$user !== null) {
            return self::$user->getId();
        } else {
            throw new \Exception("Function Auth::id() cannot be called when there is no valid active session.");
        }
    }

    /**
     * End the currently active session, i.e. log out the user
     * @return void
     */
    public static function logout()
    {
        if (isset($_COOKIE[Config::get("cookie.session")])) {
            $delete_session_q = "DELETE FROM `auth_sessions` WHERE `session_id` = :session_id";

            $stmt = Db::getPdo()->prepare($delete_session_q);
            $stmt->execute([":session_id" => @$_COOKIE[Config::get("cookie.session")]]);

            // Delete cookie if possible
            @setcookie(Config::get("cookie.session"), "", 0, Config::get("cookie.session.path"), Config::get("cookie.session.domain"), true, true);
        }
    }

    /**
     * Checks if the user's IP is currently banned from the website for too many failed login attempts
     * @return boolean True if the user's IP is banned, otherwise false
     */
    public static function isBanned()
    {
        $ip = new Ip("get");

        $check_ban_q = "SELECT NULL FROM `auth_bans` WHERE `ip` = INET_ATON(:ip) AND `end` > NOW()";
        $stmt = Db::getPdo()->prepare($check_ban_q);
        $stmt->execute([":ip" => $ip->getIp()]);
        $ban = $stmt->fetchObject();
        if ($ban !== false) {
            return true;
        } else {
            $get_attempts_q = "SELECT COUNT(*) AS `count` FROM `auth_logins` WHERE `ip` = INET_ATON(:ip) AND `success` = 0 AND TIMEDIFF(NOW(), `time`) < '00:05:00'";
            $stmt = Db::getPdo()->prepare($get_attempts_q);
            $stmt->execute([":ip" => $ip->getIp()]);
            $attempts = $stmt->fetchObject();
            
            if ($attempts !== false) {
                if ($attempts->count > 5) {
                    $add_ban_q = "INSERT INTO `auth_bans` (`start`, `end`, `ip`) VALUES (NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE), INET_ATON(:ip))";
                    $stmt = Db::getPdo()->prepare($add_ban_q);
                    $stmt->execute([":ip" => $ip->getIp()]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Log an authentication attempt in the database
     * @param int $user_id The ID of the user attempting to authenticate
     * @param boolean $success 	True if the login was successful, otherwise false
     * @return void
     */
    private static function logAttempt($user_id, $success)
    {
        $log_attempt_q =
            "INSERT INTO
				`auth_logins` (`time`, `success`, `ip`, `user_id`, `user_agent`)
			VALUES
				(NOW(), :success, INET_ATON(:ip), :user_id, :user_agent)";

        $ip = new Ip("get");

        $stmt = Db::getPdo()->prepare($log_attempt_q);
        $stmt->execute([
            ":success"         => $success,
            ":ip"             => $ip->getIp(),
            ":user_id"        => $user_id,
            ":user_agent"     => $_SERVER["HTTP_USER_AGENT"]
        ]);
    }
}
