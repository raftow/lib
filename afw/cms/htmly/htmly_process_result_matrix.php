<?php
class HtmlyProcessResultMatrix extends HtmlyMatrix
{
    /**
     * Adds an error cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addError($title, $the_message, $css_other_classes="")
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-error $css_other_classes");
    }


    /**
     * Adds an warning cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addWarning($title, $the_message, $css_other_classes="")
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-warning $css_other_classes");
    }

    /**
     * Adds an success cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addSuccess($title, $the_message, $css_other_classes="")
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-success $css_other_classes");
    }


    /**
     * Adds a result cell to the matrix based on the provided parameters.
     * @param AFWObject $object The object to be represented in the matrix cell.
     * @param string $error The error message (if any).
     * @param string $warning The warning message (if any).
     * @param string $success The success message (if any).
     * @param string $showTitleMethod The title method to describe the object if the object is null it should contain the title itself.
     * 
     */
    public function addResult($object, $error, $warning, $success, $showTitleMethod = "getShortDisplay", $result_alert="")
    {
        $title = $object ? $object->$showTitleMethod(AfwLanguageHelper::getGlobalLanguage()) : $showTitleMethod;
        $alert = $object ? $object->alert : $result_alert;
        if ($error) {
            $this->addError($title, $error, $alert);
        } elseif ($warning) {
            $this->addWarning($title, $warning, $alert);
        } elseif ($success) {
            $this->addSuccess($title, $success, $alert);
        }
    }
}
