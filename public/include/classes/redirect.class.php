<?php namespace GuildCP;

/**
 * Class for redirecting users
 * Idea is we can extend upon this class and create continue/redirect_after links upon login
 */
class Redirect
{

    /**
     * Redirect a user to the specified URL
     * @param string $url The url to redirect the user to
     */
    public static function to($url)
    {
        header("Location: {$url}");
        ob_end_flush();
        die();
    }

    /**
     * For now this is not a priority, but continue/redirect_after links would be a nice implementation in the future
     */
    public static function toLogin($redirect_after = null)
    {
        return null;
    }

}