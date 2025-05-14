<?php
#####################################################################################
####################################  FONCTIONS  ####################################
#####################################################################################
function hidden_input($col_name, $desc, $val, $obj = null)
{
	$type_input_ret = "hidden";
?>
	<input type="hidden" id="<?= $col_name ?>" name="<?php echo $col_name ?>" value="<?php echo $val ?>">
	<?
	return $type_input_ret;
}


function type_input($col_name, $desc, $obj, $selected = false)
{
	global $lang, $class_inputSelect_multi_big, $class_inputInt, $class_inputText, $class_inputSelected;
	require_once('afw_rights.php');

	// $images = AfwThemeHelper::loadTheme();
	$maxlength_input = 1000;
	$class_inputSearch = "";
	$class_inputSmallSearch = "input_small_search";
	if ($selected)
		$inp_selected = $class_inputSelected;
	else  $inp_selected = "";

	$col_placeholder = "";
	if ($desc["PLACEHOLDER"]) $col_placeholder = $obj->translate($desc["PLACEHOLDER"], $lang);

	switch ($desc["TYPE"]) {
		case 'ENUM':
		case 'MENUM':
			if ($desc["ANSWER"] == "INSTANCE_FUNCTION") {
				$fkObj = AfwStructureHelper::getEnumAnswerList($obj, $col_name);
				// $obj->_error("$col_name is INSTANCE_FUNCTION answer and it has this getEnumAnswerList ".var_export($fkObj,true));
			} 
			else 
			{
				$fcol_name = $desc["FUNCTION_COL_NAME"];
                if(!$fcol_name) $fcol_name = $col_name;
				$fkObj = AfwLoadHelper::getEnumTable($desc["ANSWER"], $obj->getTableName(), $fcol_name, $obj);
			}

			if ($desc["SEARCH-BY-ONE"] and ($desc["TYPE"] == "ENUM")) {
				//if($col_name) die("_POST[$col_name] = ".$_POST[$col_name]." liste_rep = ".var_export($fkObj,true));
				select(
					$fkObj,
					array($_POST[$col_name]),
					array(
						"class" => "form-control $class_inputSearch $class_select $inp_selected",
						"name"  => $col_name,
					),
					"asc",
					true,
					""
				);
			} else {
				select(
					$fkObj,
					((isset($_POST[$col_name])) ? $_POST[$col_name] : array()),
					array(
						"class" => "form-control $class_inputSearch $class_inputSelect_multi_big $inp_selected",
						"name"  => $col_name . "[]",
						"size"  => 5,
						"multi" => true
					),
					"asc",
					false
				);
			}
			break;
		case 'FK':
		case 'MFK':
			$nom_table_fk   = $desc["ANSWER"];
			$nom_module_fk  = $desc["ANSMODULE"];
			if (!$nom_module_fk) {
				$nom_module_fk = AfwUrlManager::currentWebModule();
			}
			$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);
			// $nom_fichier_fk = AFWObject::table ToFile($nom_table_fk);


			$ans_tab_where = $obj->getSearchWhereOfAttribute($col_name);

			/*
                                if(!isset($desc["WHERE-SEARCH"])) $ans_tab_where = $desc["WHERE"];
                                else $ans_tab_where = $desc["WHERE-SEARCH"];*/

			$file_dir_name = dirname(__FILE__);
			/*
                                if($nom_module_fk)
                                {
                                     $full_file_path = $file_dir_name."/../$nom_module_fk/".$nom_fichier_fk;
                                     
                                }
                                else
                                {
                                     $full_file_path = $file_dir_name."/".$nom_fichier_fk;
                                }
                                
                                if(!file_exists($full_file_path))
                                {
                                     $obj->_error("Impossible de charger $full_file_path in type_input($col_name) for $obj");
                                }
                                
                                require_once $full_file_path;*/


			$fkObj      = new $nom_class_fk();


			$list_distinct_txt = "";
			$list_distinct_sql_in = "";
			$caluse_where = "";
			if (($desc["TYPE"] == "FK") && ($desc["DISTINCT-FOR-LIST"])) {

				$list_distinct_txt = implode("','", $obj->loadCol($col_name, true));
				if (!$list_distinct_txt) $list_distinct_txt = "0";
				$list_distinct_sql_in = " in ('$list_distinct_txt')";
				$caluse_where = $fkObj->getPKField() . $list_distinct_sql_in;
			}

			if ($ans_tab_where) {
				$ans_tab_where = $obj->decodeText($ans_tab_where);
			}

			if ($caluse_where) {
				if ($ans_tab_where) $caluse_where .= " and " . $ans_tab_where;
			} else {
				$caluse_where = $ans_tab_where;
			}

			if ($fkObj->comptageBeforeLoadMany()) {
				$list_count = AfwLoadHelper::vhGetListe($fkObj, $col_name, $obj->getTableName(), $caluse_where, $action = "count", $lang);				
			} else $list_count = 0;
			$LIMIT_INPUT_SELECT = AfwSession::config("LIMIT_INPUT_SELECT", 20);
			if (($list_count <= $LIMIT_INPUT_SELECT) and (!$desc["AUTOCOMPLETE-SEARCH"])) {
				$l_rep = AfwLoadHelper::vhGetListe($fkObj, $col_name, $obj->getTableName(), $caluse_where, $action = "default", $lang);
				$fkObj_disp = $fkObj->getDisplay($lang);
				// if($col_name=="institution_id") die("AfwLoadHelper :: vhGet Liste($fkObj_disp, '$caluse_where', objme, $action, $lang) =>".var_export($l_rep,true));
				if ($desc["SEARCH-DEFAULT"]) {
					$searchDefaultValue = explode(",", $obj->searchDefaultValue($col_name));
				} else {
					$searchDefaultValue = array();
				}

				if ($desc["SEARCH-BY-ONE"] and ($desc["TYPE"] == "FK")) {
					select(
						$l_rep,
						isset($_POST[$col_name]) ? array($_POST[$col_name]) : $searchDefaultValue,
						array(
							"class" => "form-control $class_inputSearch $class_select $inp_selected",
							"name"  => $col_name,
							"reloadfn" => AfwJsEditHelper::getJsOfReloadOf($obj, $col_name),
							"onchange" => AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name),
							"onchangefn" => AfwJsEditHelper::getJsOfOnChangeOf($obj, $col_name, $descr = "", false),
						),
						"asc",
						true,
						""
					);
				} else {
					select(
						$l_rep,
						((isset($_POST[$col_name])) ? $_POST[$col_name] : $searchDefaultValue),
						array(
							"class" => "form-control $class_inputSearch $class_inputSelect_multi_big $inp_selected",
							"name"  => $col_name . "[]",
							"size"  => 5,
							"multi" => true
						),
						"asc",
						true
					);
				}
			} else {
				if ($desc["SEARCH-BY-ONE"] and ($desc["TYPE"] == "FK")) {
					$nom_table_fk   = $desc["ANSWER"];
					$nom_module_fk  = $desc["ANSMODULE"];
					if (!$nom_module_fk) {
						$nom_module_fk = AfwUrlManager::currentWebModule();
					}
					$nom_class_fk   = AfwStringHelper::tableToClass($nom_table_fk);

					$col_name_atc = $col_name . "_atc";
					$atc_input_normal = "";

					$val = $_POST[$col_name];
					if ($val) {
						$fkObj->load($val);
						$val_display = $fkObj->getDisplay();
					} else $val_display = "";
					//$clwhere = $desc["WHERE"];
					$attp = $col_name;
					$clp = $obj->getMyClass();
					$idp = $obj->getId();
					$modp = $obj->getMyModule();

					//$clwhere = $ans_tab_where;

	?>
					<table style='width: 100%;' cellspacing='0' cellpadding='0'>
						<tr style="background-color: rgba(255, 255, 255, 0);">
							<td style="padding:0px;margin:0px;background-color: rgba(255, 255, 255, 0);"><input type="hidden" id="<?= $col_name ?>" name="<?= $col_name ?>" class="form-control inputtrescourt cl_id" value="<?= $val ?>" readonly></td>
							<td style="padding:0px;margin:0px;"><input type="text" id="<?= $col_name_atc ?>" name="<?= $col_name_atc ?>" class="form-control <?= $atc_input_normal . " " . $class_inputSearch ?>" value="<?= $val_display ?>"></td>
						</tr>
					</table>
					<script>
						$(function() {

							$("#<?= $col_name_atc ?>").autocomplete({
								source: "../lib/api/autocomplete.php?cl=<?= $nom_class_fk ?>&currmod=<?= $nom_module_fk ?>&clp=<?= $clp ?>&idp=<?= $idp ?>&modp=<?= $modp ?>&attp=<?= $attp ?>",
								minLength: 0,

								change: function(event, ui) {
									if ($("#<?= $col_name_atc ?>").val() == "") {
										$("#<?= $col_name ?>").val("");
									}
									// $("#<?= $col_name ?>").val("");
									// $("#<?= $col_name ?>").attr('class', 'inputtrescourt cl_<?= $class_icon ?>_id');
									// $("#<?= $col_name_atc ?>").attr('class', '<?= $atc_input_modified_class ?>');
								},


								select: function(event, ui) {
									//alert(ui.item.id);
									$("#<?= $col_name ?>").val(ui.item.id);
									$("#<?= $col_name ?>").attr('class', 'form-control inputtrescourt cl_id');
									$("#<?= $col_name_atc ?>").attr('class', 'form-control <?= $atc_input_normal ?>');
								},

								html: true, // optional (jquery.ui.autocomplete.html.js required)

								// optional (if other layers overlap autocomplete list)
								open: function(event, ui) {
									$(".ui-autocomplete").css("z-index", 1000);
								}
							});

						});
					</script>
				<?
				} else {
				?>
					<input type="text" class="form-control <?= trim($class_inputInt . " $class_inputSearch " . $inp_selected) ?>" name="<?php echo $col_name; ?>" value="<? echo ((isset($_POST[$col_name])) ? $_POST[$col_name] : ''); ?>" size=32 maxlength=255>
			<?
				}
			}
			break;

			/* obsolete
                case 'ANSWER' : $fkObj = AFWObject::getAnswerTable($desc["ANSWER"], $desc["MY_PK"], $desc["MY_VAL"],$ans_tab_where);
				select(
					$fkObj,
					( (isset($_POST[$col_name])) ? $_POST[$col_name]: array()  ),
					array(
						"class" => "form-control $class_inputSearch $class_inputSelect_multi_big $inp_selected",
						"name"  => $col_name."[]",
						"size"  => 5,
						"multi" => true
					),
					"asc",
					false
				);*/
			break;
		case 'PK':
		case 'TEXT':
		case 'PCTG':
		case 'INT':
		case 'AMNT':

			?> <input type="text" class="form-control <?= trim(" inputfull $class_inputSearch ") ?>" placeholder="<?php echo $col_placeholder ?>" name="<?php echo $col_name ?>" value="<? echo ((isset($_POST[$col_name])) ? $_POST[$col_name] : ''); ?>" size=32 maxlength="<?= $maxlength_input ?>">
		<?php echo $desc["UNIT"];
			//echo $desc["TITLE_AFTER"];
			break;
		case 'YN':
			$answer_list = array();
			$remove_options_arr = $desc["REMOVE_OPTIONS"];

			if (!$remove_options_arr["Y"]) $this_yes_label = $obj->showYNValueForAttribute("YES", $col_name, $lang);
			if (!$remove_options_arr["N"]) $this_no_label  = $obj->showYNValueForAttribute("NO", $col_name, $lang);
			if (!$remove_options_arr["W"]) $this_dkn_label = $obj->showYNValueForAttribute("EUH", $col_name, $lang);

			if (!$remove_options_arr["Y"]) $answer_list["Y"] = $this_yes_label;
			if (!$remove_options_arr["W"]) $answer_list["W"] = $this_dkn_label;
			if (!$remove_options_arr["N"]) $answer_list["N"] = $this_no_label;



			if (isset($desc["ANSWER"]) && !empty($desc["ANSWER"])) {
				$temp_answer_val = explode('|', $desc["ANSWER"]);
				if (count($temp_answer_val) == 3) {
					$answer_list["Y"] = $temp_answer_val[0];
					$answer_list["N"] = $temp_answer_val[1];
					$answer_list["W"] = $temp_answer_val[2];
				}
			}
			$answer_list[""] = "";

			if ($desc['MANDATORY']) unset($answer_list["W"]);  // <=  to be reviewed (ظپظٹظ‡ ظ†ط¸ط±)
			if ($desc['MANDATORY-SEARCH']) unset($answer_list["W"]);

			select(
				$answer_list,
				(isset($_POST[$col_name])) ? array($_POST[$col_name]) : array(),
				array(
					"class" => "form-control",
					"name"  => $col_name,
					"style" => $input_style,
				),
				"asc",
				false
			);
			break;
		case 'DATE':
		?>
			<table style="border: 1px silver solid;width:100%">
				<tr>
					<td style='padding:5px;'>
						من
					</td>
					<td>
						<input type="text" class="form-control <?= $class_inputSmallSearch ?>" id="<?= $col_name ?>" name="<?= $col_name ?>" value="<? echo ((isset($_POST[$col_name])) ? $_POST[$col_name] : ''); ?>"> </input>
						<script type="text/javascript">
							$('#<?= $col_name ?>').calendarsPicker({
								calendar: $.calendars.instance('UmmAlQura')
							});
						</script>
					</td>
					<td style='padding:5px;'>
						إلى
					</td>
					<td>
						<input type="text" class="form-control <?= $class_inputSmallSearch ?>" id="<?= $col_name . "_2" ?>" name="<?= $col_name . "_2" ?>" value="<? echo ((isset($_POST[$col_name . "_2"])) ? $_POST[$col_name . "_2"] : ''); ?>"> </input>
						<script type="text/javascript">
							$('#<?= $col_name . "_2" ?>').calendarsPicker({
								calendar: $.calendars.instance('UmmAlQura')
							});
						</script>
					</td>
				</tr>
			</table>
	<?php break;
		default:
			break;
	}
}
function type_oper($col_name, $desc, $obj, $selected = false)
{
	global $lang, $class_inputOper, $class_inputSelected;


	if ($selected)
		$inp_selected = $class_inputSelected;
	else  $inp_selected = "";

	$operSelected = $_POST["oper_$col_name"];

	switch ($desc["TYPE"]) {
		case 'PK':
			select(
				array(
					"in (.)"     => $obj->translate('IN', $lang, true),
					"="  => $obj->translate('EQUAL', $lang, true),
					"<"  => $obj->translate('LESS_THAN', $lang, true),
					">"  => $obj->translate('GREATER_THAN', $lang, true),
					"<=" => $obj->translate('LESS_OR_EQUAL_THAN', $lang, true),
					">=" => $obj->translate('GREATER_OR_EQUAL_THAN', $lang, true),
					"!=" => $obj->translate('NOT_EQUAL', $lang, true)
				),
				array($operSelected),
				array(
					"class" => "$class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
		case 'PCTG':
		case 'INT':
		case 'AMNT':

			select(
				array(
					"="  => $obj->translate('EQUAL', $lang, true),
					"<"  => $obj->translate('LESS_THAN', $lang, true),
					">"  => $obj->translate('GREATER_THAN', $lang, true),
					"<=" => $obj->translate('LESS_OR_EQUAL_THAN', $lang, true),
					">=" => $obj->translate('GREATER_OR_EQUAL_THAN', $lang, true),
					"!=" => $obj->translate('NOT_EQUAL', $lang, true)
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
		case 'DATE':
			select(
				array(
					"between"	=> $obj->translate('BETWEEN', $lang, true)
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
		case 'TEXT':
			if ($operSelected == "=") $operSelected = "like X'.'";
			select(
				array(
					"like X'%.%'"     => $obj->translate('CONTAIN', $lang, true),
					"like X'.%'"      => $obj->translate('BEGINS_WITH', $lang, true),
					"like X'%.'"      => $obj->translate('ENDS_WITH', $lang, true),
					"like X'.'"       => $obj->translate('EQUAL', $lang, true),
					"not like X'%.%'" => $obj->translate('NOT_CONTAIN', $lang, true),
					"not like X'%.%'" => $obj->translate('NOT_CONTAIN', $lang, true),
					"=''"             => $obj->translate('IS_EMPTY', $lang, true),
					"!=''"             => $obj->translate('IS_NOT_EMPTY', $lang, true),
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
		case 'MENUM':
		case 'MFK':
			select(
				array(
					"like '%.%'"     => $obj->translate('CONTAIN', $lang, true),
					"not like '%.%'" => $obj->translate('NOT_CONTAIN', $lang, true),
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);

			break;
		case 'FK':
		case 'ENUM':
			if ($desc["SEARCH-BY-ONE"]) {
				select(
					array(
						"="  => $obj->translate('EQUAL', $lang, true),
					),
					array($operSelected),
					array(
						"class" => "form-control $class_inputOper $inp_selected",
						"name"  => "oper_" . $col_name
					),
					"",
					false
				);
			} else {
				select(
					array(
						"in"     => $obj->translate('IN', $lang, true),
						"not in" => $obj->translate('NOT_IN', $lang, true),
					),
					array($operSelected),
					array(
						"class" => "form-control $class_inputOper $inp_selected",
						"name"  => "oper_" . $col_name
					),
					"",
					false
				);
			}
			break;
		case 'YN':
			select(
				array(
					"in"     => $obj->translate('IN', $lang, true),
					"not in" => $obj->translate('NOT_IN', $lang, true),
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
		default:
			select(
				array(
					"in"     => $obj->translate('IN', $lang, true),
					"not in" => $obj->translate('NOT_IN', $lang, true),
				),
				array($operSelected),
				array(
					"class" => "form-control $class_inputOper $inp_selected",
					"name"  => "oper_" . $col_name
				),
				"",
				false
			);
			break;
	}
}

function select($list_id_val, $selected = array(), $info = array(), $ordre = "", $null_val = true, $null_val_display = "??? ????")
{
	$lang = AfwLanguageHelper::getGlobalLanguage();
	$null_val_value = 0;
	$null_val_display = AfwLanguageHelper::translateKeyword('NULL', $lang);

	switch (strtolower($ordre)) {
		case 'asc':
			$list_val = array();
			foreach ($list_id_val as $id => $val)
				$list_val[$id] = '' . $val;
			$list_id_val = subval_sort($list_id_val, $list_val, "asc");
			break;
		case 'desc':
			$list_val = array();
			foreach ($list_id_val as $id => $val)
				$list_val[$id] = '' . $val;
			$list_id_val = subval_sort($list_id_val, $list_val, "desc");
			break;
		default:
			break;
	}
	$multi = "";
	if (isset($info["multi"]) && $info["multi"])
		$multi = " multiple";
	if (!$multi) {
		$null_val_value = "";
		$null_val_display = "";
	}
	$size = 1;
	if (isset($info["size"]))
		$size = intval($info["size"]);
	$count = count($list_id_val) + 1;
	if (!empty($multi) && $count < $size)
		$size = $count;
	if (!$info["id"]) $info["id"] = trim(trim($info["name"], "]"), "[");
	?>

	<script>
		<?php
		echo $info["reloadfn"] . "\n\n";
		echo $info["onchangefn"]."\n\n";                   
		?>
	</script>

	<select onchange="<?php echo $info["name"] ?>_onchange()" class="<?php echo $info["class"] ?>" name="<?php echo $info["name"] ?>" id="<?php echo $info["id"] ?>" <?php echo $multi ?> size=<?php echo $size ?>>
		<?php if ($null_val) {
		?> <option value="<?php echo $null_val_value ?>" <?php echo (in_array(0, $selected)) ? " selected" : ""; ?>>&nbsp;<?php echo $null_val_display ?></option>
		<?php   }
		foreach ($list_id_val as $id => $val) {
		?> <option value="<?php echo $id ?>" <?php echo (in_array($id, $selected)) ? " selected=\"selected\"" : ""; ?>><?php echo $val ?></option>
		<?php   } ?>
	</select>
	<?
	if ($multi) {
	?>
		<!-- Initialize the plugin: -->
		<script type="text/javascript">
			$(document).ready(function() {
				$('#<?php echo $info["id"] ?>').multiselect({
					inheritClass: true
				});
			});
		</script>
	<?
	}
	?>
<?php
}

function subval_sort($table_a_trie, $table_ref, $ord = "desc")
{
	$lang = AfwLanguageHelper::getGlobalLanguage();

	$res = array();
	if ($ord == "asc")
		asort($table_ref);
	else
		arsort($table_ref);
	foreach ($table_ref as $key => $val)
		$res[$key] = $table_a_trie[$key];
	return $res;
}


?>