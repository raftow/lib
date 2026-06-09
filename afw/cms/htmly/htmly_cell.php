<?php

class HtmlyCell extends HtmlyElement
{
    public function __construct(
        $headerFooter = false, 
        $id = "",
        $name = "",
        $text_direction = '',
        $content = '',
        $classCss = ''
    ) {
        $balise = $headerFooter ? "th" : "td";
        parent::__construct($balise, true, $id, $name, $text_direction);
        if($content) $this->addElement($content);
        if($classCss) $this->addClass($classCss);
    }
}
