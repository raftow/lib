<?php

class HtmlyDiv extends HtmlyElement
{
    /**
     * @param mixed $content
     */
    public function __construct(
        $content,
        $id = "",
        $name = "",
        $class = '',
        $text_direction = '',
    ) {
        parent::__construct("div", true, $id, $name, $text_direction);
        $this->addClass($class);
        $this->addElement($content);
    }
}
