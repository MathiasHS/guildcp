<?php namespace GuildCP;

/**
 * Represents a MenuItem
 */
class MenuItem
{
    private $label;
    private $page;
    private $class;
    private $children;

    /**
     * Construct a MenuItem representing an item of a menu
     * @param $page Page of the menu item
     * @param $label Label of the menu item
     * $param $class Class of the menu item
     */
    public function __construct($page, $label, $class = "")
    {
        if ($page == "default") {
            $this->page = "";
        } else {
            $this->page = $page;
        }

        $this->label = $label;
        $this->class = $class;
        $this->children = array();
    }

    /**
     * Add a child to the menu item
     * @param $page The page of the child
     * @param $label The label of the child
     * @return $this The current instance
     */
    public function addChild($page, $label)
    {
        $this->children[] = new MenuItem($page, $label);
        return $this;
    }

    /**
     * Returns the children of the menu item
     * @return $children Array of children
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Returns the page of the current menu item
     * @return $page The page of the current menu item
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returns the label of the current menu item
     * @return $label The label of the current menu item
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns an MD5 hash of the name to use for dropdown ID
     */
    public function getLabelId()
    {
        return md5($this->label);
    }

    /**
     * Returns the class of the current menu item
     * @return $class The class of the current menu item
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Returns the URL of the current menu item
     * @return $url String representing the URL of the current menu item
     */
    public function getURL()
    {
        $url = "";

        if (!strlen($this->page)) {
            $url = "./";
        } else {
            $url = $this->page;
        }

        return $url;
    }

    /**
     * Returns true / false based on whether or not the menu item is active
     * @return boolean True if active, false if not
     */
    public function isActive()
    {
        $uri = explode("?", $_SERVER["REQUEST_URI"]);
        $page = rtrim($uri[0], "/");

        if ($page == $this->page) {
            return true;
        } else {
            return false;
        }
    }
}
