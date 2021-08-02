<?php namespace GuildCP;

/**
 * Class representing a submenu item
 */
class SubmenuItem
{
    private $label;
    private $page;
    private $icon;
    private $seo;

    /**
     * Represent a Submenu item
     * @param string $page The page of the menu item
     * @param string $label The label of the menu item
     * @param string $icon The icon of the menu item
     * @param boolean $seo Whether or not the URL is SEO optimized with URL rewrites
     */
    public function __construct($page, $label, $icon = "", $seo = false)
    {
        if ($page == "default") {
            $this->page = "";
        } else {
            $this->page = $page;
        }
        $this->label = $label;
        $this->icon = $icon;
        $this->seo = $seo;
    }

    /**
     * Returns the current page of the SubmenuItem
     * @return string $page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the current icon of the SubmenuItem
     * @return string $icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns the current label of the SubmenuItem
     * @return string $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the current URL of the SubmenuItem
     * @return string $url
     */
    public function getURL()
    {
        $url = "";

        if (!strlen($this->page)) {
            $url .= "./";
        } else {
            $url .= $this->seo ? "./{$this->page}" : "./?p={$this->page}";
        }

        return $url;
    }

    /**
     * Returns true if the submenuitem is active, false if not
     * @return boolean True if active, false if not
     */
    public function isActive()
    {
        $page = @$_GET["p"];

        if (empty($page) && !strlen($this->page)) {
            return true;
        }

        if ($page == $this->page) {
            return true;
        }

        return false;
    }
}
