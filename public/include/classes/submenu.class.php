<?php namespace GuildCP;

require_once __DIR__ . "/submenuitem.class.php";
/**
 * Class representing a submenu
 */
class Submenu
{
    
    private $title;
    private $items;
    private $class;

    /**
     * Construct a Submenu
     * @param string $title The title of the submenu
     */
    public function __construct($title = "")
    {
        $this->title = $title;
        $this->items = array();
        $this->class = "default-submenu";
    }

    /**
     * Set the class of the submenu
     * @param string $class The class to set
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * Add an item to the submenu
     * @param string $page The name of the page used internally in the URL
     * @param string $label The label shown to the user
     * @param string $icon The icon shown with the label
     * @param boolean $seo Whether or not the link is SEO friendly (through apache mod rewrite)
     */
    public function addItem($page, $label, $icon = "", $seo = false)
    {
        $this->items[] = new SubmenuItem($page, $label, $icon, $seo);
        return $this;
    }

    /**
     * Get the url name of the current page
     * @return string The name of the page in the url
     */
    public static function getCurrentPage()
    {
        $page = @$_GET["p"];

        if (empty($page)) {
            return "default";
        } else {
            return htmlentities($page);
        }
    }

    /**
     * Returns a unique ID to prevent ID clashing in case of submenu being used multiple times with same id
     * @return string Unique ID for navbar collapse toggle
     */
    public function getUniqueID()
    {
        $title = str_replace($this->title, " ", "");
        return md5($title);
    }

    /**
     * Renders the submenu as HTML
     * @return $src Representing the HTML code of the Submenu
     */
    public function render()
    {
        $src = "<div class='submenu-mobile-toggle bg-dark text-center text-light'>{$this->title} 
                    <i data-toggle='collapse' data-target='#{$this->getUniqueID()}' aria-controls='{$this->getUniqueID()}' aria-expanded='false' aria-label='Toggle navigation' class='submenu-mobile-toggle fas fa-bars fa-2x'></i>
                </div>";
        $src .= "<div class='{$this->class}'>";
        $src .= "<nav class='navbar navbar-expand-lg justify-content-center navbar-dark bg-dark'>";
        $src .= "<div class='collapse navbar-collapse justify-content-center' id='{$this->getUniqueID()}'>";
        $src .= "<ul class='navbar-nav submenu-toggle'>";

        foreach ($this->items as $item) {
            $active = $item->isActive() ? "active" : "";
            $icon = strlen($item->getIcon()) ? "<div class='submenu_icon_surround'>{$item->getIcon()}</div>" : "";

            $src .= "<li class='nav-item {$active}'><a class='nav-link text-center {$active}' href='{$item->getURL()}'>{$icon}{$item->getLabel()}</a></li>";
        }

        $src .= "</ul></div></nav></div>";

        return $src;
    }
}