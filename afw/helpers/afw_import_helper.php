<?php

class AfwImportHelper extends AFWRoot
{
    /**
     * @param AFWObject $object
     * @param Afile $afileObj
     */
    public static function executeSimpleImport(
        $object,
        $afileObj,
        $attribute_target,
        $attribute_source = null,
        $curr_bloc = 1,
        $overwrite_data = true,
        $skip_error = true,
        $always_commit = true,
        $lang = 'ar'
    ) {
        $me = AfwSession::getUserIdActing();
        if (!$me) {
            return ['no user connected', 'no user connected'];
        }
        $file_dir_name = dirname(__FILE__);

        $errors_arr = [];
        $warnings_arr = [];
        $informations_arr = [];

        $value_source =
            $afileObj->getId() . '-' . $afileObj->getShortDisplay($lang);
        $max_records_once = 500;//3000;
        $start_record_num = ($curr_bloc - 1) * $max_records_once;
        $end_record_num = $curr_bloc * $max_records_once - 1;
        list($excel, $my_head, $my_data) = $afileObj->getExcelData(
            $start_record_num,
            $end_record_num,
            'executeSimpleImport'
        );

        // die("my_head=".var_export($my_head, true)."<br>\n my_data=".var_export($my_data, true));
        $desc_target = AfwStructureHelper::getStructureOf($object,$attribute_target);
        $options = [];

        $okImport = false;
        $all_records =
            count($my_data) .
            " from record $start_record_num to $end_record_num";
        if ($desc_target) {
            $table_target = $desc_target['ANSWER'];
            $table_name_for_import_config = $table_target . '_import_config';
            $module_target = $desc_target['ANSMODULE'];
            $field_item_for_import = $desc_target['ITEM'];
            $field_item_for_import_id = $object->getId();
            list($myFileName, $myClassName) = AfwStringHelper::getHisFactory($table_target,$module_target);
            list($importParamsFileName,$importClassFileName,$importParamsShouldFileName,) = AfwStringHelper::getHisFactory($table_name_for_import_config,$module_target);

            if ($importParamsFileName) {
                require $importParamsFileName;
            }

            if (
                !$importRequirement or
                !is_array($importRequirement) or
                count($importRequirement) == 0
            ) {
                $errors_arr[] = $object->translateMessage(
                    "Missed the requirement table for object import, please define it in [$importParamsShouldFileName] !"
                );
            } else {
                list(
                    $okImport,
                    $errors,
                    $warnings,
                    $infos,
                ) = $excel->meetsRequirement($importRequirement, $lang);

                $errors_arr = array_merge($errors_arr, $errors);
                // die(var_export($errors_arr,true));
                $warnings_arr = array_merge($warnings_arr, $warnings);
                $infos_arr = array_merge($infos_arr, $infos);

                if ($okImport) {
                    // require_once $myFileName;
                    $myObjImport = new $myClassName();

                    list(
                        $okImport,
                        $importedObjects,
                        $skippedObjects,
                        $ignoredObjects,
                        $already_exists,
                        $errors,
                        $warnings,
                        $infos,
                    ) = $myObjImport->simpleImportData(
                        $field_item_for_import,
                        $field_item_for_import_id,
                        $attribute_source,
                        $value_source,
                        $my_data,
                        $overwrite_data,
                        $skip_error,
                        $always_commit,
                        $options,
                        $lang,
                        $excel->getDataStartRow()
                    );
                    //die(var_export($errors,true));
                    $errors_arr = array_merge($errors_arr, $errors);
                    // die(var_export($errors_arr,true));
                    $warnings_arr = array_merge($warnings_arr, $warnings);
                    $infos_arr = array_merge($infos_arr, $infos);
                }
            }
        } else {
            $errors_arr[] =
                $object->translateMessage(
                    'Missed good target attribute for object import, please define correct one current = ',
                    $lang
                ) .
                $attribute_target .
                ' !';
        }

        return [
            $all_records,
            $importedObjects,
            $skippedObjects,
            $ignoredObjects,
            $already_exists,
            $errors_arr,
            $warnings_arr,
            $infos_arr,
        ];
    }

    final public static function importData(
        $object,
        $eimport_id,
        $table_id,
        $my_data,
        $orgunit_id,
        $overwrite_data,
        $skip_error,
        $always_commit,
        $options,
        $lang,
        $dataStartRow = 3,
        $halt_if_error = false,
        $dont_check_error = true
    )     
    {
        global $nb_instances;
        $ok = true;
        $errors_arr = [];
        $warnings_arr = [];
        $informations_arr = [];
        $records = [];
        $importedObjects = [];
        $importedObj = null;
        $memoryOptimize = $options[1];
        

        $stop = false;
        $recordNum = $dataStartRow;
        foreach ($my_data as $id_data => $dataRecord) {
            unset($my_errors_arr);
            unset($my_warnings_arr);
            unset($my_informations_arr);

            $my_errors_arr = [];
            $my_warnings_arr = [];
            $my_informations_arr = [];

            if (!$stop) {
                $old_nb_instances = $nb_instances;
                list(
                    $importedObj,
                    $errors,
                    $warnings,
                    $informations,
                ) = $object->importRecord(
                    $dataRecord,
                    $orgunit_id,
                    $overwrite_data,
                    $options,
                    $lang,
                    $dont_check_error
                );
                $nb_instances = $old_nb_instances;
                if ($importedObj) {
                    $importedObj_id = $importedObj->id;
                } else {
                    $importedObj_id = 0;
                }

                $eiRec = EimportRecord::loadByMainIndex(
                    $eimport_id,
                    $recordNum,
                    $table_id,
                    $importedObj_id,
                    $object->namingImportRecord($dataRecord, $lang),
                    $create_obj_if_not_found = true
                );

                $my_errors_arr = array_merge($my_errors_arr, $errors);
                $my_warnings_arr = array_merge($my_warnings_arr, $warnings);
                $my_informations_arr = array_merge(
                    $my_informations_arr,
                    $informations
                );

                if ($importedObj) {
                    if (
                        $dont_check_error or
                        count($my_errors_arr) == 0 and $importedObj->isOk(true)
                    ) {
                        $importOk = true;
                        $importedObj->commit();
                    } else {
                        $importOk = false;
                        $error_record = implode(
                            ' / ',
                            AfwDataQualityHelper::getDataErrors($importedObj, $lang)
                        );
                        $importedObject_desc = $importedObj->getDisplay($lang);
                        $my_errors_arr[] = "$importedObject_desc : $error_record";
                    }

                    // if(count($errors)>0) $object->simpleError("XXError while importing record : ".implode(",<br>",$errors)." after merge : ".implode("/<br>",$my_errors_arr));
                } else {
                    // if(count($errors)>0) $object->simpleError("YYError while importing record : ".implode(",<br>",$errors)." after merge : ".implode("/<br>",$my_errors_arr));
                    $importOk = false;
                    if (!$skip_error and $halt_if_error) {
                        $object->simpleError(
                            "can't create the principal object " .
                                implode(' / ', $my_errors_arr)
                        );
                    }
                    $error_record = "can't create the principal object";
                    $my_errors_arr[] = "record $recordNum : $error_record ";
                }

                if ($importOk) {
                    $importedObj->recordNum = $recordNum;
                    if (!$memoryOptimize) {
                        $importedObjects[] = $importedObj;
                    }
                    $eiRec->set('success', 'Y');
                    $eiRec->set('new', $importedObj->is_new ? 'Y' : 'N');
                    if ($dont_check_error) {
                        $error_description = 'No Error check';
                    } else {
                        $error_description = '';
                    }
                    $eiRec->set('error_description', $error_description);
                } else {
                    $ok = false;
                    $eiRec->set('success', 'N');
                    $eiRec->set(
                        'error_description',
                        implode(' / ', $my_errors_arr)
                    );

                    if (!$skip_error) {
                        $eiRec->set(
                            'error_description',
                            $eiRec->getVal('error_description') .
                                ' import halted !'
                        );
                        $stop = true;
                    }
                }
                $eiRec->commit();

                if (!$memoryOptimize) {
                    $records[$recordNum] = $eiRec;
                } else {
                    $importedObj->optimizeMemory();
                    // die(var_export($importedObj,true));
                    $importedObj->destroyData();
                    unset($importedObj);
                    $eiRec->optimizeMemory();
                    // die(var_export($eiRec,true));
                    $eiRec->destroyData();
                    unset($eiRec);
                }
                $recordNum++;
            }
            $errors_arr = array_merge($my_errors_arr, $errors_arr);
            $warnings_arr = array_merge($my_warnings_arr, $warnings_arr);
            $informations_arr = array_merge(
                $my_informations_arr,
                $informations_arr
            );
        }

        return [
            $ok,
            $records,
            $importedObjects,
            $errors_arr,
            $warnings_arr,
            $informations_arr,
        ];
    }



    final public static function simpleImportData(
        $object,
        $item_field,
        $item_field_id,
        $attribute_source,
        $value_source,
        $my_data,
        $overwrite_data,
        $skip_error,
        $always_commit,
        $options,
        $lang,
        $dataStartRow = 3,
        $halt_if_error = false,
        $check_data_ok = false
    ) {
        global $nb_instances;
        $ok = true;
        $errors_arr = [];
        $warnings_arr = [];
        $informations_arr = [];
        $importedObjects = 0;
        $skippedObjects = 0;
        $ignoredObjects = 0;
        $already_exists = 0;
        $memoryOptimize = true; //$options[1];

        $stop = false;
        $recordNum = $dataStartRow;
        foreach ($my_data as $id_data => $dataRecord) {
            unset($my_errors_arr);
            unset($my_warnings_arr);
            unset($my_informations_arr);

            $my_errors_arr = [];
            $my_warnings_arr = [];
            $my_informations_arr = [];

            if (!$stop) {
                $old_nb_instances = $nb_instances;

                list(
                    $importedObj,
                    $errors,
                    $warnings,
                    $informations,
                ) = $object->simpleImportRecord(
                    $item_field,
                    $item_field_id,
                    $dataRecord,
                    $overwrite_data,
                    $options,
                    $check_data_ok,
                    $lang
                );
                $nb_instances = $old_nb_instances;
                if ($importedObj) {
                    $importedObj_id = $importedObj->getId();
                } else {
                    $importedObj_id = 0;
                }

                $my_errors_arr = array_merge($my_errors_arr, $errors);
                $my_warnings_arr = array_merge($my_warnings_arr, $warnings);
                $my_informations_arr = array_merge(
                    $my_informations_arr,
                    $informations
                );

                $imported_object = false;
                $skipped_object = false;
                $ignored_object = false;

                if ($importedObj) {
                    $source_setted = false;
                    $update_source_of_import = false;
                    if (
                        count($my_errors_arr) == 0 and
                        (!$check_data_ok or $importedObj->isOk(true))
                    ) {
                        $importOk = true;
                        $update_source_of_import = true;
                        $imported_object = true;
                    } else {
                        $importOk = false;
                        if ($skip_error) {
                            $update_source_of_import = true;
                            $skipped_object = true;
                        } else {
                            $ignored_object = true;
                        }
                        $error_record = implode(
                            ' / ',
                            AfwDataQualityHelper::getDataErrors($importedObj, $lang)
                        );
                        $importedObject_desc = $importedObj->getDisplay($lang);
                        $my_errors_arr[] = "$importedObject_desc : $error_record";
                    }

                    if ($update_source_of_import) {
                        if ($attribute_source and $value_source) {
                            $source_setted = $importedObj->set(
                                $attribute_source,
                                $value_source
                            );
                        }
                        if (!$source_setted) {
                            $already_exists++;
                            if (count($my_warnings_arr) < 10) {
                                $my_warnings_arr[] =
                                    'already worked : ' .
                                    $importedObj->getNodeDisplay($lang);
                            }
                        }
                        $importedObj->commit();
                    }

                    // if(count($errors)>0) $object->simpleError("XXError while importing record : ".implode(",<br>",$errors)." after merge : ".implode("/<br>",$my_errors_arr));
                } else {
                    $ignored_object = true;
                    // if(count($errors)>0) $object->simpleError("YYError while importing record : ".implode(",<br>",$errors)." after merge : ".implode("/<br>",$my_errors_arr));
                    $importOk = false;
                    $error_record =
                        "record $recordNum : can't create the principal object : " .
                        implode(' / ', $my_errors_arr);
                    if (!$skip_error and $halt_if_error) {
                        $object->simpleError($error_record);
                    }
                    $my_errors_arr[] = $error_record;
                }

                if ($imported_object) {
                    $importedObjects++;
                }
                if ($skipped_object) {
                    $skippedObjects++;
                }
                if ($ignored_object) {
                    $ignoredObjects++;
                }

                if ($importOk) {
                    $importedObj->recordNum = $recordNum;
                } else {
                    $ok = $skip_error;
                    $stop = !$skip_error;
                }

                unset($importedObj);

                $recordNum++;
            }
            $errors_arr = array_merge($my_errors_arr, $errors_arr);
            $warnings_arr = array_merge($my_warnings_arr, $warnings_arr);
            $informations_arr = array_merge(
                $my_informations_arr,
                $informations_arr
            );
        }

        return [
            $ok,
            $importedObjects,
            $skippedObjects,
            $ignoredObjects,
            $already_exists,
            $errors_arr,
            $warnings_arr,
            $informations_arr,
        ];
    }
}