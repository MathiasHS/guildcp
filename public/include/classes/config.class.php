<?php namespace GuildCP;

/**
 * Class for collection of settings
 */
class Config
{
    private static $data = [];

    /**
     * Set the value of a setting
     * @param [type] $key The name of the setting
     * @param mixed $value The value of the setting
     */
    public static function set($key, $value)
    {
        Config::$data[$key] = $value;
    }

    /**
     * Get the value of a setting
     * @param [type] $key The name of the setting
     * @return mixed
     */
    public static function get($key)
    {
        if (array_key_exists($key, Config::$data)) {
            return Config::$data[$key];
        } else {
            trigger_error("Config key {$key} does not exist.", E_USER_WARNING);
            return null;
        }
    }
}