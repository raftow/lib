<?php

class HtmlyRowBody extends HtmlyRow
{
    public function __construct(
        $id = "",
        $name = "",
        $text_direction = '',
        $cells = []
    ) {
        parent::__construct($id, $name, $text_direction);
        foreach($cells as $classCell => $contentCell) {
            $this->addElement(new HtmlyCell(false,"","",$text_direction, $contentCell, $classCell));
        }
    }
}
