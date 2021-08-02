<?php namespace GuildCP;

require_once __DIR__ . "/menuitem.class.php";
/**
 * Represents the main menu
 */
class Menu
{

    private $items;

    /**
     * Add a menu item to the Menu
     * @param $page The page of the menu item
     * @param $label The label of the menu item
     * @param $class The class of the menu item
     */
    public function addItem($page, $label, $class = "")
    {
        $this->items[] = new MenuItem($page, $label, $class);
        return $this;
    }

    /**
     * Add a MenuItem object to the Menu
     * @param MenuItem object to add to the Menu
     */
    public function addItemObject($item)
    {
        if ($item instanceof MenuItem) {
            $this->items[] = $item;
        }
        return $this;
    }

    /**
     * Renders the menu as HTML
     * @return $src Representing the HTML code of the Menu
     */
    public function render()
    {
        $src = "
        <div id='main-menu'>
                <nav class='navbar navbar-expand-lg navbar-dark bg-dark'>
                    <div class='container justify-content-center'>    
                    <div class='justify-content-start mr-5'>
                        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#navbarDropdown' aria-controls='navbarDropdown' aria-expanded='false' aria-label='Toggle navigation'>
                            <span class='navbar-toggler-icon'></span>
                        </button>
                    </div>
                        <div class='justify-content-start'>
                            <a class='navbar-brand' href='/'><img src='/../include/images/GCPf1.png' width='47px' height='60px' alt='Guild Control Panel'></a>
                        </div>
                        <div class='collapse navbar-collapse justify-content-end' id='navbarDropdown'>
                        <ul class='navbar-nav'>
        ";

        foreach ($this->items as $item) {
            $active = $item->isActive() ? "active" : "";
            if (!count($item->getChildren())) {
                $src .= "<li class='{$item->getClass()} {$active} nav-item'><a class='nav-link {$active}' href='{$item->getURL()}'>{$item->getLabel()}</a></li>";
            } else {
                $src .= "<li class='{$item->getClass()} {$active} nav-item dropdown'>
                    <a href='#' class='nav-link dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' id='{$item->getLabelId()}'>{$item->getLabel()}</a>
                    <div class='dropdown-menu bg-dark' aria-labelledby='{$item->getLabelId()}'>
                    ";
                foreach ($item->getChildren() as $child) {
                    $src .= "<a class='dropdown-item text-light' href='{$child->getURL()}'>{$child->getLabel()}</a>";
                }
                $src .= "</div></li>";
            }
        }

        $src .= "</ul></div></div></nav></div>";

        return $src;
    }
}
