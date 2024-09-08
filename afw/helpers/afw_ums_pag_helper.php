<?php
class AfwUmsPagHelper extends AFWRoot 
{
    //      17 items
    public static $afield_type_items = 17;

    // 	6	اختيار متعدد من قائمة
    public static $afield_type_mlst = 6;
    // 	5	اختيار من قائمة
    public static $afield_type_list = 5;
    // 	12	إختيار من قائمة قصيرة
    public static $afield_type_enum = 12;
    // 	15	إختيار متعدد من قائمة قصيرة
    public static $afield_type_menum = 15;

    // 	2	تاريخ
    public static $afield_type_date = 2;
    // 	9	تاريخ نصراني
    public static $afield_type_Gdat = 9;
    // 	4	وقت
    public static $afield_type_time = 4;

    // 	1	قيمة عددية متوسطة
    public static $afield_type_nmbr = 1;
    // 	13	قيمة عددية صغيرة
    public static $afield_type_int = 13;
    // 	14	قيمة عددية كبيرة
    public static $afield_type_bigint = 14;
    // 	3	مبلغ من المال
    public static $afield_type_amnt = 3;
    // 	7	نسبة مائوية
    public static $afield_type_pctg = 7;
    // 	16	قيمة عددية كسرية
    public static $afield_type_float = 16;

    // 	11	نص طويل
    public static $afield_type_mtxt = 11;
    // 	10	نص قصير
    public static $afield_type_text = 10;

    // 	8	نعم/لا
    public static $afield_type_yn = 8;


    public static final function getAtableObj($module,$table)
    {
        if (!($module and $table)) {
            return null;
        }

        // list($module_fileName,) = AfwStringHelper::getHisFactory("module", "ums");
        // require_once($module_fileName);
        $mod = new Module();
        $mod->select('module_code', $module);
        $mod->load();
        if (!$mod->getId()) {
            return null;
        }

        // list($atable_fileName,) = AfwStringHelper::getHisFactory("atable", "pag");
        // require_once($atable_fileName);
        $atb = new Atable();
        $atb->select('atable_name', $table);
        $atb->select('id_module', $mod->getId());
        $atb->load();
        if (!$atb->getId()) {
            return null;
        }

        return $atb;
    }


    public static final function getMyModuleAndAtable(
        $id_main_sh,
        $mcode,
        $table_name,
        $cremod = false,
        $cretbl = true
    ) 
    {
        if (!$mcode) {
            return null;
        }

        $file_dir_name = dirname(__FILE__);
        
        $mdl = Module::getModuleByCode($id_main_sh, $mcode);

        if (!$mdl) 
        {
            $me = AfwSession::getUserIdActing();
            if ($cremod) {
                $mdl = new Module();
                $mdl->set('id_main_sh', $id_main_sh);
                $mdl->set('module_code', $mcode);
                $mdl->set('titre_short', $mcode);
                $mdl->set('titre', $mcode);
                $mdl->set('id_module_type', 5);
                $mdl->set('id_module_status', 3);
                $mdl->set('id_br', $me);
                $mdl->insert();
                $mdl_new = true;
            } else {
                $mdl_new = null;
            }
        } else {
            $mdl_new = false;
        }

        return self::getMyAtable($mdl, $mdl_new, $table_name, $cremod, $cretbl);
    }

    public static final function getMyAtable(
        $mdl,
        $mdl_new,
        $table_name,
        $cremod = false,
        $cretbl = false
    ) {
        $file_dir_name = dirname(__FILE__);
        $mdl_id = $mdl->getId();

        if ($mdl_id) {
            

            $tbl = Atable::getAtableByName($mdl_id, $table_name);
            if (!$tbl) {
                $tbl_id = 0;
                if ($cretbl) {
                    $tbl = new Atable();
                    $tbl->set('id_module', $mdl_id);
                    $tbl->set('atable_name', $table_name);
                    $tbl->set('real_table', 'Y');
                    $tbl->set('titre_short', $table_name);
                    $tbl->set('titre', $table_name . '.descr');

                    if ($tbl->IS_LOOKUP) {
                        $val_is_lookup = 'Y';
                    } else {
                        $val_is_lookup = 'N';
                    }

                    $tbl->set('is_lookup', $val_is_lookup);

                    $tbl->set('titre_u', $table_name . '.single');
                    $tbl->set('key_field', 'id');
                    $tbl->set('display_field', '');
                    $tbl->set('auditable', 'N');
                    $tbl->set('sql_gen', 'N');
                    $tbl->set('id_auto_increment', 'Y');
                    $tbl->set('utf8', 'Y');
                    $tbl->set('dbengine_id', 2);
                    $tbl->ignore_initial_translation_fields = true;
                    $tbl->insert();
                    $tbl_id = $tbl->getId();
                    $tbl_new = true;
                } else {
                    $tbl_new = null;
                }
            } else {
                $tbl_id = $tbl->getId();
                $tbl_new = false;
            }
        } else {
            unset($mdl);
            $mdl = null;
            $tbl = null;
            $mdl_id = 0;
            $tbl_id = 0;
            $tbl_new = null;
        }

        return [$mdl, $tbl, $mdl_id, $tbl_id, $mdl_new, $tbl_new];
    }

    public static function pagObject($obj, $this_db_structure, $module, $table, $id_main_sh, $updateIfExists = false, $restrictToField="")
    {
        global $lang, $the_last_sql;
        $file_dir_name = dirname(__FILE__);
        $mcode = strtolower($module);
        $table_name = strtolower($table);

        list(
            $mdl,
            $tbl,
            $mdl_id,
            $tbl_id,
            $mdl_new,
            $tbl_new,
        ) = AfwUmsPagHelper::getMyModuleAndAtable($id_main_sh, $mcode, $table_name);

        if (!$mdl_id) {
            $obj->simpleError(
                "can't find or create module [$mcode] in related orgunit [$id_main_sh]."
            );
        }

        if (!$tbl_id) {
            $obj->simpleError(
                "can't find or create table [$table_name] in module [$mcode/$mdl_id]."
            );
        }
        if ($tbl_new) $restrictToField = "";
        $fld_u = 0; // updated
        if ($tbl_new or $updateIfExists) {
            $tbl->set('titre_short', $obj->transClassPlural('ar'));
            $tbl->set('titre', $obj->transClassPlural('ar'));
            $tbl->set('titre_short_s', $obj->transClassPlural('ar', true));
            $tbl->set('titre_u', $obj->transClassSingle('ar'));
            $tbl->set('titre_u_s', $obj->transClassSingle('ar', true));
            $tbl->set('titre_short_en', $obj->transClassPlural('en'));
            $tbl->set('titre_en', $obj->transClassPlural('en'));
            $tbl->set('titre_u_en', $obj->transClassSingle('en'));
            $tbl->set('key_field', $obj->getPKField());

            if ($obj->IS_LOOKUP) {
                $tbl->set('is_lookup', 'Y');
            } else {
                $tbl->set('is_lookup', 'N');
            }

            $tbl->set('display_field', $obj->DISPLAY_FIELD);
            if ($obj->VIEW or $obj->IS_VIEW) {
                $tbl->set('real_table', 'N');
            }

            if ($obj->editByStep) {
                $tbl->addRemoveInMfk(
                    'tboption_mfk',
                    $ids_to_add_arr = [5],
                    $ids_to_remove_arr = [6]
                );
            } else {
                $tbl->addRemoveInMfk(
                    'tboption_mfk',
                    $ids_to_add_arr = [6],
                    $ids_to_remove_arr = [5]
                );
            }

            // die(var_export($tbl,true));

            if ($tbl->update()) {
                $fld_u++;
            }
        }
        // else die("updateIfExists=$updateIfExists");
        if(!$restrictToField)
        {
            $tbl->createUpdateMySteps($lang);
        }
        

        $fnum = 10;

        $fld_i = 0; // inserted

        $fldObj = new Afield();
        $fldObj->where("atable_id = $tbl_id");
        $fldObj->logicDelete(true, false);
        
        // throw new AfwRuntimeException("rafik medali debugg framework the_last_sql=$the_last_sql or debugg_reason_non_update=".$fldObj->debugg_reason_non_update);
        
        foreach ($this_db_structure as $attribute => $structr) 
        {
            if((!$restrictToField) or ($restrictToField==$attribute))
            {
                $structure = AfwStructureHelper::repareMyStructure($obj, $structr, $attribute);
                list($toPag, $notToPagReason) = $obj->attributeIsToPag($attribute);

                if (!$toPag) {
                    if ($attribute == 'moduleList') {
                        die("$attribute is not to pag reason : $notToPagReason");
                    }
                }

                if ($toPag) {
                    unset($fld);
                    $fld = new Afield();

                    $fld->select('atable_id', $tbl_id);
                    $fld->select('field_name', $attribute);
                    if (!$fld->load()) {
                        $field_to_create = true;
                    } else {
                        $field_to_create = false;
                    }

                    if ($field_to_create) {
                        $fld->set('atable_id', $tbl_id);
                        $fld->set('field_name', $attribute);
                    }

                    $fld->set('avail', 'Y');
                    $fld->set('answer_module_id', '0');
                    $fld->commit();
                    if ($field_to_create or $updateIfExists) {
                        if ($structure['FGROUP']) {
                            $fgroup_id = AfieldGroup::loadByMainIndex(
                                $tbl_id,
                                $structure['FGROUP'],
                                $create_obj_if_not_found = true
                            );
                            if ($fgroup_id) {
                                $fld->set('afield_group_id', $fgroup_id);
                            }
                        }

                        //$afw.... = $structure["...."];
                        $afwType = $structure['TYPE'];
                        $afwCat = $structure['CATEGORY'];
                        $afield_type_id = self::fromAFWtoAfieldType(
                            $afwType,
                            $afwCat,
                            $structure
                        );

                        if ($afield_type_id == $afwType) {
                            die(
                                "for attribute [$attribute] can not decode afw type [$afwType] : " .
                                    var_export($obj, true)
                            );
                        }

                        $fld->set('titre',trim(strip_tags($obj->getAttributeLabel($attribute, "ar", $short = false))));
                        $fld->set('titre_short',trim(strip_tags($obj->getAttributeLabel($attribute, "ar", $short = true))));

                        $fld->set('titre_en',trim(strip_tags($obj->getAttributeLabel($attribute, "en", $short = false))));
                        $fld->set('titre_short_en',trim(strip_tags($obj->getAttributeLabel($attribute, "en", $short = true))));


                        $this_help_text_ar = $obj->translate(
                            $attribute . '_help_text',
                            'ar'
                        );
                        if ($this_help_text_ar != $attribute . '_help_text') {
                            $fld->set('help_text', $this_help_text_ar);
                        }

                        $this_help_text_en = $obj->translate(
                            $attribute . '_help_text',
                            'en'
                        );
                        if ($this_help_text_en != $attribute . '_help_text') {
                            $fld->set('help_text_en', $this_help_text_en);
                        }

                        $this_question_text_ar = $obj->translate(
                            $attribute . '_question',
                            'ar'
                        );
                        if ($this_question_text_ar != $attribute . '_question') {
                            $fld->set('question_text', $this_question_text_ar);
                        }

                        $this_question_text_en = $obj->translate(
                            $attribute . '_question',
                            'en'
                        );
                        if ($this_question_text_en != $attribute . '_question') {
                            $fld->set('question_text_en', $this_question_text_en);
                        }

                        $fld->set('afield_type_id', $afield_type_id);

                        $row = AfwStructureHelper::getStructureOf($obj,$attribute);

                        $row['atable'] = $tbl;
                        $row['obj'] = $obj;

                        $fld_att = Afield::to_afield_att($id_main_sh, $row);
                        /*
                        if($attribute=="idn")
                        {
                            unset($row["atable"]);
                            unset($row["obj"]);
                            die("row=". var_export($row,true) . " fld_att=" .var_export($fld_att,true));
                        }*/
                        foreach ($fld_att as $prop => $propvalue) {
                            $fld->set($prop, $propvalue);
                        }

                        $fld->set('field_order', $fnum);
                        $fnum += 10;

                        if ($field_to_create) {
                            $fld->set('additional', 'N');
                        }

                        if ($structure['CATEGORY']) {
                            $fld->set('reel', 'N');
                        } else {
                            $fld->set('reel', 'Y');
                        }

                        if (isset($obj->UNIQUE_KEY) and is_array($obj->UNIQUE_KEY) and (!in_array($attribute, $obj->UNIQUE_KEY))) {
                            $fld->set('distinct_for_list', 'N');
                        } else {
                            if($attribute == "original_name") throw new AfwRuntimeException("rafik-medali : obj->UNIQUE_KEY = ".var_export($obj->UNIQUE_KEY,true)." obj = ".var_export($obj,true));
                            $fld->set('distinct_for_list', 'Y');
                        }
                    }

                    if ($field_to_create) {
                        if ($fld->commit()) {
                            $fld_i++;
                        }
                    } else {
                        if ($fld->update()) {
                            $fld_u++;
                        }
                    }

                    Afield::props_to_foptions($obj, $tbl, $fld, true);
                }
            }
        }

        return [$fld_i, $fld_u];
    }

    public static final function isValueType($typeOfAttribute)
    {
        if ($typeOfAttribute == 'PK') {
            return true;
        }
        if ($typeOfAttribute == 'TEXT') {
            return true;
        }
        if ($typeOfAttribute == 'FLOAT') {
            return true;
        }
        if ($typeOfAttribute == 'INT') {
            return true;
        }
        if ($typeOfAttribute == 'TIME') {
            return true;
        }
        if ($typeOfAttribute == 'GDAT') {
            return true;
        }
        if ($typeOfAttribute == 'GDATE') {
            return true;
        }
        if ($typeOfAttribute == 'DATE') {
            return true;
        }
        if ($typeOfAttribute == 'AMOUNT') {
            return true;
        }
        if ($typeOfAttribute == 'ENUM') {
            return true;
        }
        if ($typeOfAttribute == 'MENUM') {
            return true;
        }
        return false;
    }

    public static final function isObjectType($typeOfAttribute)
    {
        if ($typeOfAttribute == 'FK') {
            return true;
        }
        if ($typeOfAttribute == 'MFK') {
            return true;
        }
        return false;
    }

    public static final function attributeIsAfwKnownOption($attribute)
    {
        if (AfwStringHelper::stringStartsWith($attribute, 'hzm_')) {
            return true;
        }
        if ($attribute == 'after_save_edit') {
            return true;
        }
        if ($attribute == 'public_display') {
            return true;
        }
        if ($attribute == 'ignore_insert_doublon') {
            return true;
        }

        if ($attribute == 'currentStep') {
            return true;
        }
        if ($attribute == 'editByStep') {
            return true;
        }
        if ($attribute == 'editNbSteps') {
            return true;
        }
        if ($attribute == 'showRetrieveErrors') {
            return true;
        }
        if ($attribute == 'forceShowRetrieveErrors') {
            return true;
        }
        if ($attribute == 'forceCheckErrors') {
            return true;
        }
        if ($attribute == 'showQeditErrors') {
            return true;
        }
        if ($attribute == 'showId') {
            return true;
        }
        if ($attribute == 'general_check_errors') {
            return true;
        }
        if ($attribute == 'copypast') {
            return true;
        }
        if ($attribute == 'OwnedBy') {
            return true;
        }

        if ($attribute == 'hirerachyField') {
            return true;
        }
        if ($attribute == 'qedit_minibox') {
            return true;
        }
        if ($attribute == 'styleStep') {
            return true;
        }

        if ($attribute == 'datatable_on_for_mode') {
            return true;
        }
        if ($attribute == 'nbQeditLinksByRow') {
            return true;
        }


        return false;
    }

    public static function fromAFWtoAfieldType($afwType, $afwCat, $structure)
    {
        $file_dir_name = dirname(__FILE__);
        // 

        if ($afwType == 'FK') {
            if ($afwCat == 'ITEMS') {
                return AfwUmsPagHelper::$afield_type_items;
            }
            return AfwUmsPagHelper::$afield_type_list;
        } elseif ($afwType == 'MFK') {
            return AfwUmsPagHelper::$afield_type_mlst;
        } elseif ($afwType == 'MENUM') {
            return AfwUmsPagHelper::$afield_type_menum;
        } elseif ($afwType == 'MTEXT') {
            return AfwUmsPagHelper::$afield_type_mtxt;
        } elseif ($afwType == 'YN') {
            return AfwUmsPagHelper::$afield_type_yn;
        } elseif ($afwType == 'TEXT') {
            if ($structure['SIZE'] == 'AREA' or $structure['SIZE'] == 'AEREA') {
                return AfwUmsPagHelper::$afield_type_mtxt;
            } else {
                return AfwUmsPagHelper::$afield_type_text;
            }
        } elseif ($afwType == 'DATE') {
            return AfwUmsPagHelper::$afield_type_date;
        } elseif ($afwType == 'GDAT') {
            return AfwUmsPagHelper::$afield_type_Gdat;
        } elseif ($afwType == 'GDATE') {
            return AfwUmsPagHelper::$afield_type_Gdat;
        } elseif ($afwType == 'DATETIME') {
            return AfwUmsPagHelper::$afield_type_Gdat;
        } elseif ($afwType == 'INT') {
            return AfwUmsPagHelper::$afield_type_int;
        } elseif ($afwType == 'BIGINT') {
            return AfwUmsPagHelper::$afield_type_bigint;
        } elseif ($afwType == 'INT') {
            return AfwUmsPagHelper::$afield_type_nmbr;
        } elseif ($afwType == 'ENUM') {
            return AfwUmsPagHelper::$afield_type_enum;
        } elseif ($afwType == 'AMNT') {
            return AfwUmsPagHelper::$afield_type_amnt;
        } elseif ($afwType == 'PCTG') {
            return AfwUmsPagHelper::$afield_type_pctg;
        } elseif ($afwType == 'TIME') {
            return AfwUmsPagHelper::$afield_type_time;
        } elseif ($afwType == 'FLOAT') {
            return AfwUmsPagHelper::$afield_type_float;
        } else {
            return $afwType;
        }
    }

    public static final function userCanDoOperationOnObject(
        $object,
        $auser,
        $operation,
        $log = true
    ) {


        if ($auser and $auser->isSuperAdmin()) {
            return true;
        }

        if ($operation == 'display' and $object->public_display) {
            return true;
        }
        if ($operation == 'search' and $object->public_display) {
            return true;
        }
        if ($operation == 'qsearch' and $object->public_display) {
            return true;
        }

        if ($operation == 'display' and $object->canBePublicDisplayed()) {
            return true;
        }
        if (
            $operation == 'display' and
            $object->canBeSpeciallyDisplayedBy($auser)
        ) {
            return true;
        }

        if ($operation == 'edit') {
            $operation_sql = 'update';
        } else {
            $operation_sql = $operation;
        }

        $table = $object->getMyTable();
        $module_code = $object->getMyModule();
        if (
            $auser and
            !$auser->iCanDoOperation($module_code, $table, $operation_sql)
        ) {
            if ($log) {
                AfwSession::contextLog(
                    "failed user_have_access_to_do_operation_on_me, user($auser)->iCanDoOperation($module_code,$table,$operation_sql) ==> false ",
                    'iCanDo'
                );
            }
            return false;
        } else {
            if ($log) {
                AfwSession::contextLog(
                    "succeeded user_have_access_to_do_operation_on_me, user($auser)->iCanDoOperation($module_code,$table,$operation_sql) ==> true ",
                    'iCanDo'
                );
            }
        }

        $return = $object->userCanDoOperationOnMe(
            $auser,
            $operation,
            $operation_sql
        );
        if (!$return) {
            if ($log) {
                AfwSession::contextLog(
                    "failed user_have_access_to_do_operation_on_me, userCanDoOperationOnMe($auser, $operation, $operation_sql) ==> $return",
                    'iCanDo'
                );
            }
        } else {
            if ($log) {
                AfwSession::contextLog(
                    "succeeded user_have_access_to_do_operation_on_me, userCanDoOperationOnMe($auser, $operation, $operation_sql) ==> $return",
                    'iCanDo'
                );
            }
        }

        return $return;
    }

    public static final function userCanNotDoOperationOnObjectReason(
        $object,
        $auser,
        $operation,
        $log = true
    ) 
    {
        $return_arr = [];
        if (!($auser and $auser->isSuperAdmin())) {
            $return_arr[] = "$auser is not SuperAdmin";
        }
        else
        {
            $return_arr[] = "return = true";
        }
        

        if (!($operation == 'display' and $object->public_display)) {
            $return_arr[] = "object and case is not public_display object=".var_export($object,true);
        }
        else
        {
            $return_arr[] = "return = true";
        }

        if (!($operation == 'search' and $object->public_display)) {
            $return_arr[] = "object and case is not public_display for search";
        }
        else
        {
            $return_arr[] = "return = true";
        }

        if (!($operation == 'qsearch' and $object->public_display)) {
            $return_arr[] = "object and case is not public_display for qsearch";
        }
        else
        {
            $return_arr[] = "return = true";
        }

        if (!($operation == 'display' and $object->canBePublicDisplayed())) {
            $return_arr[] = "object->canBePublicDisplayed return false";
        }
        else
        {
            $return_arr[] = "return = true";
        }

        if (!($operation == 'display' and $object->canBeSpeciallyDisplayedBy($auser))) {
            $return_arr[] = "object->canBeSpeciallyDisplayedBy $auser return false";
        }
        else
        {
            $return_arr[] = "return = true";
        }

        if ($operation == 'edit') {
            $operation_sql = 'update';
        } else {
            $operation_sql = $operation;
        }

        $table = $object->getMyTable();
        $module_code = $object->getMyModule();
        if ($auser and !$auser->iCanDoOperation($module_code, $table, $operation_sql)
        ) {
            $return_arr[] = "$auser => iCanDoOperation($module_code, $table, $operation_sql) return false";
            if ($log) {
                AfwSession::contextLog(
                    "failed user_have_access_to_do_operation_on_me, user($auser)->iCanDoOperation($module_code,$table,$operation_sql) ==> false ",
                    'iCanDo'
                );
            }
        } else {
            if ($log) {
                AfwSession::contextLog(
                    "succeeded user_have_access_to_do_operation_on_me, user($auser)->iCanDoOperation($module_code,$table,$operation_sql) ==> true ",
                    'iCanDo'
                );
            }
        }

        $return = $object->userCanDoOperationOnMe(
            $auser,
            $operation,
            $operation_sql
        );
        if (!$return) {
            $return_arr[] = "$auser => iCanDoOperation($module_code, $table, $operation_sql) return false";
            if ($log) {
                AfwSession::contextLog(
                    "failed user_have_access_to_do_operation_on_me, userCanDoOperationOnMe($auser, $operation, $operation_sql) ==> $return",
                    'iCanDo'
                );
            }
        } else {
            if ($log) {
                AfwSession::contextLog(
                    "succeeded user_have_access_to_do_operation_on_me, userCanDoOperationOnMe($auser, $operation, $operation_sql) ==> $return",
                    'iCanDo'
                );
            }
        }

        $return_arr[] = "return = $return";

        return implode("<br>\n",$return_arr);
    }

    public static final function getAllActions($object, $step = 0, $takeViewIcon = true)
    {
        global $images, $lang;
        $objme = AfwSession::getUserConnected();

        $cl = $object->getMyClass();
        $currmod = $object->getMyModule();

        $actions_tpl_arr = $object->getSpecificActions($step);
        // die("$object : getSpecificActions = ".var_export($actions_tpl_arr,true));
        list($editAction, $editFilename) = $object->editAction($step);
        list($displayAction, $displayFilename) = $object->displayAction($step);
        list($deleteAction, $deleteFilename) = $object->deleteAction($step);

        if (!$object->isActive()) {
            $viewIcon = 'view_off';
            $data_errors = 'سجل محذوف الكترونيا';
        } elseif (
            $object->showRetrieveErrors and
            (AfwSession::hasOption('CHECK_ERRORS') or $object->forceCheckErrors)
        ) {
            if (!$object->isOk()) {
                $viewIcon = 'view_err';
                $arr_dataErrors = $object->getDataErrors($lang);
                $data_errors = implode(' / ', $arr_dataErrors);
                if (strlen($data_errors) > 596 or count($arr_dataErrors) > 18) {
                    $data_errors = 'أخطاء كثيرة';
                    $viewIcon = 'view_error';
                }
            } else {
                $viewIcon = 'view_ok';
                $data_errors = 'لا يوجد أخطاء';
            }
        } else {
            $viewIcon = 'view_me';
            $data_errors = 'لم يتم تفعيل التثبت من الأخطاء';
            if (!$object->showRetrieveErrors) {
                $data_errors .= ' في العرض الاستردادي (retrieve mode)';
            }
        }
        if($takeViewIcon)
        {
            $actions_tpl_arr['view'] = [
                'link' => "Main_Page=$displayFilename&cl=$cl&currmod=$currmod&id=[id]&popup=[popup_t]",
                'img' => "../lib/images/$viewIcon.png",
                'framework_action' => $displayAction,
                'help' => htmlentities($data_errors),
            ];
        }
        
        $actions_tpl_arr['edit'] = [
            'link' => "Main_Page=$editFilename&cl=$cl&currmod=$currmod&id=[id]&popup=[popup_t]",
            'img' => $images['modifier'],
            'framework_action' => $editAction,
        ];
        //$actions_tpl_arr["delete"] = array("link"=>"Main_Page=$deleteFilename&cl=$cl&currmod=$currmod&id=[id]&popup=", "img"=>$images['delete'],"target"=>"_del_popup","framework_action"=>$deleteAction);
        $actions_tpl_arr['delete'] = [
            'link' => '#todo',
            'img' => $images['delete'],
            'framework_action' => $deleteAction,
            'ajax_class' => 'trash',
        ];

        return $actions_tpl_arr;
    }

    public static final function getActionsMatrix($liste_obj, $step = 0)
    {
        $actions_tpl_matrix = [];
        foreach ($liste_obj as $id_obj => $obj) {
            $actions_tpl_matrix[$id_obj] = AfwUmsPagHelper::getAllActions($obj, $step);
        }

        return $actions_tpl_matrix;
    }


    public static final function getRetrieveHeader(
        $object,
        $mode = 'display',
        $lang = 'ar',
        $all = false
    ) {
        $cols = $object->getRetrieveCols($mode, $lang, $all);

        $cols_retrieve = [];

        foreach ($cols as $nom_col) {
            $cols_retrieve[$nom_col] = $object->translate($nom_col, $lang);
        }

        return $cols_retrieve;
    }

    public static final function getExportExcelHeader($object, $lang = 'ar')
    {
        $objme = AfwSession::getUserConnected();
        $all_nom_cols = $object->getAllAttributes();

        $cols_excel = [];

        foreach ($all_nom_cols as $nom_col) {
            $desc = AfwStructureHelper::getStructureOf($object,$nom_col);

            if ($object->keyIsToDisplayForUser($nom_col, $objme)) {
                if (
                    $desc['TYPE'] == 'PK' and
                    (!isset($desc['EXCEL']) or $desc['EXCEL'])
                ) {
                    $cols_excel[$nom_col] = $object->translate($nom_col, $lang);
                } else {
                    if (
                        isset($desc['EXCEL']) && $desc['EXCEL'] or
                        !isset($desc['EXCEL']) &&
                            isset($desc['RETRIEVE']) &&
                            $desc['RETRIEVE']
                    ) {
                        $cols_excel[$nom_col] = $object->translate(
                            $nom_col,
                            $lang
                        );
                    }
                }
            } else {
                $nonexcel_cols[] = $nom_col;
            }
        }

        //$object->simpleError(var_export($nonexcel_cols,true)."<br>".var_export($cols_excel,true));

        return $cols_excel;
    }

    public static function getPluralTitle($object, $lang = 'ar', $force_from_pag = true)
    {
        if ($force_from_pag) {
            $at = AfwUmsPagHelper::getAtableObj($object::$MODULE, $object::$TABLE);
            if ($at == null) {
                return $object->transClassPlural($lang);
            }

            if ($lang == 'ar') {
                $field_pluraltitle = 'pluraltitle';
            } else {
                $field_pluraltitle = "pluraltitle_$lang";
            }

            return $at->getVal($field_pluraltitle);
        } else {
            return $object->transClassPlural($lang);
        }
    }

    public function getAllObjUsingMe($object, $action = '', $mode = '', $nbMax = 5)
    {
        //$className = AfwStringHelper::tableToClass(static::$TABLE);
        $fileName = 'fk_' . AfwStringHelper::tableToFile($object::$TABLE);
        include $fileName;

        $arr_ObjUsingMe = [];
        $count_arr_ObjUsingMe = 0;
        //AFWDebugg::log("faika-const($mode) : ");
        //AFWDebugg::log($FK_CONSTRAINTS,true);
        foreach ($FK_CONSTRAINTS
            as $fk_on_me_table => $FK_CONSTRAINT_ITEM_ARR) {
            //AFWDebugg::log("faika-arr : ");
            //AFWDebugg::log($FK_CONSTRAINT_ITEM_ARR,true);
            foreach ($FK_CONSTRAINT_ITEM_ARR
                as $fk_on_me_col => $FK_CONSTRAINT_COL_PROPS) {
                //AFWDebugg::log("faika-props : ");
                //AFWDebugg::log($FK_CONSTRAINT_COL_PROPS,true);
                //AFWDebugg::log("faika mode=$mode vs props[$action] = ".$FK_CONSTRAINT_COL_PROPS[$action]);

                if (!$action or $FK_CONSTRAINT_COL_PROPS[$action] == $mode) {
                    $limit = $nbMax - $count_arr_ObjUsingMe;
                    if ($limit > 0) {
                        $fk_className = AfwStringHelper::tableToClass($fk_on_me_table);
                        $fk_fileName = AfwStringHelper::tableToFile($fk_on_me_table);

                        //AFWDebugg::log("faika-find obj using me : $fk_fileName");

                        require_once $fk_fileName;

                        $fk_obj = new $fk_className();
                        $fk_obj->select($fk_on_me_col, $object->getId());
                        $arr_ObjUsingMe[$fk_on_me_table][$fk_on_me_col] = $fk_obj->loadMany($limit);
                        $count_arr_ObjUsingMe += count(
                            $arr_ObjUsingMe[$fk_on_me_table][$fk_on_me_col]
                        );
                    }
                }
            }
        }

        return [$arr_ObjUsingMe, $count_arr_ObjUsingMe];
    }

    public function statAllObjUsingMe($object)
    {
        global $lang;
        $objme = AfwSession::getUserConnected();
        $myAtable_id = 0;
        list($myModule, $myAtable) = $object->getThisModuleAndAtable();
        if ($myAtable) {
            $myAtable_id = $myAtable->getId();
        }

        if (!$myAtable_id) {
            throw new AfwRuntimeException(
                "can't find Atable_id for the current object, so not able to do new id refactory for the deleted object."
            );
        }

        $file_dir_name = dirname(__FILE__);
        // require_once("afield.php");

        $af = new Afield();

        $af->select('answer_table_id', $myAtable_id);
        $af->select('avail', 'Y');
        $af->select('reel', 'Y');

        $stats = [];

        $af_list = $af->loadMany();
        foreach ($af_list as $af_id => $af_item) {
            $error_mess = '';
            $fk_on_me_tab = $af_item->getTable();
            if ($fk_on_me_tab->isActive()) {
                $fk_on_me_module = $fk_on_me_tab->getModule();
                $fk_on_me_module_code = strtolower(
                    trim($fk_on_me_module->getVal('module_code'))
                );
                $fk_on_me_table = $fk_on_me_tab->valAtable_name();
                $fk_on_me_col = $af_item->valField_name();

                $fk_on_me_col_type = $af_item->valFtype();

                $fk_className = AfwStringHelper::tableToClass($fk_on_me_table);
                $fk_fileName = AfwStringHelper::tableToFile($fk_on_me_table);

                $file_dir_name_pag_module = $file_dir_name . '/../pag';
                $file_dir_name_fk_module =
                    $file_dir_name . "/../$fk_on_me_module_code";

                if (file_exists("$file_dir_name_fk_module/$fk_fileName")) {
                    require_once "$file_dir_name_fk_module/$fk_fileName";
                } elseif (
                    file_exists("$file_dir_name_pag_module/$fk_fileName")
                ) {
                    require_once "$file_dir_name_pag_module/$fk_fileName";
                } else {
                    $error_mess = "MOMKEN : An error has occured when trying to check dependency of the object you want to delete, file $fk_fileName not found in $file_dir_name_fk_module";
                    if ($objme and $objme->isSuperAdmin()) {
                        $error_mess .= "<br>  pag path : $file_dir_name_pag_module";
                        $error_mess .= "<br>  $fk_on_me_module_code path : $file_dir_name_fk_module";
                        $error_mess .= "<br>  file not exists $file_dir_name_fk_module/$fk_fileName tried also under pag path !";
                        $error_mess .= "<br>   -> fk field   : $af_item ($fk_on_me_col,$fk_on_me_col_type)";
                        $error_mess .= "<br>   -> fk module : $fk_on_me_module -> code    : $fk_on_me_module_code";

                        $error_mess .= "<br>   -> fk table   : $fk_on_me_tab";
                        $error_mess .= "<br>   -> fk class  $fk_className";
                        $error_mess .= "<br>   -> fk file name $fk_fileName";
                    }

                    if (
                        $fk_on_me_tab
                        ->getModule()
                        ->getVal('id_module_status') == 6
                    ) {
                        throw new AfwRuntimeException($error_mess);
                    }
                }

                if (!$error_mess) {
                    $fk_obj = new $fk_className();

                    if ($fk_on_me_col_type == AfwUmsPagHelper::$afield_type_list) {
                        $fk_obj->select($fk_on_me_col, $object->getId());
                    } else {
                        $this_id = $object->getId();
                        $fk_obj->where("$fk_on_me_col like '%,$this_id,%'");
                    }

                    $count_fk = $fk_obj->count();

                    if ($count_fk > 0) {
                        $stats[] = [
                            'field_name' => $fk_on_me_col,
                            'field_id' => $af_item->getId(),
                            'field_title' => $af_item->getDisplay($lang),
                            'table_id' => $af_item->valAtable_id(),
                            'table_name' => $fk_on_me_table,
                            'table_title' => $fk_on_me_tab->valDescription(),
                            'count' => $count_fk,
                        ];

                        // die(var_export($stats,true));
                    }
                } else {
                    $stats[] = [
                        'field_name' => $fk_on_me_col,
                        'field_id' => $af_item->getId(),
                        'field_title' => $af_item->getDisplay($lang),
                        'table_id' => $af_item->valAtable_id(),
                        'table_name' => $fk_on_me_table,
                        'table_title' => $fk_on_me_tab->valDescription(),
                        'count' => $error_mess,
                    ];
                }
            }
        }

        return $stats;
    }

    public function replaceAllObjUsingMeBy($object, $id_replace)
    {
        // new version
        $myAtable_id = 0;
        list($myModule, $myAtable) = $object->getThisModuleAndAtable();
        if ($myAtable) {
            $myAtable_id = $myAtable->getId();
        }

        if (!$myAtable_id) {
            throw new AfwRuntimeException(
                "can't find Atable_id for the current object, so not able to do new id refactory for the deleted object."
            );
        }

        $file_dir_name = dirname(__FILE__);
        // require_once("afield.php");

        $af = new Afield();

        $af->select('answer_table_id', $myAtable_id);
        $af->select('avail', 'Y');
        $af->select('reel', 'Y');

        $af_list = $af->loadMany();
        foreach ($af_list as $af_id => $af_item) {
            $fk_on_me_table = $af_item->getTable()->valAtable_name();
            $fk_on_me_col = $af_item->valField_name();

            $fk_on_me_col_type = $af_item->valFtype();

            $fk_className = AfwStringHelper::tableToClass($fk_on_me_table);
            $fk_fileName = AfwStringHelper::tableToFile($fk_on_me_table);

            require_once $fk_fileName;

            $fk_obj = new $fk_className();

            if ($fk_on_me_col_type == AfwUmsPagHelper::$afield_type_list) {
                $fk_obj->select($fk_on_me_col, $object->getId());
                $fk_obj->set($fk_on_me_col, $id_replace);
            } else {
                $this_id = $object->getId();
                $fk_obj->where("$fk_on_me_col like '%,$this_id,%'");
                $fk_obj->set(
                    $fk_on_me_col,
                    "REPLACE($fk_on_me_col, ',$this_id,', ',$id_replace,')"
                );
            }

            $affected_rows += $fk_obj->update(false);
        }


        return $affected_rows;
    }

    

    
}