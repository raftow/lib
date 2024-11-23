<?php
    // will be deprecated and then obsoleted
    $TECH_FIELDS = array();
    $MODULE = "workflow";
    $THIS_MODULE_ID = 1283;
    $MODULE_FRAMEWORK[$THIS_MODULE_ID] = 1;                
    $LANGS_MODULE = array("ar"=>true,"fr"=>false,"en"=>true);
    
    $file_box_css_class = "rea_filebox";  // by default filebox
    
    $file_type_ids = "2,3,4,5,6,7,10,12,13,15";
    
    $module_config_token = array();
    $module_config_token["file_types"] = $file_type_ids;
    
    // $config["img-path"] = "pic/";
    // $config["img-company-path"] = "../external/pic/";
    
    $display_in_edit_mode["*"] = true;

    $TECH_FIELDS[$MODULE]["CREATION_USER_ID_FIELD"]="created_by";
    $TECH_FIELDS[$MODULE]["CREATION_DATE_FIELD"]="created_at";
    $TECH_FIELDS[$MODULE]["UPDATE_USER_ID_FIELD"]="updated_by";
    $TECH_FIELDS[$MODULE]["UPDATE_DATE_FIELD"]="updated_at";
    $TECH_FIELDS[$MODULE]["VALIDATION_USER_ID_FIELD"]="validated_by";
    $TECH_FIELDS[$MODULE]["VALIDATION_DATE_FIELD"]="validated_at";
    $TECH_FIELDS[$MODULE]["VERSION_FIELD"]="version";
    $TECH_FIELDS[$MODULE]["ACTIVE_FIELD"]="active";
?>