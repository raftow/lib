<?php
interface AfwImportable {
    
    // the implementation of a new import process  using AfwImportHelper is just 
    // to make the Afw class implements and write these these 3 methods
    // 1. importRecord (or simpleImportRecord or both depending on import tool and options)
    // 2. namingImportRecord
    // 3. getRelatedClassesForImport()
    // and write the [sub_class_name]_import_config.php see example : employee_import_config.php

    // should return return [$afw_object, $errors, $infos, $warnings];
    public function simpleImportRecord(
        $item_field,
        $item_val,
        $dataRecord,
        $overwrite_data,
        $options,
        $check_data_ok,
        $lang
    );

    
    // should return return [$afw_object, $errors, $infos, $warnings];
    public function importRecord(
        $dataRecord,
        $orgunit_id,
        $overwrite_data,
        $options,
        $lang,
        $dont_check_error
    );

    // should return the name/title of the record imported
    // can return for example $this->getDisplay($lang);
    public function namingImportRecord($dataRecord, $lang);
    

    // return array of class names of related Afw classes to import
    public function getRelatedClassesForImport($options = null);
}