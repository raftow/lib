<?php

// old require of afw_root 

class AfwFrameworkHelper extends AFWRoot {

        public static function displayInEditMode($cl)
        {
            global $display_in_edit_mode, $display_in_display_mode;
            return ($display_in_edit_mode[$cl]) or ($display_in_edit_mode["*"] and (!$display_in_display_mode[$cl]));
        }
}
                                 
