<?php

class AfwSearchHelper
{
        public static function prepareSQLWhereFromPostedFilter(&$object, $arr_sql_conds = array(), $lang = "ar")
        {
                $cond_phrase_arr = array();
                $myClass = get_class($object);
                $class_db_structure = $object->getMyDbStructure($return_type = "structure", $attribute = "all");
                AfwSession::pullSessionVar("search-$myClass");
                //$currmod = $obj->getMyModule();
                // $newo_qedit = $object->QEDIT_MODE_NEW_OBJECTS_DEFAULT_NUMBER;
                // $newo = $newo_qedit;
                // if (!$newo) $newo = 10;
                // récupération des critères du formulaire

                if (true) {
                        $cond_phrase_arr[] = "تم البحث بالمعايير التالية : ";

                        $fixms = array();
                        // AFWDebugg::print_str('foreach  '.__LINE__);
                        foreach ($class_db_structure as $nom_col => $desc) {
                                //if((isset($desc["SEARCH"]) && $desc["SEARCH"] == "YES") || ((isset($desc["SHOW"]) &&
                                //$desc["SHOW"]) && (!isset($desc["SEARCH"]) || (isset($desc["SEARCH"]) && $desc["SEARSH"] == ""))))
                                $my_oper = $_POST["oper_" . $nom_col];;
                                if (!$my_oper) $my_oper = "=";
                                //if(($nom_col == "parent_module_id") and (!$my_oper)) die("my_oper=$my_oper, _POST[oper_$nom_col] = ".$_POST["oper_".$nom_col]." _POST = ".var_export($_POST,true));
                                $my_posted_val = $_POST[$nom_col];
                                $my_val = $my_posted_val; // $$nom_col;
                                $my_val2 = $_POST[$nom_col . '_2'];

                                $there_is_search = ((isset($my_val) && $my_val != "") or ($my_oper == "=''") or ($my_oper == "!=''"));
                                if ($there_is_search) {
                                        $where = "";
                                        $fixm = "";

                                        // die("DBG-getClauseWhere for $nom_col [$my_oper] ($my_val)");
                                        list($where, $fixm, $cond_phrase) = AfwSqlHelper::getClauseWhere($object, "me." . $nom_col, $my_oper,  $my_val, $my_val2, $lang);
                                        // if(($nom_col == "idn") and ((!$my_val) or ($my_val == "1092666765"))) die("getClauseWhere(me.$nom_col,$my_oper,$my_val,$my_val2,$lang) = list($where,$fixm,$cond_phrase)");
                                        $arr_sql_conds[] = $where;
                                        // if(($nom_col == "idn") and ((!$my_val) or ($my_val == "1092666765"))) die("1.debugg the criteria = ".var_export($arr_sql_conds,true));
                                        $cond_phrase_arr[] = $cond_phrase;
                                        if ($fixm) $fixms[] = $fixm;

                                        $new_criteria_arr = array("col" => $nom_col, "oper" => $my_oper, "val" => $my_val);

                                        AfwSession::pushIntoSessionArray("search-$myClass", $new_criteria_arr);
                                        //if($where) AFWDebugg::log(" new where $where\n");
                                        //if($fixm) AFWDebugg::log(" new fixm $fixm\n");
                                } else {
                                        // keep it only for test of criterea not working
                                        // $cond_phrase_arr[] = "no search requested for field $nom_col as oper=$my_oper, val=$my_val (posted=$my_posted_val) ";
                                }
                        }
                        // AFWDebugg::print_str('fin for each '.__LINE__);
                        // die("debugg the criteria = ".var_export($arr_sql_conds,true));

                        // search by qsearch_by_text field
                        $qsearch_by_text = $_POST["qsearch_by_text"];
                        $my_oper = "like X'%.%'";
                        if ($qsearch_by_text) {
                                $qsearch_by_text_where_arr = array();
                                $pk = $object->getPKField($add_me = 'me.');
                                $qsearch_by_text_without_spaces_and_comma = $qsearch_by_text;
                                $qsearch_by_text_without_spaces_and_comma = str_replace(' ', '', $qsearch_by_text_without_spaces_and_comma);
                                $qsearch_by_text_without_spaces_and_comma = str_replace(',', '', $qsearch_by_text_without_spaces_and_comma);
                                if ((!$object->PK_MULTIPLE) and is_numeric($qsearch_by_text_without_spaces_and_comma)) $qsearch_by_text_where_arr[] = $pk . " in ($qsearch_by_text)";
                                $qsearch_by_text_cols = AfwPrevilegeHelper::getAllTextSearchableCols($object);
                                foreach ($qsearch_by_text_cols as $nom_col) {
                                        if (AfwPrevilegeHelper::isInternalSearchableCol($object, $nom_col)) {

                                                $internal_where_arr = array();
                                                $objTempForInternalSearch = AfwStructureHelper::getEmptyObject($object, $nom_col);
                                                $internal_qsearch_by_text_cols = AfwPrevilegeHelper::getAllTextSearchableCols($objTempForInternalSearch);
                                                foreach ($internal_qsearch_by_text_cols as $nom_col_internal) {
                                                        // die("DBG-qsearch_by_text::getClauseWhere for isInternalSearchableCol $nom_col_internal [$my_oper] (qsearch_by_text=$qsearch_by_text)");
                                                        list($internal_where_col, $internal_fixm_col, $internal_cond_phrase) = AfwSqlHelper::getClauseWhere($objTempForInternalSearch, $nom_col_internal, $my_oper,  $qsearch_by_text, "", $lang);
                                                        $internal_where_arr[] = $internal_where_col;
                                                }

                                                $internal_where = "((" . implode(") or (", $internal_where_arr) . "))";
                                                $objTempForInternalSearch->where($internal_where);
                                                $objTempForInternalSearch->select_visibilite_horizontale();
                                                $objTempForInternal_ids_arr = AfwLoadHelper::loadManyIds($objTempForInternalSearch);
                                                $objTempForInternal_ids_txt = implode(",", $objTempForInternal_ids_arr);
                                                if (!$objTempForInternal_ids_txt) {
                                                        $where_col = "FALSE";
                                                        $objTempForInternal_ids_txt = "0";
                                                } else $where_col = "me." . $nom_col . " in (" . $objTempForInternal_ids_txt . ")";
                                        } else {
                                                // die("DBG-qsearch_by_text::getClauseWhere for $nom_col [$my_oper] (qsearch_by_text=$qsearch_by_text)");
                                                list($where_col, $fixm_col, $cond_phrase) = AfwSqlHelper::getClauseWhere($object, "me." . $nom_col, $my_oper,  $qsearch_by_text, "", $lang);
                                        }
                                        $qsearch_by_text_where_arr[] = $where_col;
                                }

                                //throw new AfwRun timeException("qsearch_by_text_cols = ".var_export($qsearch_by_text_cols,true)." where_arr = ".var_export($qsearch_by_text_where_arr,true));
                                if (count($qsearch_by_text_where_arr) > 0) {
                                        $where_qsearch_by = "((" . implode(") or (", $qsearch_by_text_where_arr) . "))";

                                        // die("AfwSqlHelper::deduire _where($nom_col, $desc, $my_oper, $my_val, $my_val2) = list($where,$fixm,$cond_phrase)");
                                        $arr_sql_conds[] = $where_qsearch_by;
                                        // die("2.debugg the criteria = ".var_export($arr_sql_conds,true));
                                        $cond_phrase_arr[] = $trad_qsearch_by_text = $object->translate("qsearch_by_text", $lang) . " : " . $qsearch_by_text;
                                        if ($fixm) $fixms[] = $fixm;

                                        $new_criteria_arr = array("col" => "qsearch_by_text", "oper" => $my_oper, "val" => $qsearch_by_text);

                                        AfwSession::pushIntoSessionArray("search-$myClass", $new_criteria_arr);

                                        //if($where) AFWDebugg::log(" new where $where\n");
                                        //if($fixm) AFWDebugg::log(" new fixm $fixm\n");
                                }
                        }
                }

                return [$arr_sql_conds, $cond_phrase_arr];
        }
}
