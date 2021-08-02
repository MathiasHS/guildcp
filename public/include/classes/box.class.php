<?php namespace GuildCP;
/**
 * Box klasse for easy usage of containers
 */
class Box
{
    private $title;
    private $content;
    private $style;
    private $class;
    private $centerText;

    private $imageUrl;
    private $imageAlt;

    private $icon;

    /**
     * Create an object representing a container.
     *
     * @param string $title Title of the box
     * @param string $content The content of the box
     * @param boolean $centerText Whether or not to center the text
     */
    public function __construct($title = "", $content = "", $centerText = false)
    {
        $this->title = $title;
        $this->content = $content;
        $this->style = "";
        $this->class = "";
        $this->centerText = $centerText;
    }

    /**
     * Set the title of the box
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set the class of the box
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Add a class to the box, keeping previous classes
     * @param string $class
     */
    public function addClass($class)
    {
        $this->class .= " " . $class;
        return $this;
    }

    /**
     * Set the style of the box
     * @param string $value
     */
    public function setStyle($value)
    {
        $this->style = $value;
        return $this;
    }

    /**
     * Set the content of the box
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Make the box center the text
     * @param boolean $value
     */
    public function setCenter($value)
    {
        $this->centerText = $value;
        return $this;
    }

    /**
     * Add to the body of the box
     * @param string $text
     */
    public function append($text)
    {
        $this->content .= $text;
        return $this;
    }

    /**
     * Set the image of a box
     * @param string $url The directory of the image
     * @param string $alt The description of the image
     */
    public function setImage($url, $alt)
    {
        $this->imageUrl = $url;
        $this->imageAlt = $alt;
    }

    /**
     * Set the box to display a FontAwesome Icon
     * @param $classes The classes to apply to the icon
     * @param $size The size in em of the icon
     * @param $color The color of the icon
     * @return void
     */
    public function setIcon($classes, $size, $color = "")
    {
        $add = "style='font-size: {$size}";
        if (strlen($color)) {
            $add .= "; color: {$color};'";
        } else {
            $add .= ";'";
        }
        $this->icon = "<span class='mx-auto mt-1 mb-1' {$add}><i class='card-img-top {$classes}'></i></span>";
    }

    /**
     * Render the box as HTML
     * @return string
     */
    public function render()
    {
        $card_div = ($this->centerText) ? ("<div class='card text-center bg-dark'>") : ("<div class='card bg-dark'>");
        $class = strlen($this->class) ? ("class='{$this->class}'") : ("");
        $style = strlen($this->style) ? ("style='{$this->style}'") : ("");
        $img = strlen($this->imageUrl) ? ("<img class='card-img-top' src='{$this->imageUrl}' alt='{$this->imageAlt}'>") : ("");
        $icon = strlen($this->icon) ? ($icon = $this->icon) : ("");
        $title = strlen($this->title) ? ("<h5 class='card-header text-light'>{$this->title}</h5>") : ("");
        $body = "
        <div {$class} {$style}>
            {$card_div}
                {$img}{$icon}
                {$title}
                <div class='card-body text-light'>
                    {$this->content}
                </div>
            </div>
        </div>";

        return $body;
    }
}
