<?php
$config_arr = array(
        'application_id' => 1283,

        'application_code' => 'workflow',

        'application_name' => ['ar' => 'سير العمل', 'en' => 'work flow',],
                                  
        'no_menu_for_login' => true,

        'enable_language_switch' => false,

        'notify_customer' => array("new_request" => array("sms"=>true, "email" => false, "web" => false, "whatsup" => false),
        
                                ),

        'notify_manager' => array("new_request" => array("sms"=>true, "email" => false, "web" => true, "whatsup" => false),
        
                        ),

        'notify_employee' => array("new_request" => array("sms"=>true, "email" => false, "web" => true, "whatsup" => false),
        
                ),


        'general_company_id' => 1,

        'LOGO_APP_HEIGHT' => 66,
        'LOGO_APP_MARGIN_TOP' => 5,
        'TITLE_APP_HEIGHT' => 56,
        'TITLE_APP_MARGIN_TOP' => 15,
        'LOGO_COMP_HEIGHT' => 56,
        'LOGO_COMP_MARGIN_TOP' => 14,
        'TITLE_COMP_HEIGHT' => 56,
        'TITLE_COMP_MARGIN_TOP' => -10,
        
        
        'DISABLE_PROJECT_ITEMS_MENU' => true,

        // APPLICATION SETTINGS
        'MODE_DEVELOPMENT' => true,

        // SIS settings
        'default_course_mfk' => ',1,',
        );


