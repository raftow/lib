<?php

class HtmlyRow extends HtmlyElement
{
    public function __construct(
        $id = "",
        $name = "",
        $text_direction = ''
    ) {
        parent::__construct("tr", true, $id, $name, $text_direction);
    }
}
