<?php namespace GuildCP;
/**
 * Alert class representing a Bootstrap Alert
 */

class Alert
{
    private $type;
    private $title;
    private $message;
    private $classes;
    private static $allowed_types = ["primary", "secondary", "success", "danger", "warning", "info", "light", "dark"];

    /**
     * Opprett en advarsel
     *
     * @param string $title
     * @param string $message
     * @param string $type Tillate verdier: success, info, warning, danger
     */
    public function __construct($title = null, $message = null, $type = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->classes = "";

        $type = strtolower($type);

        if (in_array($type, self::$allowed_types)) {
            $this->type = $type;
        } else {
            throw new InvalidArgumentException("Invalid alert type provided!");
        }
    }

    // Setters
    /**
     * Set tittellen til advarslen
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Sett meldingen til advarslen
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sett typen til advarslen
     * @param string $type Lovlige typer: success, info, warning, danger
     */
    public function setType($type)
    {
        $type = strtolower($type);
        if (in_array($type, self::$allowed_types)) {
            $this->type= $type;
        } else {
            throw new InvalidArgumentException("Invalid alert type provided!");
        }

        return $this;
    }

    /**
     * Get typen til advarslen
     * @return Alert advarslen
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set klassen(e) of advarslens parent div
     * @param string $class
     */
    public function setClass($class)
    {
        $this->classes = $class;
        return $this;
    }

    /**
     * Legg til en klasse til advarslens parent div
     * @param string $class
     */
    public function addClass($class)
    {
        $this->classes .= " " . $class;
        return $this;
    }

    /**
     * Returner advarslen som HTML
     * @return string
     */
    public function render()
    {
        if ($this->title === null && $this->message === null) {
            return "";
        }

        $this->addClass("alert-{$this->type}");
        $this->addClass("alert");

        $html = "<div class='col-sm-12'><div style='margin-bottom: 10px; margin-top: 10px;' class='{$this->classes}'>";

        if (!empty($this->title)) {
            $html .= "<strong>" . htmlentities($this->title) . "</strong><br><br>";
        }

        $html .= "{$this->message}</div></div>";

        return $html;
    }

    /**
     * Create and return a warning alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function warning($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "warning");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a danger alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function danger($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "danger");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return an information alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function info($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "info");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a success alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function success($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "success");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a primary alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function primary($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "primary");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a secondary alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function secondary($title, $message, $class = "")
    {
        $alert = new Alert(null, $message, "secondary");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a light alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function light($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "light");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }

    /**
     * Create and return a dark alert
     * @param string $title
     * @param string $message
     * @param string $class
     * @return string html
     */
    public static function dark($title, $message, $class = "")
    {
        $alert = new Alert($title, $message, "dark");

        if (strlen($class)) {
            $alert->addClass($class);
        }

        return $alert->render();
    }
}
