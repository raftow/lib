<?php

class HtmlyTableau extends HtmlyElement
{

    private string $last_element_class = "";

    public function __construct(
        $id = "",
        $name = "",
        $text_direction = ''
    ) {
        parent::__construct("table", true, $id, $name, $text_direction);
    }


    /**
     * @param HtmlyElement $element
     * @return string
     */
    protected function beforeRenderElement($element)
    {
        $return = "";
        $element_class = get_class($element);
        if(($element_class == "HtmlyRowHeader") and ($this->last_element_class != "HtmlyRowHeader")) {
            if($this->last_element_class == "HtmlyRowFooter") {
                $return .= "</tfoot>\n";        
            }
            if($this->last_element_class == "HtmlyRowBody") {
                $return .= "</tbody>\n";        
            }
            $return .= "<thead>\n";    
        }

        if(($element_class == "HtmlyRowFooter") and ($this->last_element_class != "HtmlyRowFooter")) {
            if($this->last_element_class == "HtmlyRowHeader") {
                $return .= "</thead>\n";        
            }
            if($this->last_element_class == "HtmlyRowBody") {
                $return .= "</tbody>\n";        
            }
            $return .= "<tfoot>\n";    
        }

        if(($element_class == "HtmlyRowBody") and ($this->last_element_class != "HtmlyRowBody")) {
            if($this->last_element_class == "HtmlyRowHeader") {
                $return .= "</thead>\n";        
            }
            if($this->last_element_class == "HtmlyRowFooter") {
                $return .= "</tfoot>\n";        
            }
            $return .= "<tbody>\n";    
        }

        $this->last_element_class = $element_class;
        return $return;
    }

    /**
     * @param HtmlyElement $element
     * @return string
     */
    protected function afterRenderElement($element)
    {
        return "";
    }

    /**
     * @return string
     */
    protected function afterRenderElements()
    {
        $return = "";
        if($this->last_element_class == "HtmlyRowHeader") {
            $return .= "</thead>\n";    
        }

        if($this->last_element_class == "HtmlyRowFooter") {
            $return .= "</tfoot>\n";    
        }

        if($this->last_element_class == "HtmlyRowBody") {
            $return .= "</tbody>\n";    
        }

        return $return;
    }
}
