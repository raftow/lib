<?php

class AfwRichException extends AfwRuntimeException
{
    /**
     * @param array $var_toexpert_arr 
     * @return AfwRichException
     */
    public function __construct(
        string $message,
        string $explain,
        array $var_toexpert_arr = []
    ) {
        $message_html = "<div class='rich exception'>$message</div>";
        $message_html .= "<div class='rich explain'>$explain</div>";

        foreach ($var_toexpert_arr as $var_name => $var_toexpert) {
            $message_html .= "<div class='rich varname'>$var_name</div>";
            $message_html .= AfwExportHelper::afwExport($var_toexpert, 3);
        }
        parent::__construct($message_html);
    }
}
