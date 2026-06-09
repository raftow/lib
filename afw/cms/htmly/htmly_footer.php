<?php

class HtmlyFooter extends HtmlyElement
{
    public function __construct(
        $id = "",
        $name = "",
        $text_direction = ''
    ) {
        parent::__construct("tfoot", true, $id, $name, $text_direction);
    }
}
