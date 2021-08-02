<?php namespace GuildCP;

require_once __DIR__ . "/../config.php";

/**
 * DB Class for simplified instance management
 */
class Db
{
    /**
     * Connect to the MySQL server using PDO and return PDO object
     * @return PDO A PDO object which represents the connection to the MySQL server
     */
    public static function getPdo()
    {
        try {
            $config = self::getConfig();
            $pdo = new \PDO(
                "mysql:host=" . $config['hostname'] . ";dbname=" . $config['database'] . ";charset=utf8",
                $config['username'],
                $config['password']
            );

            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (Exception $e) {
            echo "Error: Connection to the database could not be established.";
            die();
        }
    }

    /**
     * Get the configuration options for DB Connection
     * @return Array Configuration options
     */
    private static function getConfig()
    {
        return [
            'hostname' => getenv('GCP_MYSQL_HOSTNAME'),
            'database' => getenv('GCP_MYSQL_DATABASE'),
            'username' => getenv('GCP_MYSQL_USERNAME'),
            'password' => getenv('GCP_MYSQL_PASSWORD')
        ];
    }
}