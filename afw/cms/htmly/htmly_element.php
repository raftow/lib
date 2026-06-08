<?php
class HtmlyElement {
    private $balise = "";
    private $elements = [];
    private $container = true;
    private $attributes = [];

    /**
     * @var array<string>
     */
    private $classes = [];

    private $id = "";
    private $name = "";
    

    /**
     * @param string $balise
     */

    public function __construct($balise, $container=true, $id = "", $name = "") {
        $this->balise = $balise;
        $this->container = $container;
        $this->name = $name;
        $this->id = $id;
    }

    /**
     * @param string $class
     */

    public function addClass($class) {
        $this->classes[$class] = $class;
    }

    /**
     * @param string $class
     */
    public function removeClass($class) {
        unset($this->classes[$class]);
    }

    public function addElement(HtmlyElement $element) {
        $this->elements[] = $element;
    }


    /**
     * @param string $attribute
     * @param string $value
     */

    public function setAttribute($attribute, $value) {
        $this->attributes[$attribute] = $value;
    }

    /**
     * @param string $attribute
     */
    public function removeAttribute($attribute) {
        unset($this->attributes[$attribute]);
    }


    public function renderHtml() {
        
    }




}