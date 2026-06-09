<?php

class HtmlyHeader extends HtmlyElement
{
    public function __construct(
        $id = "",
        $name = "",
        $text_direction = ''
    ) {
        parent::__construct("thead", true, $id, $name, $text_direction);
    }
}
