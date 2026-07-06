<?php
class HtmlyProcessResultMatrix extends HtmlyMatrix
{
    /**
     * Adds an error cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addError($title, $the_message)
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-error");
    }


    /**
     * Adds an warning cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addWarning($title, $the_message)
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-warning");
    }

    /**
     * Adds an success cell to the matrix.
     *
     * @param string $title
     * @param string $the_message
     */
    public function addSuccess($title, $the_message)
    {
        $this->addCell(null, $title, $the_message, "", "htmly-matrix-success");
    }


    /**
     * Adds a result cell to the matrix based on the provided parameters.
     * @param AFWObject $object The object to be represented in the matrix cell.
     * @param string $error The error message (if any).
     * @param string $warning The warning message (if any).
     * @param string $success The success message (if any).
     * @param string $showTitleMethod The title method to describe the object.
     * 
     */
    public function addResult($object, $error, $warning, $success, $showTitleMethod = "getShortDisplay")
    {
        $title = $object->$showTitleMethod(AfwLanguageHelper::getGlobalLanguage());
        if ($error) {
            $this->addError($title, $error);
        } elseif ($warning) {
            $this->addWarning($title, $warning);
        } elseif ($success) {
            $this->addSuccess($title, $success);
        }
    }
}
