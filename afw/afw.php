<?php

// use PhpOffice\PhpSpreadsheet\RichText\Run;

if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define(
        'PHP_VERSION_ID',
        $version[0] * 10000 + $version[1] * 100 + $version[2]
    );
}
if (!defined('STRUCTURE_IN_CACHE')) {
    define('STRUCTURE_IN_CACHE', false);
}
/**
 * File that contains the AFWObject Class
 */

// @todo
// 3. we can/need sub-modes of qedit in structure "QEDIT-[submode]"=>true and when accessed with the  qedit mode :
//    main.php?Main_Page=afw_mode_qedit.php&cl=blabla&......&submode=[submode] we only qedit these columns ...
//    in constructor we should add array $this->qedit_sub_modes_arr to define all submodes this help to auto-generate buttons/links for theses submodes
//    in getOtherLinksArray method
// 4. we can use the above to 3. to qedit afield / atable for many purposes (for example define the arole_mfk for each table of the analysed system)
// 5.

// a quoi cela sert ?
// $_SERVER["STR-OBJECTS"]	= array();

// old require of afw_root
// old require of afw_debugg
// old require of afw_ini

/**
 *
 * @descr Basic AFW Framework Class
 * @package AFW
 * @authors rafik B + karim G
 */
class AFWObject extends AFWRoot
{
    /**
     *
     * Package
     * @var string
     */

    public static $DATABASE = '____afw';
    public static $MODULE = 'afw';
    public static $TABLE = 'afw_object';
    public static $copypast = false;
    private static $mfk_separator = ',';

    private static $my_debugg_db_structure = null;

    // params


    /**
     *
     * Table name
     * @var string
     */

    protected $AUDIT_DATA = false;

    public $arr_erros = null;

    private $PK_FIELD = '';

    // since v 3.0 merged with gotItemCache it is same purpose
    // private $gotItems Cache = null;
    // private $gotItemCache = null;

    /**
     *
     * Fields values
     * @var array
     */
    private $AFIELD_VALUE = [];

    /**
     *
     * Attrib values
     * @var array
     */

    private $ATTRIB_VALUE = [];

    /**
     *
     * Options values
     * @var array
     */
    private $OPTIONS = [];

    /**
     *
     * debuggs values
     * @var array
     */
    private $debuggs = [];

    /**
     *
     * Updated Fields values
     * @var array
     */
    private $FIELDS_UPDATED = [];

    private $FIELDS_INITED = [];

    /**
     *
     * Search criteria
     * @var string
     */
    private $SEARCH = '';

    /**
     *
     * Search criteria in array
     * @var array
     */
    private $SEARCH_TAB = [];

    private $PARTITION_COLS = [];
    private $CONTEXT_COLS = [];

    /**
     *
     * List of related objects
     * @var array of AFWObject objects
     */
    public $OBJECTS_CACHE = [];

    /**
     *
     * DB_STRUCTURE
     * @var array
     */
    private $DB_STRUCTURE = [];

    /**
     *
     * UNIQUE INDEXES
     * @var array
     */
    private $U_INDEXES = [];

    /**
     *
     * MY_DEBUG
     * @var boolean
     */
    private $MY_DEBUG = false;

    /**
     *
     * IS_VIRTUAL
     * @var boolean
     */
    private $IS_VIRTUAL = false;

    public $ME_VIRTUAL = false;

    protected $tokens = null;

    public static $all_data;

    //private $force_mode = false;

    private $maj_trig_count = 0;

    public function majTriggerReset()
    {
        $this->maj_trig_count=0;
    }

    public function majTriggered()
    {
        $this->maj_trig_count++;
        if($this->maj_trig_count>50)
        {
            throw new AfwRuntimeException("Too much Update event triggered");
        }
    }
    /**
     * __construct
     * Constructor
     * @param string $table
     */
    public function __construct(
        $table = '',
        $pk_field = '',
        $database_module = '',
        $module = ''
    ) {
        if (!$module) {
            $module = $database_module;
        }
        if (!$module) {
            throw new AfwRuntimeException("no database/module defined for this class, table=$table");
        }

        // $table != "cache_system" to avoid infinite loop  (may be)

        if ($table != 'cache_system') {
            if (class_exists('AfwAutoLoader') or class_exists('AfwCacheSystem')) {
                AfwCacheSystem::getSingleton()->triggerCreation(
                    $module,
                    $table
                );
            } else {
                // throw new AfwRuntimeException("no class AfwAutoLoader and no class AfwCacheSystem");
            }
        }

        AfwMemoryHelper::checkMemoryBeforeInstanciating($this);

        $call_method = "__construct(table = $table)";

        if ($table != '') {
            $server_db_prefix = AfwSession::config('db_prefix', "default_db_");
            static::$MODULE = strtolower($module);
            static::$DATABASE = $server_db_prefix . $database_module;
            static::$TABLE = $table;
            $this->AUDIT_DATA = false;
            $this->IS_VIRTUAL = strtolower(substr(static::$TABLE, 0, 2)) == 'v_';
            //$this_db_structure = static::getDbStructure($return_type="structure", $attribute = "all");
            $this->initValues();
            $this->PK_FIELD = $pk_field;
        } else {
            throw new AfwRuntimeException('The parameter $table of constructor is not defined.');
        }

        $this->init_row($table);

        $this->general_check_errors = true;
    }

    function __destruct()
    {
        $this->optimizeMemory();
        $this->destroyData();
    }

    private static $attributeDefaultsArr = [];

    private final function attributeDefaults($table_name, $field_name)
    {
        if (self::$attributeDefaultsArr[$table_name][$field_name]) return self::$attributeDefaultsArr[$table_name][$field_name];
        $struct = AfwStructureHelper::getStructureOf($this, $field_name);
        $def_type = $struct['TYPE'];
        if ($def_type == 'MFK') {
            if(!$struct['DEFAUT']) $struct['DEFAUT'] = ',';
            $def_val = $struct['DEFAUT'];
            $def_val_force = true;
        } elseif ($def_type == 'FK') {
            if(!$struct['DEFAUT']) $struct['DEFAUT'] = 0;
            $def_val = $struct['DEFAUT'];
            $def_val_force = true;
        } elseif (
            AfwSession::config('SQL_STRICT_MODE', true) and
            ($def_type == 'TEXT' or
                $def_type == 'DATE' or
                $def_type == 'GDAT') and
            (!isset($struct['MANDATORY']) or $struct['MANDATORY']) and
            (!isset($struct['DEFAUT']))
        ) {
            if ($def_type == 'GDAT') {
                if ($struct['MANDATORY']) {
                    $def_val = date("Y-m-d H:i:s");
                    $def_val_force = true;
                } else {
                    $def_val = null;
                    $def_val_force = false;
                }
            } else {
                $def_val = '';
                $def_val_force = true;
            }
        } else {
            //if(($field_name=="active") and static::$TABLE == "bus_seat") die("struct = ".var_export($struct,true));
            $def_val = $struct['DEFAUT'];
            $def_val_force = $struct['DEFAULT_FORCE'];
        }
        self::$attributeDefaultsArr[$table_name][$field_name] = [$def_val, $def_val_force];

        return self::$attributeDefaultsArr[$table_name][$field_name];
    }

    /**
     * init_row
     * called by constructor to init state of object after creation
     */
    public final function init_row($table_name)
    {
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $field_name) {
            list($def_val, $def_val_force) = self::attributeDefaults($table_name, $field_name);
            if ($def_val || $def_val_force) {
                $this->setAfieldValue($field_name, $def_val);
                $this->setAfieldDefaultValue($field_name, $def_val);
                //if(($field_name=="active") and static::$TABLE == "bus_seat") die("this->FIELDS_INITED after setAfieldDefaultValue($field_name, $def_val) = ".var_export($this->FIELDS_INITED,true));
            }
        }

        $this->initObject();
        //if(static::$TABLE == "bus_seat") die("this->FIELDS_INITED = ".var_export($this->FIELDS_INITED,true));
    }



    public function getMyAnswerTableAndModuleFor($attribute, $struct = null)
    {
        if (!$struct) $struct = $this->getMyDbStructure($return_type = 'structure', $attribute);

        if (!$struct['ANSWER']) {
            throw new AfwRuntimeException("Missed ANSWER property for attribute $attribute : getMyDbStructure => structure = " . var_export($struct, true));
        }

        return [$struct['ANSWER'], $struct['ANSMODULE']];
    }

    public static function answerTableAndModuleFor($attribute)
    {
        $struct = self::getDbStructure($return_type = 'structure', $attribute);
        if (!$struct['ANSWER']) {
            throw new AfwRuntimeException("Missed ANSWER property for attribute $attribute : answerTableAndModuleFor => structure = " . var_export($struct, true));
        }
        return [$struct['ANSWER'], $struct['ANSMODULE']];
    }



    public static function getShortNames()
    {
        return self::getDbStructure($return_type = 'shortnames');
    }

    public static function getShortcutFields()
    {
        return self::getDbStructure($return_type = 'shortcuts');
    }

    public static function getFormulaFields()
    {
        return self::getDbStructure($return_type = 'formulas');
    }

    

    public function getMyDbStructure(
        $return_type = 'structure',
        $attribute = 'all'
    ) {
        $return = self::getDbStructure($return_type, $attribute);
        //if(!$return) die(static::$TABLE." getDbStructure($return_type, $attribute) returned empty value");
        return $return;
    }

    public static function getDbStructure(
        $return_type = 'structure',
        $attribute = 'all',
        $step = 'all',
        $start_step = null,
        $end_step = null
    ) {
        $class_name = static::class;
        $module_code = static::$MODULE;
        $table_name = static::$TABLE;
        //static::$DB_STRUCTURE

        return AfwStructureHelper::getDbStructure($module_code,
                $class_name,
                $table_name,
                $return_type,
                $attribute,
                $step,
                $start_step,
                $end_step
            );  
    }

    public function getMyOwnerId()
    {
        return $this->getOwnerId();
    }

    final public function getOwnerId()
    {
        return $this->getVal($this->fld_CREATION_USER_ID());
    }

    final public function getOwner()
    {
        if ($this->getVal($this->fld_CREATION_USER_ID()) > 0) {
            return $this->get($this->fld_CREATION_USER_ID());
        } else {
            return null;
        }
    }

    public function fld_CREATION_USER_ID()
    {
        return 'id_aut';
    }

    public function fld_CREATION_DATE()
    {
        return 'date_aut';
    }

    public function fld_UPDATE_USER_ID()
    {
        return 'id_mod';
    }

    public function fld_UPDATE_DATE()
    {
        return 'date_mod';
    }

    public function fld_VALIDATION_USER_ID()
    {
        return 'id_valid';
    }

    public function fld_VALIDATION_DATE()
    {
        return 'date_valid';
    }

    public function fld_VERSION()
    {
        return 'version';
    }

    public function fld_ACTIVE()
    {
        return 'avail';
    }

    public function loadablePropsBy($user)
    {
        return [];
    }

    protected function attributeCanBeEditedBy($attribute, $user, $desc)
    {
        // this method can be orverriden in sub-classes
        // write here your cases
        // ...
        // return type is : array($can, $reason)
        return [true, ''];
    }

    // attribute can be modified by user in some specific context
    public final function attributeCanBeUpdatedBy($attribute, $user, $desc)
    {
        list($can, $reason) = $this->attributeCanBeEditedBy($attribute, $user, $desc);
        if (!$can) return [$can, $reason];

        // but keep that by default we should use standard HZM-UMS model
        return AfwStructureHelper::attributeCanBeModifiedBy($this, $attribute, $user, $desc);
    }

    public function getModuleServer()
    {
        $this_module = static::$MODULE;
        return AfwSession::config("$this_module-server", '');
    }

    public static function myModuleServer()
    {
        $this_module = static::$MODULE;
        return AfwSession::config("$this_module-server", '');
    }

    public function getProjectLinkName()
    {
        $this_module_server = $this->getModuleServer();
        return 'server' . $this_module_server;
    }

    public static final function executeQuery($sql_query, $throw_error = true, $throw_analysis_crash = true)
    {
        /*
        if(AfwStringHelper::stringStartsWith($sql_query,"delete from"))
        {
            throw new AfwRuntimeException("delete from is being executed");
        }*/
        $module_server = self::myModuleServer();
        return AfwSqlHelper::executeQuery($module_server, static::$MODULE, static::$TABLE, $sql_query, $throw_error, $throw_analysis_crash);
    }



    protected function debuggTableQueries($sql)
    {
        return false;
    }

    protected function tableQueried($sql, $row_count, $affected_row_count)
    {
        if ($this->debuggTableQueries($sql)) die("tableQueried : $sql, row_count=$row_count, affected_row_count=$affected_row_count");
        // or @toReImplement in subclasses
    }

    public final function execQuery($sql_query, $throw_error = true, $throw_analysis_crash = true)
    {
        $module_server = $this->getModuleServer();
        list($result, $row_count, $affected_row_count) = AfwSqlHelper::executeQuery($module_server, static::$MODULE, static::$TABLE, $sql_query, $throw_error, $throw_analysis_crash);

        $this->debugg_sql_query = $sql_query;
        $this->debugg_affected_row_count = $affected_row_count;
        $this->debugg_row_count = $row_count;
        $this->tableQueried($sql_query, $row_count, $affected_row_count);
        return $result;
    }


    /**
     * _affected_rows
     * Return number of affected rows
     */
    public static function _affected_rows($project_link_name)
    {
        return AfwMysql::affected_rows(AfwDatabase::getLinkByName($project_link_name));
    }

    public static final function getDatabase()
    {
        // $origin = "static::DATABASE";
        $my_database = static::$DATABASE;
        if (!$my_database) {
            $my_modue = static::$MODULE;
            $server_db_prefix = AfwSession::config('db_prefix', "default_db_");
            // $origin = "server_db_prefix.my_modue";
            $my_database = $server_db_prefix . $my_modue;
        }

        // return array($my_database, $origin);
        return $my_database;
    }

    /**
     * _prefix_table
     * add database name as prefix to table
     * @param string $table
     */
    public static final function _prefix_table($table)
    {
        if (strpos($table, '.') === false) {
            $dbse = static::getDatabase();
            //list($dbse, $ooo) = $this->getDatabase();
            $return =  $dbse . '.' . $table;
        } else {
            $return = $table;
        }
        /*
        if($table == "date_system")
        {
            throw new AfwRuntimeException("_prefix_table($table) > (db=$dbse) (ooo=$ooo) => $return this=>".var_export($this,true)." static::DATABASE = ".static::$DATABASE);
        }*/
        return $return;
    }

    /**
     *
     * setMyDebugg
     * Set MY_DEBUG to true or false
     * @param boolean $bool
     */
    public function setMyDebugg($bool)
    {
        $this->MY_DEBUG = $bool ? true : false;
    }



    public function repareExistingObjectForEdit()
    {
        return true;
    }

    public function prepareNewObjectForEdit()
    {
        return true;
    }

    public function prepareUnset()
    {
        global $nb_instances;
        if ($nb_instances > 0) {
            $nb_instances--;
        }
    }

    public function reallyUpdated($ignoreActive = false)
    {
        $tmpArr = $this->FIELDS_UPDATED;
        if ($ignoreActive) {
            unset($tmpArr[$this->fld_ACTIVE()]);
        }
        unset($tmpArr[$this->fld_VERSION()]);
        unset($tmpArr[$this->fld_UPDATE_DATE()]);
        unset($tmpArr[$this->fld_UPDATE_USER_ID()]);

        if (count($tmpArr) > 0) {
            $tmpArrKeys = array_keys($tmpArr);
            return implode('-', $tmpArrKeys);
        }

        return '';
    }

    public function activate($commit = true, $only_me = true)
    {
        if (AfwStructureHelper::fieldExists($this, $this->fld_ACTIVE())) {
            $this->set($this->fld_ACTIVE(), 'Y');
            if ($commit) {
                return $this->update($only_me);
            }

            return -1;
        }

        return -2;
    }

    public static function logicDeleteWhere($where_clause, $sets_arr = [])
    {
        $obj = new static();
        return $obj->logicDelete(
            $commit = true,
            $only_me = false,
            $where_clause,
            $sets_arr
        );
    }

    public function logicDelete(
        $commit = true,
        $only_me = true,
        $where_clause = '',
        $sets_arr = []
    ) {
        //if((static::$TABLE=="user_story") and (!$only_me)) throw new AfwRuntimeException("this->FIELDS_UPDATED = ".var_export($this->FIELDS_UPDATED,true));
        if (!$only_me) {
            foreach ($sets_arr as $col_name => $col_value) {
                $this->set($col_name, $col_value);
            }

            if ($where_clause) {
                $this->where($where_clause);
            }
        }
        if (AfwStructureHelper::fieldExists($this, $this->fld_ACTIVE())) {
            $this->set($this->fld_ACTIVE(), 'N');

            if ($commit) {

                return $this->update($only_me);
            }
        } else {
            throw new AfwRuntimeException("call to logicDelete without define an 'active' field in structure.");
        }

        return 0;
    }

    /**
     * isActive
     * check if the current record is ACTIVE or not
     */
    public function isActive()
    {
        if (AfwStructureHelper::fieldExists($this, $this->fld_ACTIVE())) {
            return $this->is($this->fld_ACTIVE(), false);
        } else {
            return true;
        }
    }

    /**
     * isTechField
     * check if the attribute parameter is a technical field or not
     * technical field is updated automically with AFW Framework no need (and no effect) to manage its update by developer
     * @param string $attribute
     */

    public function isTechField($attribute)
    {
        return $attribute == $this->fld_CREATION_USER_ID() or
            $attribute == $this->fld_CREATION_DATE() or
            $attribute == $this->fld_UPDATE_USER_ID() or
            $attribute == $this->fld_UPDATE_DATE() or
            /*($attribute==$this->fld_VALIDATION_USER_ID()) or
             ($attribute==$this->fld_VALIDATION_DATE()) or   rafik : 4/7/2021 validation fields update is not managed by AFW */

            $attribute == $this->fld_VERSION();
    }

    public function isAdminField($attribute)
    {
        if (
            $attribute == 'sci_id' or
            $attribute == 'display_groups_mfk' or
            $attribute == 'delete_groups_mfk' or
            $attribute == 'update_groups_mfk' or
            $attribute == 'tech_notes'
        ) {
            return true;
        }

        return false;
    }

    public function isSystemField($attribute)
    {
        return $attribute == $this->fld_ACTIVE() or
            $attribute == 'draft';
        // it is mistake to consider lookup_code as system field as major tables doenst contain it and it should be paggable
        // $attribute == 'lookup_code' or    
    }



    /**
     * liste
     * return an array of IDs
     */
    public function liste($throw_error = true, $throw_analysis_crash = true)
    {
        $query =
            'select ' .
            $this->getPKField() .
            "\n from " .
            self::_prefix_table(static::$TABLE) .
            " me\n where 1" .
            $this->SEARCH;
        $this->clearSelect();
        $module_server = $this->getModuleServer();
        $rows = AfwDatabase::db_recup_rows(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );
        $return = [];
        foreach ($rows as $row) {
            $return[] = $row[$this->getPKField()];
        }
        return $return;
    }

    public static function aggreg(
        $function,
        $where = '1',
        $group_by = '',
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        $obj = new static();
        $obj->where($where);
        return AfwSqlHelper::aggregFunction($obj, $function, $group_by, $throw_error, $throw_analysis_crash);
    }

    public static function loadRecords($where, $limit = '', $order_by = '')
    {
        $obj = new static();
        $obj->where($where);
        return $obj->loadMany($limit, $order_by);
    }

    public static function sqlRecupIndexedRows($sql, $indexKey)
    {
        $module_server = self::myModuleServer();
        $rows = AfwDatabase::db_recup_rows(
            $sql,
            true,
            true,
            $module_server
        );

        $indexedRowsArr = [];

        foreach ($rows as $row) {
            $indexedRowsArr[$row[$indexKey]] = $row;
        }

        return $indexedRowsArr;
    }

    public static function sqlRecupRows($sql)
    {
        $module_server = self::myModuleServer();
        return AfwDatabase::db_recup_rows(
            $sql,
            true,
            true,
            $module_server
        );
    }



    /**
     * loadVirtualRow
     * load virtual row
     * @param array $search_tab
     */
    protected function loadVirtualRow()
    {
        return '';
    }

    public function getMyCode($not_defined_code = 'not_defined_code')
    {
        if (!$this->OBJECT_CODE) {
            return $not_defined_code;
        }
        return $this->getVal($this->OBJECT_CODE);
    }


    /**
     * @param AFWObject $obj
     * @param array $avoid_if_filled_fields
     * 
     * syncSameFieldsWith :
     *   I (this) take from him (obj) only what I need
     *   but after
     *   He (obj) take from me (this) all my fields 
     *   except primary key and unique index columns and filled columns if they are specified in $avoid_if_filled_fields
     *   so (this) is the master
     */


    public function syncSameFieldsWith($obj, $commit_obj = true, $commit_this = false, $avoid_if_filled_fields = [])
    {
        $logActive = true;
        $exception_fields = null;
        // I take from him only what I need (all fields not filled except primary key and unique index columns);
        $fields1 = $this->copyDataFrom($obj, $exception_fields, $avoid_if_filled_fields0 = "all", true, $logActive);
        // and after he take from me all my fields (except primary key and unique index columns)
        $fields0 = $obj->copyDataFrom($this, $exception_fields, $avoid_if_filled_fields, true, $logActive);
        /*
        if($obj->id == '1114138306')
        {
            die("rafik for 1114138306 : avoid_if_filled_fields = ".var_export($avoid_if_filled_fields,true)." fields0 = ".var_export($fields0,true));
        }*/

        if ($commit_obj) $obj->commit();
        if ($commit_this) $this->commit();

        if (in_array("xxxxxx", $fields1) or (trim($this->getVal("lastname")) == "-----xxxx")) {
            die("syncSameFieldsWith :::: obj=" . var_export($obj, true) .
                "<br>\n<br>\n<br>\n<br>\n<br>\n this=" . var_export($this, true) .
                "<br>\n<br>\n<br>\n<br>\n<br>\n fields0=" . var_export($fields0, true) .
                "<br>\n<br>\n<br>\n<br>\n<br>\n fields1=" . var_export($fields1, true));
        }

        return [$fields1, $fields0];
    }

    public function copyDataFrom(
        $obj,
        $exception_fields = null,
        $avoid_if_filled_fields = [],
        $avoid_unique_index = true,
        $logHistory = false
    ) {
        $this_class = get_class($this);
        if($this_class == "Student")
        {
            $field_name_to_debugg = "firstname";
        }
        else
        {
            $field_name_to_debugg = "xxxx";
        }
        
        $fields_updated = [];
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $field_name) {
            list($is_category_field, $is_settable) = AfwStructureHelper::isSettable(
                $this,
                $field_name
            );
            if (
                $this->getPKField() != $field_name and
                $is_settable and
                !$exception_fields[$field_name]
            ) {
                $ex_u_i = false;
                if ($avoid_unique_index) {
                    if (in_array($field_name, $this->UNIQUE_KEY)) {
                        $ex_u_i = true;
                    }
                }
                if (!$ex_u_i) {
                    $old_val = $this->getVal($field_name);
                    if($old_val == "--") $old_val = "";
                    $erase_even_if_filled = (($avoid_if_filled_fields != "all") and (!$avoid_if_filled_fields[$field_name]));

                    if ((!$old_val) or $erase_even_if_filled) {
                        if ($old_val) {
                            $erase_even_if_filled_log = ($avoid_if_filled_fields != "all") ? "old_val=$old_val erase_even_if_filled=$erase_even_if_filled because " . var_export($avoid_if_filled_fields, true) . "=> $field_name is not to avoid if filled" : "strange : all is to avoid if filled and $field_name is filled";
                        } else $erase_even_if_filled_log = "NO-OLD-VAL";


                        $val = $obj->getVal($field_name);
                        if (($val and ($val !== $old_val)) or (!$old_val)) {
                            $this->setForce($field_name, null);
                            $this->setForce($field_name, $val);
                            if (!$logHistory) $fields_updated[] = $field_name;
                            else $fields_updated[] = $field_name . " was '$old_val' become '$val' explanation : $erase_even_if_filled_log";
                        } else if ($field_name == $field_name_to_debugg) die("val=$val empty or same as old_val=$old_val ");
                    } else if ($field_name == $field_name_to_debugg) die("old_val=$old_val is filled or $field_name is in avoid_if_filled_fields=" . var_export($avoid_if_filled_fields, true) . " ??");
                } else if ($field_name == $field_name_to_debugg) die("$field_name_to_debugg is in UNIQUE_KEY");
            } else {
                if ($field_name == $field_name_to_debugg) die("is_settable=$is_settable except_fields[$field_name]=" . $exception_fields[$field_name]);
            }
        }

        //$this->set($this->fld_VERSION(), -1);
        // rafik : not clear why this below
        //$this->set($this->fld_VERSION(), 1);

        return $fields_updated;
    }

    public function resetAsCopy($field_vals = [])
    {
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $field_name) {
            if ($field_name != $this->getPKField()) {
                $val = $this->getVal($field_name);
                $this->set($field_name, null);
                $this->set($field_name, $val);
            }
        }

        foreach ($field_vals as $field_name => $val) {
            $this->set($field_name, $val);
        }
        $this->authorize_empty_of_id = true;
        $this->set($this->getPKField(), '');

        //$this->set($this->fld_VERSION(), -1);
        $this->set($this->fld_VERSION(), 1);
    }

    // to be overridden if need to init some fields after instanciation
    protected function initObject()
    {
        return true;
    }

    public function afterLoad()
    {
        return true;
    }



    public final function isAfieldValueSetted($field_name)
    {
        return isset($this->AFIELD_VALUE[$field_name]);
    }

    public final function getAfieldValue($field_name)
    {
        return $this->AFIELD_VALUE[$field_name];
    }

    private function deleteAfieldValues()
    {
        unset($this->AFIELD_VALUE);
        $this->AFIELD_VALUE = [];
    }

    public function getAllfieldValues()
    {
        // if(!isset($this->AFIELD _VALUE)) $this->AFIELD _VALUE = array();
        return $this->AFIELD_VALUE;
    }

    public function getAllfieldsToInsert()
    {
        $return = array_merge($this->FIELDS_INITED, $this->FIELDS_UPDATED);

        return $return;
    }

    /*
    private function isAfieldDefaultValueSetted($field_name)
    {
        return isset($this->FIELDS_INITED[$field_name]);
    }*/

    private function setAfieldDefaultValue($field_name, $value, $reset = false)
    {

        /* if (static::$TABLE == "period") {
            if (($field_name == "validated_at") and (!$value)) throw new AfwRuntimeException("rafik dbg : $field_name inited as = [$value] into " . static::$TABLE);
        }*/

        $this->FIELDS_INITED[$field_name] = $value;
        $this->setAfieldValue($field_name, $value);
        return $value;
    }

    public function getAfieldDefaultValue($field_name)
    {
        return $this->FIELDS_INITED[$field_name];
    }

    public function unsetAfieldDefaultValue($field_name)
    {
        unset($this->FIELDS_INITED[$field_name]);
    }
    /*
    private function deleteAfieldDefaultValues()
    {
        unset($this->FIELDS_INITED);
        $this->FIELDS_INITED = [];
    }*/

    public function getAllfieldDefaultValues()
    {
        // if(!isset($this->AFIELD _VALUE)) $this->AFIELD _VALUE = array();
        return $this->FIELDS_INITED;
    }



    protected function getSpecialWhereOfAttribute($field_name)
    {
        return '';
    }

    protected function getSpecialSearchWhereOfAttribute($field_name)
    {
        return '';
    }

    final public function getSearchWhereOfAttribute($field_name)
    {
        $where_att = $this->getSpecialSearchWhereOfAttribute($field_name);

        if (!$where_att) {
            $desc = AfwStructureHelper::getStructureOf($this, $field_name);
            if (!isset($desc['WHERE-SEARCH'])) {
                $where_att = $this->getWhereOfAttribute($field_name);
            } else {
                $where_att = $desc['WHERE-SEARCH'];
            }
        }

        $where_att = $this->decodeText($where_att);
        return $where_att;
    }

    final public function getWhereOfAttribute($field_name)
    {
        $where_att = $this->getSpecialWhereOfAttribute($field_name);
        if (!$where_att) {
            $struct = AfwStructureHelper::getStructureOf($this, $field_name);
            $where_att = $struct['WHERE'];
        }

        $where_att = $this->decodeText($where_att);
        return $where_att;
    }

    public function tryToLoadWithUniqueKeyForEditMode()
    {
        // if the PK is same that Unique key no need to load with unique key because the query will be same twice
        // in this case override this method to return false
        return true;
    }

    public static function loadBrotherWithUniqueKey($ukey_array)
    {
        $obj = new static();
        if($obj->loadWithUniqueKey($ukey_array)) return $obj;
        else return null;
    }

    public function loadWithUniqueKey($ukey_array)
    {
        foreach ($ukey_array as $ukey => $ukey_value) {
            $this->select($ukey, $ukey_value);
        }
        $this->test_rafik = true;
        $loaded = $this->load();

        return $loaded;
    }

    public function getTheLoadByIndex()
    {
        $loadByIndex = null;

        if (is_array($this->UNIQUE_KEY) and count($this->UNIQUE_KEY) > 0) {
            $uk_val_arr = [];
            $isLoadByIndex = true;
            foreach ($this->UNIQUE_KEY as $key_item) {
                if (!isset($this->SEARCH_TAB[$key_item])) {
                    $isLoadByIndex = false;
                } else {
                    $uk_val_arr[] = $this->SEARCH_TAB[$key_item];
                }
            }

            if ($isLoadByIndex) {
                $loadByIndex = implode('-/-', $uk_val_arr);
            }
            // if(($className=="TravelHotel") and (!$value)) throw new AfwRuntimeException("loadByIndex=$loadByIndex this->SEARCH_TAB = ".var_export($this->SEARCH_TAB,true));
        }

        return $loadByIndex;
    }

    public function loadMeFromRow($result_row)
    {
        return $this->load('', $result_row, '');
    }

    public function getSQLMany(
        $pk_field = '',
        $limit = '',
        $order_by = '',
        $optim = true,
        $eager_joins = false
    ) {
        return AfwSqlHelper::getSQLMany($this, $pk_field, $limit, $order_by, $optim, $eager_joins);
    }


    public function comptageBeforeLoadMany()
    {
        return false;
    }


    /**
     * Rafik 10/6/2021 : use joins on lookup tables and any answer table of FK retrieved field
     * to avoid if for example it load 1000 objects to load each 1000 FK attribute object each one by separated SQl query
     * which make heavy the script, to be used only when needed because it increase memory loaded by those many objects as it load
     * FK objects as eager
     */

    public function loadManyEager(
        $limit = '',
        $order_by = '',
        $optim = true,
        $result_rows = null,
        $query_special = null
    ) {
        return $this->loadMany(
            $limit,
            $order_by,
            $optim,
            $result_rows,
            $query_special,
            $eager_joins = true
        );
    }

    /**
     * loadMany
     * Load into an array of objects returned rows
     * @param AFWObject $object
     * @param string $limit : Optional add limit to query
     * @param string $order_by : Optional add order by to query
     * $optim=true param obsolete here to remove when we develop the AfwLoaderService that extends AfwService
     */
    public function loadMany(
        $limit = '',
        $order_by = '',
        $optim = true,
        $result_rows = null,
        $query_special = null,
        $eager_joins = false
    ) {
        return AfwLoadHelper::loadMany(
            $this,
            $limit,
            $order_by,
            $optim,
            $result_rows,
            $query_special,
            $eager_joins
        );
    }

    /**
     * loadListe
     * Load into an array of values returned rows
     * @param string $limit : Optional add limit to query
     * @param string $order_by : Optional add order by to query
     */
    public function loadListe($limit = '', $order_by = '')
    {
        return AfwLoadHelper::loadListe($this, $limit, $order_by);
    }

    /**
     * loadCol
     * @param string  $col_name
     * @param boolean $distinct
     * @param string  $limit : Optional add limit to query
     * @param string  $order_by : Optional add order by to query
     */
    public function loadCol(
        $object,
        $col_name,
        $distinct = false,
        $limit = '',
        $order_by = ''
    ) {
        return AfwLoadHelper::loadCol($this, $col_name, $distinct, $limit, $order_by);
    }

    protected function considerEmpty()
    {
        return false;
    }

    public function isConsideredEmpty()
    {
        return $this->isEmpty() or $this->considerEmpty();
    }

    public function isEmpty()
    {
        return !$this->getId();
    }

    public function setPKField($pk_field)
    {
        list($pk_field_prefix, $pk_field_column) = explode('.', $pk_field);
        if (!$pk_field_column) {
            $pk_field_column = $pk_field_prefix;
        }
        $this->PK_FIELD = $pk_field_column;
    }

    public function inMultiplePK($field_name)
    {
        foreach ($this->PK_MULTIPLE_ARR as $pk_field) {
            if ($pk_field == $field_name) {
                return true;
            }
        }
        return false;
    }

    public function setMultiplePK($pk_fields, $sep = '-')
    {
        if (($this->PK_MULTIPLE == "+") and ($sep == '-')) AfwStructureHelper::dd("strange setMultiplePK sep=+ to sep=- again. why ?");
        $this->PK_MULTIPLE = $sep;
        $pk_multiple_arr = explode(',', $pk_fields);
        foreach ($pk_multiple_arr as $pk_col_order => $pk_col) {
            $pk_multiple_arr[$pk_col_order] = trim($pk_col);
        }

        $this->PK_MULTIPLE_ARR = $pk_multiple_arr;
        if (!$this->ORDER_BY_FIELDS) {
            $this->ORDER_BY_FIELDS = $pk_fields;
        }
    }


    public function getVirtualPKField()
    {
        if ($this->PK_MULTIPLE) {
            return implode('.', $this->PK_MULTIPLE_ARR);
        } else {
            return $this->getPKField();
        }
    }

    public function getPKIsMultiple()
    {
        return $this->PK_MULTIPLE;
    }

    public function getPKField($add_me = '')
    {
        if (!$this->PK_FIELD and !$this->PK_MULTIPLE) {
            $return = $add_me . "id";
        } elseif ($this->PK_FIELD and !$this->PK_MULTIPLE) {
            $return = $add_me . $this->PK_FIELD;
        } elseif ($this->PK_MULTIPLE) {
            if ($this->PK_MULTIPLE === true) {
                $sep = '-';
            } else {
                $sep = $this->PK_MULTIPLE;
            }
            $pk_arr = $this->PK_MULTIPLE_ARR;
            foreach ($pk_arr as $pki => $pk_item) {
                $pk_arr[$pki] = $add_me . $pk_arr[$pki];
            }

            $return = 'concat(' . implode(",'$sep',", $pk_arr) . ')';
        }

        /*if(static::$TABLE=="application_desire")
        {
            die("For This debugged table pk_field=$return this=".var_export($this, true));
        }*/

        return $return;
    }

    public function getOrderByFields($join = true)
    {
        if ($join) {
            $prefix_me = 'me.';
        } else {
            $prefix_me = '';
        }
        if (!$this->ORDER_BY_FIELDS) {
            return $this->getDefaultOrderByFields($join);
        } else {
            $arrOrderBy = explode(',', $this->ORDER_BY_FIELDS);
            $sentenceOrderByArr = [];
            foreach ($arrOrderBy as $itemOrderBy) {
                $sentenceOrderByArr[] = $prefix_me . trim($itemOrderBy);
            }

            return implode(', ', $sentenceOrderByArr);
        }
    }

    public function getDefaultOrderByFields($join = true)
    {
        if ($join) {
            $prefix_me = 'me.';
        } else {
            $prefix_me = '';
        }
        if ($this->PK_MULTIPLE) {
            return $prefix_me .
                implode(', ' . $prefix_me, $this->PK_MULTIPLE_ARR);
        } else {
            $all_real_fields = AfwStructureHelper::getAllRealFields($this);
            return isset($all_real_fields[1])
                ? $prefix_me . $all_real_fields[1]
                : $prefix_me . $this->getPKField();
        }
    }

    /**
     * getId
     * Return the first field's value
     */
    public function getId()
    {
        /*
        global $boucle_inf, $boucle_inf_arr;
        
        if(!$boucle_inf)
        {
           $boucle_inf = 0;
           $boucle_inf_arr = array();
        }
        $this_getId = $this->getAfieldValue($this->getPKField());
        $this_table = $this->getTableName();
        $boucle_inf_arr[$boucle_inf] = "getId from object [$this_table,$this_getId]";
        $boucle_inf++;

        
        if($boucle_inf > 10000)
        {
              throw new AfwRuntimeException("heavy page halted after $boucle_inf enter to getId() method in one request, ".var_export($boucle_inf_arr,true));
        } */

        if ($this->PK_MULTIPLE) {
            $pk_val_arr = [];
            // $all_null = true;
            foreach ($this->PK_MULTIPLE_ARR as $pk_col) {
                $pk_val_i = $this->getAfieldValue($pk_col);
                $pk_val_arr[] = $pk_val_i;
                // if($pk_val_i) $all_null = false;
            }
            /*
            if((!$all_null) and count($this->PK_MULTIPLE_ARR)==8) 
            {
                die("pk_val_arr = ".var_export($pk_val_arr,true)."<br> MPK = ".var_export($this->PK_MULTIPLE_ARR,true)."<br> FVAL = ".var_export($this->getAllfieldValues(),true));
            }    
            */
            if ($this->PK_MULTIPLE === true) {
                $sep = '-';
            } else {
                $sep = $this->PK_MULTIPLE;
            }
            return implode($sep, $pk_val_arr);
        } else {
            //if(static::$TABLE == "auser") $this->lightSafeDie("get ID : this->getAfieldValue(".$this->getPKField().") = ".$this->getAfieldValue($this->getPKField()) ." this->getAllfieldValues() = ".var_export($this->getAllfieldValues(),true));
            return $this->getAfieldValue($this->getPKField());
        }
    }

    /**
     * setId
     * Set the first field's value
     * @param string $value
     */
    public function setId($value)
    {
        if ($this->PK_MULTIPLE) {
            if ($this->PK_MULTIPLE === true) {
                $sep = '-';
            } else {
                $sep = $this->PK_MULTIPLE;
            }
            $pk_val_arr = explode($sep, $value);
            foreach ($this->PK_MULTIPLE_ARR as $pk_col_order => $pk_col) {
                $this->set($pk_col, $pk_val_arr[$pk_col_order]);
            }
        } else {
            $this->setAfieldValue($this->getPKField(), $value);
            $this->setAfieldDefaultValue($this->getPKField(), $value);
        }
    }

    public function cest($attribute)
    {
        // faster but does not work with shortcuts and formulas
        $value = $this->getVal($attribute);

        return $value == 'Y';
    }

    public function isNot($attribute)
    {
        // faster but does not work with shortcuts and formulas
        $value = $this->getVal($attribute);

        return $value == 'N';
    }

    public function sureIs($attribute)
    {
        // work with shortcuts
        return $this->is($attribute, false);
    }

    public function mayBe($attribute)
    {
        return $this->is($attribute);
    }

    public function may($attribute)
    {
        return $this->mayBe($attribute);
    }

    /**
     * is
     * Return true if Y / false if N / W if W
     * @param string $attribute
     */
    public function is($attribute, $w = true, $struct = null)
    {
        // work with shortcuts and shortnames
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        if (!$struct) {
            $def_val = null;
            $open_options = false;
        } else {
            $def_val = $struct['DEFAULT'];
            $open_options = $struct['OPEN_OPTIONS']; // means field YN can contain other choices than Y,N,W so then all other options will be conisdered here like W
        }
        $stored_val = $this->getVal($attribute);
        // if($struct["CATEGORY"]) $stored_val = $this->calc($attribute);
        // if($attribute=="refresh_needed") die("attribute=$attribute, [$stored_val] =  this->getVal($attribute) ");
        $value = $stored_val;
        if (!$value) {
            $value = $def_val;
        }

        if ($value == 'Y') {
            return true;
        } elseif ($value == 'N') {
            return false;
        } elseif ($value == 'W') {
            return $w;
        } elseif (!$value or $open_options) {
            return $w;
        }
        throw new AfwRuntimeException('can not check attribute ' . $attribute . ' with value ' . $value . " in method is(), stored_val=$stored_val, def_val=$def_val.");
    }

    /**
     * load
     * Load into object a specified row
     * @param string $value : Optional, specify the value of primary key
     */
    public function load($value = '', $result_row = '', $order_by_sentence = '', $optim_lookup = true)
    {
        return AfwLoadHelper::loadAfwObject($this, $value, $result_row, $order_by_sentence, $optim_lookup);
    }

    /**
     * @return AFWRelation
     * 
     */

    public function getRelation($attribute, $struct = null)
    {
        $attribute_old = $attribute;
        $attribute = AfwStructureHelper::shortNameToAttributeName($this, $attribute_old);
        // die("attribute_old=$attribute_old, $attribute = $attribute_old");

        if (!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);

        return new AFWRelation(
            $struct['ANSMODULE'],
            $struct['ANSWER'],
            $struct['ITEM'],
            $this->getId(),
            $struct['WHERE'],
            $this
        );
    }


    public function getJsonMe()
    {
        $impFields = AfwPrevilegeHelper::getAfwImportantFields($this);

        $result = [];

        foreach($impFields as $attribute)
        {
            $result[$attribute] = $this->getVal($attribute);
        }

        return $result;
    }

    public function getJsonArray($attribute)
    {
        $hetted = $this->het($attribute);
        if(!$hetted) return null;
        
        if(is_array($hetted))
        {
            $result = [];
            foreach($hetted as $hettedItem)
            {
                if(is_object($hettedItem) and ($hettedItem instanceof AFWObject))
                {
                    $result[$hettedItem->id] = $hettedItem->getJsonMe();
        
                } 
            }

        }
        elseif(is_object($hetted) and ($hetted instanceof AFWObject))
        {
            $result = $hetted->getJsonMe();

        }

        return $result;
    }


    public function het($attribute, $format = '', $optim_lookup = true)
    {
        $what = 'object';
        return $this->get($attribute, $what, $format, false, false, $optim_lookup);
    }

    public function specialDecode($attribute, $val_attribute, $lang = 'ar')
    {
        return $val_attribute;
    }

    public function getAttributeLabel($attribute, $lang = 'ar', $short = false)
    {
        // die("calling getAttributeLabel($attribute, $lang, short=$short)");
        return AfwLanguageHelper::getAttributeTranslation($this, $attribute, $lang, $short);
    }

    public function shouldBeCalculatedField($attribute)
    {
        return false;
    }

    public final function seemsCalculatedField($attribute)
    {
        if ((!$this->isEmpty()) and ($attribute != "id") and !isset($this->AFIELD_VALUE[$attribute])) return true;
        if (strpos($attribute, '.') !== false) return true;
        if (strpos($attribute, '_') === 0) return true;
        if ($this->shouldBeCalculatedField($attribute)) return true;

        return false;
    }

    /**
     * get
     * Return attribute's object or value
     * @param string $attribute
     * @param string $what
     * @param string $format
     * @param boolean $integrity : Optional, specify throwing or not exception if we have no result
     */
    public function get(
        $attribute,
        $what = 'object',
        $format = '',
        $integrity = true,
        $max_items = false,
        $optim_lookup = true,
        $lang = "ar"
    ) {
        return AfwLoadHelper::getAttributeData(
            $this,
            $attribute,
            $what,
            $format,
            $integrity,
            $max_items,
            $optim_lookup,
            $lang
        );
    }


    public final function dbdb_recup_value(
        $query,
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        $module_server = $this->getModuleServer();

        return AfwDatabase::db_recup_value(
            $query,
            $throw_error,
            $throw_analysis_crash,
            $module_server
        );
    }

    public function decodeList($nom_col, $val_arr)
    {
        $decoded_list = [];

        foreach ($val_arr as $index => $value) {
            $decoded_list[$index] = AfwFormatHelper::decodeAnswerOfAttribute($this, $nom_col, $value);
        }

        return $decoded_list;
    }

    /**
     * getVirtual
     * Return Value of Virtual Attribute
     * @param string $attribute
     * @param string $what
     * @param string $format
     */
    protected function getVirtual($attribute, $what, $format)
    {
        return '';
    }

    public function showErrorsAsSessionWarnings($mode = "display")
    {
        return "all";
    }

    public final function isOk($force = false, $returnErrors = false, $langue = null, $ignore_fields_arr = null, $start_step = null, $end_step = null)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if (!$langue) $langue = $lang;
        // $objme = AfwSession::getUserConnected();
        if (!$force and !AfwSession::hasOption('CHECK_ERRORS') and !$this->forceCheckErrors) {
            if (!$returnErrors) return true;
            else return [true, []];
        }
        $stop_on_first_error = (!$returnErrors);
        $returnErrorsStep = "all";
        if ($returnErrors != "all") {
            $start_step = $returnErrors;
            $end_step = $returnErrors;
        }



        $dataErr = AfwDataQualityHelper::getDataErrors($this, $langue, true, $force, $returnErrorsStep, $ignore_fields_arr, null, $stop_on_first_error, $start_step, $end_step);
        // die("showErrorsAsSessionWarnings:: getDataErrors($langue, true, $force, $returnErrorsStep, $ignore_fields_arr, null, $stop_on_first_error, $start_step, $end_step) => ".var_export($dataErr,true));
        $is_ok = count($dataErr) == 0;
        if (!$returnErrors) return $is_ok;
        else return [$is_ok, $dataErr];
    }


    public final function mfkValueToArrayOrBoolIndex($attribute, $boolIndex = true, $takeDefault = true)
    {
        $old_val = $this->getVal($attribute);
        if (!$old_val and $takeDefault) {
            $old_val = AfwStructureHelper::getDefaultValue($this, $attribute);
        }

        $old_val = trim($old_val);
        $old_val = trim($old_val, ',');
        if ($old_val) {
            $old_val_arr = explode(',', $old_val);
        } else {
            $old_val_arr = [];
        }

        if (!$boolIndex) return $old_val_arr;

        $bool_index_arr = [];

        foreach ($old_val_arr as $old_val_item) {
            $bool_index_arr[$old_val_item] = true;
        }

        return $bool_index_arr;
    }

    public final function countMfkItems($attribute)
    {
        $old_val = trim($this->getVal($attribute));
        $old_val = trim($old_val, ',');
        if ($old_val) {
            $old_val_arr = explode(',', $old_val);
        } else {
            $old_val_arr = [];
        }

        return count($old_val_arr);
    }

    public final function findInMfk($attribute, $id_to_find, $mfk_empty_so_found = false, $struct = null)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        if ($struct['TYPE'] != 'MFK' and $struct['TYPE'] != 'MENUM') {
            throw new AfwRuntimeException(
                "Only MFK Fields can use this method, $attribute is not MFK"
            );
        }
        $takeDefault = true;
        $boolIndex = true;
        $boolIndexArr = $this->mfkValueToArrayOrBoolIndex($attribute, $boolIndex, $takeDefault);

        return $boolIndexArr[$id_to_find];
    }

    /**
     * addRemoveInMfk add and/or remove ids in mfk attribute
     * @param string $attribute is the mfk attribute
     * @param array $ids_to_add_arr is the array of ids to add
     * @param array $ids_to_remove_arr is the array of ids to remove
     * @param array $struct is optional and if specified contain the attribute strcuture array
     */

    public function addRemoveInMfk($attribute, $ids_to_add_arr, $ids_to_remove_arr, $struct = null)
    {
        $old_val = $this->getVal($attribute);
        if (!$old_val) {
            $old_val = AfwStructureHelper::getDefaultValue($this, $attribute);
        }
        //if(!$old_val) return $old_val;

        if (!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        if ($struct['TYPE'] != 'MFK') {
            throw new AfwRuntimeException(
                get_class($this) . " : Only MFK Fields can use this method, $attribute is not MFK but look strcuture " . var_export($struct, true)
            );
        }

        $old_val = trim($old_val, ',');
        if ($old_val) {
            $old_val_arr = explode(',', $old_val);
        } else {
            $old_val_arr = [];
        }

        $old_index = [];

        foreach ($old_val_arr as $old_val_item) {
            $old_index[$old_val_item] = true;
        }

        foreach ($ids_to_add_arr as $id_to_add) {
            $old_index[$id_to_add] = true;
        }

        foreach ($ids_to_remove_arr as $id_to_remove) {
            $old_index[$id_to_remove] = false;
        }

        // throw new AfwRuntimeException("addRemoveInMfk $attribute : ".var_export($old_index,true));

        $new_val_arr = [];
        foreach ($old_index as $new_id => $new_bool) {
            if ($new_bool and $new_id) {
                $new_val_arr[] = $new_id;
            }
        }
        if (count($new_val_arr) > 0) {
            $new_mfk = ',' . implode(',', $new_val_arr) . ',';
        } else {
            $new_mfk = ',';
        }

        $this->set($attribute, $new_mfk);

        return $old_val != $new_mfk;
    }

    public function dynamicHelpCondition($attribute)
    {
        return true;
    }

    protected function attributeToBeRequiredDecision($attribute, $desc = null)
    {
        $decision = false;
        $required = null;
        return [$decision, $required];
    }

    public final function attributeIsRequired($attribute, $desc = null)
    {
        list($decision, $required) = $this->attributeToBeRequiredDecision(
            $attribute,
            $desc
        );
        if ($decision) {
            return $required;
        }

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }
        return $desc['MANDATORY'] and
            $this->attributeIsApplicable($attribute) or
            $desc['REQUIRED'];
    }

    public function attributeIsApplicable($attribute)
    {
        return true;
    }

    public function whyAttributeIsNotApplicable($attribute, $lang = 'ar')
    {
        $icon = 'na20.png';
        $textReason = $this->translateMessage('NA-HERE', $lang);
        return [$icon, $textReason, 20, 20];
    }

    /**
     * getFormuleResult
     * Return Value of Formule Attribute
     * @param string $attribute
     */
    public function getFormuleResult($attribute, $what = "value")
    {
        return AfwFormulaHelper::calculateFormulaResult($this, $attribute, $what);
    }

    public function searchDefaultValue($attribute)
    {
        return null;
    }

    public function calcObject($attribute)
    {
        return $this->calc($attribute, $integrity = false, $format = 'object');
    }

    public function calc($attribute, $integrity = false, $format = '')
    {
        return $this->get($attribute, 'calc', $format, $integrity);
    }


    public function v($attribute)
    {
        return $this->getVal($attribute);
    }

    /**
     * getVal
     * Return the value of an attribute
     * @param string $attribute
     */
    public function getVal($attribute, $integrity = false)
    {
        return $this->get($attribute, 'value', '', $integrity);
    }

    public function translateMyYesNo($attribute, $format, $lang)
    {
        if($format=="value") return ["Y","N","W"];
        $yes = $this->translate($attribute.".YES", $lang);
        $no = $this->translate($attribute.".NO", $lang);
        $euh = $this->translate($attribute.".EUH", $lang);

        return [$yes,$no,$euh];
    }

    /**
     * decode
     * Decode an attribute switch his type and display it through a specified format
     * @param string $attribute
     * @param string $format
     */
    public function decode($attribute, $format = '', $integrity = false, $lang="ar")
    {
        // if($attribute == "session_status_id") die("decode($attribute, $format, $integrity)");
        if (strtolower($format) == 'value') {
            return $this->getVal($attribute);
        } else {
            return $this->get($attribute, 'decodeme', $format, $integrity, false, true, $lang);
        }
    }

    public function setOrder($order)
    {
        return false;
    }

    /**
     * superNativeSet
     * light native set of attribute's value without 
     * it doen't make :
     *                 neither the check if attribute is shortname 
     *                 neither the structure check 
     *                 neither the format value even if needed 
     *                 nor the trigger of events like beforeSet afterSet etc...
     * @param string $attribute
     * @param string $value
     * 
     * It is developed for performance purposes but to be used very carefully and only
     * with experienced developers because of explanation above
     */
    public function superNativeSet($field_name, $value)
    {
        $this->setAfieldValue($field_name, $value);
    }



    private function setAfieldValue($field_name, $value)
    {
        // if(!isset($this->AFIELD _VALUE)) $this->AFIELD _VALUE = array();
        /*
        if(static::$TABLE == "academic_term") 
        {
            if(($field_name == "active") and ($value=="N")) throw new AfwRuntimeException("case Medali found for debugg");
        }*/
        $this->AFIELD_VALUE[$field_name] = $value;
        return $value;
    }

    /**
     * nativeSet
     * light native set of attribute's value without 
     * it doen't make :
     *                 neither the check if attribute is shortname 
     *                 neither the structure check 
     *                 nor the format value even if needed 
     * but it trigger events like beforeSet afterSet etc...
     * @param string $attribute
     * @param string $value
     * @param boolean $check
     */
    public function nativeSet(
        $attribute,
        $value,
        $check = true,
        $nothing_updated = false,
        $simul_do_not_save = false,
        $forceSet = false,
        $is_numeric_field = false
    ) {
        // new logic start

        // new logic end

        $old_value = $this->getAfieldValue($attribute);

        // rafik 2019 : $old_value == $value below was $old_value === $value  (=== 3 times)
        // but cause pb if default val of attribute is integer and value setted is string
        // to check if any impact
        // => rafik 2020-09-22 I put === $value instead of == $value it is for me more logic so rollback
        // no pb if setting 15 integer value to "15" string value is considered as a change ...
        // better than setting 0 value instead of "" string value is considered not a change !!!
        //
        $value_same = (($old_value === $value) or ($is_numeric_field and $value == $old_value));

        $value_exists = ((!$this->isEmpty()) and ($this->isAfieldValueSetted($attribute)));

        $value_exists_and_same_and_no_force =
            ($value_exists and $value_same and !$forceSet);
        /*
        if (
            !$value_exists_and_same_and_no_force and
            $value == $old_value and
            AfwStringHelper::stringContain($value, 'ر')
        ) {
            die(
                "value_exists=$value_exists,value_same=$value_same,old_value=" .
                    var_export($old_value, true) .
                    ',value=' .
                    var_export($value, true)
            );
        }*/

        $value_doesnt_exist_and_set_to_empty_and_no_force =
            !$old_value && (!$value and !$forceSet); // obsolete kharabit and (!$value_zero_int)

        if ($value_exists_and_same_and_no_force) {
            $this->debugg_value_exists_and_same_and_no_force = true;
            $return = false;
        } elseif ($value_doesnt_exist_and_set_to_empty_and_no_force) {
            $this->debugg_value_doesnt_exist_and_set_to_empty_and_no_force = true;
            $return = false;
        } else {
            // rafik : for qedit '' != 0 and we dont want record to be inserted
            //  donc dans ce cas $nothing_updated = true
            //  pour que cela soit traite par FIELDS_ INITED et pas FIELDS_UPDATED
            // c faux FIELDS_ INITED not used in update only in insert
            // a voir : if((!$old_value) && (!$value)) $nothing_updated = true;

            if ($this->beforeSetAttribute($attribute, $value)) {
                $this->setAfieldValue($attribute, $value);
                $this->afterSetOfAttribute($attribute, $value);
                $this->UPDATE_DATE_val = null; // then it will take now when the commit is performed
            } else {
                $this->debugg_before_set_attrib_rejected_the_set = true;
            }
            /*
            if(($attribute=="email")) 
            {
                die(" nothing_updated = $nothing_updated, simul_do_not_save = $simul_do_not_save id=".$this->getId().", attribute=$attribute, value = $value, this->getAfieldValue($attribute) = ".$this->getAfieldValue($attribute));
            }
            */
            if ($nothing_updated) {
                // just init fields to default values
                $this->setAfieldDefaultValue($attribute, $value);
                // $this->debugg_nothing_updated_init_to = $value;
            } elseif ($simul_do_not_save) {
                //$this->debugg_simul_do_not_save_to = $value;
                // it means it is simulation only
                // if(($attribute=="email")) die(" nothing_updated = $nothing_updated, simul_do_not_save = $simul_do_not_save id=".$this->getId().", attribute=$attribute, value = $value, this->FIELDS_UPDATED=".var_export($this->FIELDS_UPDATED,true));
            } else {
                // rafik : since version 2.0.1 if we are updating existing record
                // we put in FIELDS_UPDATED the old values
                // not the new value that we can find in aFIELD VALUEs array
                // except if value is empty so we put true "@@empty@@"
                // to be sure that FIELDS_UPDATED contain old value
                // --> to test that old value was not empty do FIELDS_UPDATED[$key] !== "@@empty@@"
                // --> to test that old value was empty do FIELDS_UPDATED[$key] === "@@empty@@"
                if (!$this->isEmpty()) {
                    if ($old_value) {
                        $old_value_changed = $old_value;
                    } else {
                        $old_value_changed = '@@empty@@';
                    }
                    $this->FIELDS_UPDATED[$attribute] = $old_value_changed;
                    // $this->debugg_field_updated_for_insert_from = $old_value_changed;
                } else {
                    $this->FIELDS_UPDATED[$attribute] = '@@nov@@'; // ie. no old value as empty object example if we work on empty object to do an update not only on me
                    // $this->debugg_field_updated_from = $value;
                }
                //if(($attribute=="status_id")) throw new AfwRuntimeException(" nothing_updated = $nothing_updated, simul_do_not_save = $simul_do_not_save id=".$this->getId().", attribute=$attribute, value = $value, this->FIELDS_UPDATED=".var_export($this->FIELDS_UPDATED,true));
            }

            $return = true;
        }
        // if(($attribute=="email")) die("value_doesnt_exist_and_set_to_empty_and_no_force=$value_doesnt_exist_and_set_to_empty_and_no_force, value_exists_and_same_and_no_force=$value_exists_and_same_and_no_force return =$return, nothing_updated = $nothing_updated, simul_do_not_save = $simul_do_not_save id=".$this->getId().", attribute=$attribute, value = $value, this->FIELDS_UPDATED=".var_export($this->FIELDS_UPDATED,true));

        return $return;
    }

    public function multipleSet($rowAttributeValues, $commit=false)
    {
        foreach($rowAttributeValues as $attribute => $value)
        {
            $this->set($attribute, $value);
        }

        if($commit) $this->commit(); 
    }

    /**
     * set
     * Set attribute's value for next insert or update
     * it make the check if attribute is shortname and check structure and format value if needed 
     * it also trigger events like beforeSet afterSet etc... 
     * @param string $attribute
     * @param string $value
     * @param boolean $check
     */
    public function set($attribute, $value, $forceSet = null, $is_numeric_field = false)
    {
        if ($forceSet === null) {
            $forceSet = $this->force_mode;
        }
        //$call_method = "set(attribute = $attribute, value = $value)";
        if ($attribute == 'id' and $this->id and !$value and !$this->authorize_empty_of_id) {
            throw new AfwRuntimeException($this->class . ' : trying to empty id ... it was id=' . $this->id);
        }

        $attribute = AfwStructureHelper::shortNameToAttributeName($this, $attribute);
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        if (!$structure) {
            throw new AfwRuntimeException("attribute $attribute doesn't exist in strcuture of this class : " . $this->getMyClass());
        }
        if ($structure['TYPE'] == 'DATE') {
            if ($value and $value != 'now()') {
                $value = AfwDateHelper::formatDateForDB($value);
            }
        }

        if ($structure['TYPE'] == 'TEXT') {
            if((!$value) or ($value=="0"))
            {
                $forceSet = true; 
            }
        }

        if ($structure['TYPE'] == 'GDAT') {
            if ($value and $value != 'now()') {
                $value = AfwDateHelper::formatGDateForDB($value);
            }
        }

        if (is_array($value)) {
            $value = var_export($value, true);
        }

        if ($structure['WRITE_PRIVATE']) {
            throw new AfwRuntimeException("cannot set the attribute $attribute of table " . static::$TABLE . " protected by property [WRITE_PRIVATE]");
        } else {
            $return = $this->nativeSet(
                $attribute,
                $value,
                $check = true,
                $nothing_updated = false,
                $simul_do_not_save = false,
                $forceSet,
                $is_numeric_field
            );
        }
        $this->debugg_last_attribute_setted = "attribute=$attribute, value=$value, check = $check, nothing_updated = $nothing_updated, simul_do_not_save = $simul_do_not_save, forceSet=$forceSet return = $return";
        return $return;
    }

    public function simulSet($attribute, $value)
    {
        return $this->nativeSet($attribute, $value, false, true, true);
    }

    protected function beforeSetAttribute($attribute, $newvalue)
    {
        $oldvalue = $this->getVal($attribute);
        /*
          if($attribute=="capproval")
          {
           throw new AfwRuntimeException("before set attribute $attribute from '$oldvalue' to '$newvalue'");
          }
          */
        return true;
    }

    final public function afterSetOfAttribute($attribute, $newvalue, $struct = null)
    {
        if (!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);

        // if($attribute=="nasrani_birth_date") die("afterSetOfAttribute($attribute) -> struct : ".var_export($struct,true));
        if ($struct['DATE_CONVERT'] == 'NASRANI') {
            $attribute_original = $struct['ORIGINAL_ATTRIBUTE'];
            if (!$attribute_original) {
                $attribute_original = str_replace('nasrani_', '', $attribute);
            }
            // die("afterSetOfAttribute($attribute) -> update of $attribute_original");
            if ($attribute_original) {
                $nasrani_val = $newvalue;
                $this->set(
                    $attribute_original,
                    AfwDateHelper::to_hijri($nasrani_val)
                );
                // die("afterSetOfAttribute : $attribute, $attribute_original = to_hijri($nasrani_val) = ".$this->getVal($attribute_original));
            }
        }

        $this->afterSetAttribute($attribute);
    }

    protected function afterSetAttribute($attribute)
    {
        // It is to be rewritten in sub classes
        /* ca be useful for some debugg 
        if(($attribute=="id") and $this->id == 300 0000002) 
        {
            throw new AfwRuntimeException("Here the bug");
        }*/
    }

    public function setSlient($attribute, $value)
    {
        return $this->nativeSet($attribute, $value, true, true);
    }

    // il faut utiliser setForce si on essaye de vider attribut dans un multi update (many records not only one) donc on va utiliser
    // un objet vide et on essaye de vider un attribut deja vide et donc par conclusion l'optimisateur va ignorer l'operation
    // si on n'utilise pas le mode force
    public function setForce($attribute, $value, $is_numeric_field = false)
    {
        return $this->nativeSet(
            $attribute,
            $value,
            $check = true,
            $nothing_updated = false,
            $simul_do_not_save = false,
            $forceSet = true,
            $is_numeric_field
        );
    }



    public function isChanged()
    {
        // $this->debugg_has_changed = implode(',', $this->FIELDS_UPDATED);
        return count($this->FIELDS_UPDATED);
    }

    public function hasChanged()
    {
        $debugg_has_changed = implode(',', $this->FIELDS_UPDATED);
        // $this->debugg_has_changed = $debugg_has_changed;
        return $debugg_has_changed;
    }

    public function isDraft()
    {
        if (AfwStructureHelper::fieldExists($this, 'draft')) {
            // $cl = $this->getMyClass();
            // if($cl == "Student") die("$cl has draft field");
            return $this->estDraft();
        }
        // rafik : if draft is not defined all are drafts and no error considered if
        // i am inside a wizard step and some mandatory fields are not defined
        return true;
    }


    public function fieldsHasChanged()
    {
        return $this->FIELDS_UPDATED;
    }

    public function resetChangedFields()
    {
        $this->FIELDS_UPDATED = [];
    }

    public function myShortNameToAttributeName($attribute)
    {
        return $attribute;
    }



    /* obsoleted
    public final function stdShortNameToAttributeName($attribute)
    {
        // use of short names in strcucture 
        // will be obsoleted except if we override 
        // myShortNameToAttributeName method in sub-classes
        
        $short_names = self::getShortNames();
        if ($short_names[$attribute]) {
            $attribute = $short_names[$attribute];
        }

        return $attribute;
    }*/

    protected function isSpecificOption($attribute)
    {
        return false;
    }

    final public function isSpecialOption($attribute)
    {
        if (AfwUmsPagHelper::attributeIsAfwKnownOption($attribute)) return true;
        return $this->isSpecificOption($attribute);
    }

    final public function isOption($attribute)
    {
        return $this->isSpecialOption($attribute) or
            strtoupper($attribute) == $attribute;
    }

    final public function getOptionValue($attribute)
    {
        return $this->OPTIONS[$attribute];
    }

    final public function setOptionValue($attribute, $value)
    {
        $this->OPTIONS[$attribute] = $value;
    }

    final public function isDebugg($attribute)
    {
        return AfwStringHelper::stringStartsWith($attribute, 'debugg_');
    }

    final public function extractDebuggAttribute($attribute)
    {
        return substr($attribute, 7);
    }

    final public function isSetDebuggAttribute($attribute)
    {
        $debugg_attribute = $this->extractDebuggAttribute($attribute);
        return isset($this->debuggs[$debugg_attribute]);
    }

    final public function getDebuggAttributeValue($attribute)
    {
        //if(AfwStringHelper::stringStartsWith($attribute,"mfk_val_city1_hotel_mfk")) die("getDebuggAttributeValue($attribute) : this->debuggs => ".var_export($this->debuggs,true));
        return $this->debuggs[$attribute];
    }

    public final function setDebuggAttributeValue($attribute, $value)
    {
        $this->debuggs[$attribute] = $value;
        //if(AfwStringHelper::stringStartsWith($attribute,"mfk_val_city1_hotel_mfk")) die("setDebuggAttributeValue($attribute, $value) : this->debuggs => ".var_export($this->debuggs,true));
    }

    public function easyModeNotOptim()
    {
        return false;
    }

    public function __set($attribute, $value)
    {
        if ($this->isOption($attribute)) {
            $this->setOptionValue($attribute, $value);
        } elseif ($this->isDebugg($attribute)) {
            $debugg_attribute = $this->extractDebuggAttribute($attribute);
            $this->setDebuggAttributeValue($debugg_attribute, $value);
        } else {
            if (
                AfwStructureHelper::isEasyAttribute($this, $attribute) or
                AfwStructureHelper::isObjectEasyAttribute($this, $attribute)
            ) {
                if (
                    is_object($value) and
                    AfwStructureHelper::isObjectEasyAttribute($this, $attribute)
                ) {
                    $this->set($attribute, $value->pk);
                } else {
                    $this->set($attribute, $value);
                }
            } else {
                if ($attribute == 'pk' or $attribute == 'id') {
                    return $this->setId($value);
                } else {
                    $this->ATTRIB_VALUE[$attribute] = $value;
                }
            }
        }
    }

    public function __get($attribute)
    {
        if (substr($attribute, 0, 9) == 'value_of_') {
            $attribute = substr($attribute, 9);
            return $this->getVal($attribute);
        }

        if (substr($attribute, 0, 10) == 'object_of_') {
            $attribute = substr($attribute, 10);
            return $this->het($attribute);
        }



        if ($this->isOption($attribute)) {
            return $this->getOptionValue($attribute);
        } elseif ($this->isDebugg($attribute)) {
            $debugg_attribute = $this->extractDebuggAttribute($attribute);
            return $this->getDebuggAttributeValue($debugg_attribute);
        } else {
            // if attribute is short name decode it to real name
            $attribute = AfwStructureHelper::shortNameToAttributeName($this, $attribute);
            // important to keep like this
            // the use of ->yyyy that handle __get php magic function is ONLY TO get the scalar value
            // if he want the object value of FK he should explicitely use ->get("yyyy") or use ->object_of_yyyy
            if (AfwStructureHelper::isObjectEasyAttribute($this, $attribute)) {
                return $this->het($attribute);
            } elseif (AfwStructureHelper::isListObjectEasyAttribute($this, $attribute)) {
                return $this->get($attribute);
            } elseif (AfwStructureHelper::isFormulaEasyAttribute($this, $attribute)) {
                return $this->calc($attribute);
            } elseif (AfwStructureHelper::isEasyAttribute($this, $attribute)) {
                return $this->getVal($attribute);
            } else {
                if ($attribute == 'pk' or $attribute == 'id') {
                    return $this->getId();
                } else {
                    return $this->ATTRIB_VALUE[$attribute];
                }
            }

            //
        }
    }

    public function __isset($attribute)
    {
        return AfwStructureHelper::containObjects($this, $attribute) or
            AfwStructureHelper::containData($this, $attribute) or
            $this->isOption($attribute) or
            $this->isSetDebuggAttribute($attribute);
    }



    public function __call($name, $arguments)
    {
        if (substr($name, 0, 11) == 'my_list_of_') {
            $method = substr($name, 3);
            return $this->$method();
        }

        $method = substr($name, 0, 3);
        $attribute_firstlower = lcfirst(substr($name, 3));
        $attribute = strtolower($attribute_firstlower);
        switch ($method) {
            case 'rel':
                return $this->getRelation($attribute_firstlower);
                break;
            case 'get':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute)) throw new AfwRuntimeException("call to unknown method $name from " . static::class);
                return $this->get($attribute);
                break;
            case 'het':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute)) throw new AfwRuntimeException("call to unknown method $name from " . static::class);
                return $this->het($attribute);
                break;
            case '__v':
                return $this->getAfieldValue($attribute); // to obsolete may be
                break;
            
            /*case 'cal':
                return $this->calc($attribute);
                break; never do this because if developper doesnt implement calcXXXXX method no exception is thrown*/ 
            case 'val':
                return $this->getVal($attribute);
                break;
            case 'shw':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute_firstlower)) throw new AfwRuntimeException("unknown attribute $attribute_firstlower when call to method $name from " . static::class);
                return $this->showAttribute($attribute_firstlower);
                break;
            case '_is':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute)) throw new AfwRuntimeException("unknown attribute $attribute when call to method $name from " . static::class);
                return $this->is($attribute);
                break;
            case 'est':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute)) throw new AfwRuntimeException("unknown attribute $attribute when call to method $name from " . static::class);
                return $this->sureIs($attribute);
                break;
            case 'dec':
                if (!AfwStructureHelper::fieldReallyExists($this, $attribute)) throw new AfwRuntimeException("unknown attribute $attribute when call to method $name from " . static::class);
                if (count($arguments) <= 1) {
                    $this->decode($attribute, $arguments[0]);
                } else {
                    throw new AfwRuntimeException(
                        "call to the method decode() avec plus d'un argument : decode('" .
                            $attribute .
                            "', '" .
                            implode("', '", $arguments) .
                            "')."
                    );
                }
                break;
            case 'set':
                if (count($arguments) == 1) {
                    return $this->set($attribute, $arguments[0]);
                } else {
                    throw new AfwRuntimeException(
                        "call to the method set() avec plus d'un argument : set('" .
                            $attribute .
                            "', '" .
                            implode("', '", $arguments) .
                            "')."
                    );
                }
                break;
            default:
                $returnAfwCall = $this->afwCall($name, $arguments);
                if ($returnAfwCall === false) {
                    $this_table = static::$TABLE;
                    throw new AfwRuntimeException(
                        "afw 'magic' method afwCall : class $this_table make a call to a non exisiting method : '" .
                            $name .
                            "'."
                    );
                }
                return $returnAfwCall;
                break;
        }
    }

    protected function afwCall($name, $arguments)
    {
        // can be rewritten if needed in subclasses
        // if case treated should return something !== false
        return false;
        // the above return should be keeped if not treated
    }

    /**
     * mfkContain
     * Set attribute's value in the Search criteria as mfk contained
     * @param string $attribute
     * @param string $value
     */
    public function mfkContain($attribute, $value)
    {
        $sep = self::$mfk_separator;

        return $this->where("$attribute like '%" . $sep . $value . $sep . "%'");
    }

    /**
     * afterSelect
     * To be overridden if need by sub-classes,
     * this event happen when we filter a column with select($attribute, $value) method
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param string $value
     */
    public function afterSelect($attribute, $value)
    {
        // NOTICE : qsearch mode use ->where() and not ->select() so doesn't come here
        // overridde your code here
        /*if((get_class($this)=="Application") and ($attribute=="idn"))
        {
            die("after select $attribute getSQL() = ".$this->getSQL());
        }*/
    }

    /**
     * select1OrSelect2
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param string $value
     */
    public function select1OrSelect2($attribute1, $value1, $attribute2, $value2)
    {
        $sql_select1 = $this->select($attribute1, $value1, true);
        $sql_select2 = $this->select($attribute2, $value2, true);

        $this->SEARCH .= " and (($sql_select1) or ($sql_select2))";                
        return true;
    }

    /**
     * selectOneOfListOfCritirea
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param string $value
     */
    public function selectOneOfListOfCritirea($arrSelects)
    {
        $sql_select_arr = [];
        foreach($arrSelects as $attribute => $value)
        {
            $sql_select_arr[] =  $this->select($attribute, $value, true);
        }
        
        

        $this->SEARCH .= " and ((".implode(") or (",$sql_select_arr)."))";                
        return true;
    }

    /**
     * select
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param string $value
     */
    public function select($attribute, $value, $returnSQLOnly=false)
    {
        if ((self::$TABLE == "acondition_origin") and ($attribute == "cvalid")) {
            throw new AfwRuntimeException(self::$TABLE . "->select($attribute, $value) ya rafik !!!!");
        }
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible faire call to the method select() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {

            $attribute = AfwStructureHelper::shortNameToAttributeName($this, $attribute);
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
            if ($structure['UTF8']) {
                $_utf8 = '_utf8';
            } else {
                $_utf8 = '';
            }
            
            if(!$returnSQLOnly)
            {
                $this->SEARCH_TAB[$attribute] = AfwStringHelper::_real_escape_string($value);
                $this->afterSelect($attribute, $value);
            }    

            if ($structure['FIELD-FORMULA']) {
                $attribute_sql = $structure['FIELD-FORMULA'];
            } else {
                $attribute_sql = 'me.' . $attribute;
            }

            $sql_select = $attribute_sql . " = $_utf8'" . AfwStringHelper::_real_escape_string($value) . "'";

            if($returnSQLOnly)
            {
                return $sql_select;
            }
            else
            {
                $this->SEARCH .= ' and ' . $sql_select;
                // if($attribute=="cvalid") throw new AfwRuntimeException("this->SEARCH = ".$this->SEARCH." because structure=".var_export($structure,true));
                return true;
            }
        }
    }

    /**
     * select
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param Array $values
     */
    public function selectIn($attribute, $values)
    {
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible faire call to the method selectIn() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {
            $attribute = AfwStructureHelper::shortNameToAttributeName($this, $attribute);
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
            if ($structure['UTF8']) {
                $_utf8 = '_utf8';
            } else {
                $_utf8 = '';
            }

            if ($structure['FIELD-FORMULA']) {
                $attribute_sql = $structure['FIELD-FORMULA'];
            } else {
                $attribute_sql = 'me.' . $attribute;
            }

            $this->SEARCH .=
                ' and ' .
                $attribute_sql .
                " in ('" .
                implode("','", $values) .
                "')";
            //if($attribute=="parent_module_id") throw new AfwRuntimeException("this->SEARCH = ".$this->SEARCH);
            return true;
        }
    }

    /**
     * where
     * Set with SQL Language the Search Criteria
     * @param string $sql : Adding to clause where
     */
    public function where($sql, $val_to_keep = '')
    {
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible faire call to the method where() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {
            // ajouter " and " seulement si elle n'est pas dans $sql
            $check_and_exists = substr(strtoupper(trim($sql)), 0, 4);
            if ($check_and_exists == 'AND ') {
                $sql = substr(trim($sql), 4);
            }

            $val_to_keep = trim($val_to_keep);
            $val_to_keep = trim($val_to_keep, ',');
            if ($val_to_keep) {
                $pk_col = $this->getPKField();
                $sql = "(($sql) or (me.$pk_col in ($val_to_keep)))";
                // if($val_to_keep==107895) throw new AfwRuntimeException("rafik 107895 ici");
            }
            $this->SEARCH .= " and ($sql)";
            //if($sql=="me.system_id '1273' and me.parent_module_id '1274' and me.avail 'Y'")
            //throw new AfwRuntimeException("this->SEARCH = ".$this->SEARCH);

            return true;
        }
    }

    public function sqlAnd($sql)
    {
        return $this->where($sql);
    }

    public function sqlOr($sql)
    {
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible faire call to the method where() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {
            // ajouter " and " seulement si elle n'est pas dans $sql
            $check_or_exists = substr(strtoupper(trim($sql)), 0, 3);
            if ($check_or_exists == 'OR ') {
                $sql = substr(trim($sql), 3);
            }

            $this->SEARCH .= " or ($sql)";

            return true;
        }
    }

    public function select_VH($val_to_keep = '', $dropdown = false)
    {
        $val_to_keep = trim($val_to_keep);
        $val_to_keep = trim($val_to_keep, ',');
        $pk_col = $this->getPKField();
        if ($val_to_keep and $pk_col) {
            $this->SEARCH .= " and ((me.$pk_col in ($val_to_keep)) or (1 ";
        }

        $this->select_visibilite_horizontale($dropdown);

        if ($val_to_keep and $pk_col) {
            $this->SEARCH .= '))';
        }
    }

    public function loadLookupObjects($orderBy = '')
    {
        return self::loadAllLookupObjects($orderBy);
    }

    public static function loadAllLookupObjects($orderBy = '')
    {
        $obj = new static();
        if (!self::$all_data[static::$TABLE]) {
            $obj->select($obj->fld_ACTIVE(), 'Y');
            self::$all_data[static::$TABLE] = $obj->loadMany('', $orderBy);
        }
        return self::$all_data[static::$TABLE];
    }






    /**
     * clearSelect
     * Empty Search criteria
     */
    final public function clearSelect()
    {
        $this->SEARCH = '';
        $this->SEARCH_TAB = [];
        return true;
    }

    /**
     * initValues
     * Empty Fields and Objects cache
     */


    private function initValues()
    {
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);

        foreach ($all_real_fields as $fieldName) {
            $this->setAfieldValue(
                $fieldName,
                $this->getAfieldDefaultValue($fieldName),
                true
            );
        }
    }

    /**
     * resetValues
     * Empty Fields and Objects cache
     */

    public function resetValues()
    {
        $all_real_fields = array_keys($this->getAllfieldValues());

        foreach ($all_real_fields as $fieldName) {
            $this->setAfieldValue(
                $fieldName,
                $this->getAfieldDefaultValue($fieldName),
                true
            );
        }

        foreach ($this->OBJECTS_CACHE as $key => $object) {
            unset($this->OBJECTS_CACHE[$key]);
            $this->OBJECTS_CACHE[$key] = null;
        }

        return $all_real_fields;
    }

    final public function silentField($attribute)
    {
        if (isset($this->FIELDS_UPDATED[$attribute])) {
            $this->setAfieldDefaultValue(
                $attribute,
                $this->FIELDS_UPDATED[$attribute]
            );
            unset($this->FIELDS_UPDATED[$attribute]);
            // throw new AfwRuntimeException("debugg rafik choof silentField($attribute)");
        }
    }

    public function getFixmSearchCols($request=[], $qsearch=true)
    {
        $result = [];
        if($qsearch) $arrCols = AfwFrameworkHelper::getColsByMode($this, "QSEARCH");
        else $arrCols = AfwFrameworkHelper::getColsByMode($this, "SEARCH");

        foreach($arrCols as $col)
        {
            if($request[$col])
            {
                $result[$col] = $request[$col];
            }
        }

        return $result;
    }

    public function fixModeSubAttributes($attribute, $value)
    {
        // should be overriden for virtual fields or category fields

        // by default no sub attributes :
        return [];
    }

    public final function fixModeSet($attribute, $value)
    {
        $subAttr = $this->fixModeSubAttributes($attribute, $value);
        if (count($subAttr) > 0) {
            foreach ($subAttr as $attr0 => $val0) {
                if (!is_array($val0)) {
                    $this->setSlient($attr0, $val0);
                }
            }
            return;
        }

        // by default should do :
        $this->setSlient($attribute, $value);
    }

    public function fixModeSelect($attribute, $value)
    {
        $subAttr = $this->fixModeSubAttributes($attribute, $value);
        if (count($subAttr) > 0) {
            foreach ($subAttr as $attr0 => $val0) {
                if (!is_array($val0)) {
                    $this->select($attr0, $val0);
                } else {
                    $this->selectIn($attr0, $val0);
                }
            }
            return;
        }

        // by default should do :
        $this->select($attribute, $value);
    }



    final public function setSilent($attribute, $value)
    {
        $this->set($attribute, $value);
        $this->silentField($attribute);
    }

    public function get_CREATION_DATE_value($add_cote_if_needed = false)
    {
        $creation_date = $this->CREATION_DATE_val;
        if (!$creation_date) {
            $creation_date = date("Y-m-d H:i:s");
        }

        if ($add_cote_if_needed) {
            $creation_date = "'$creation_date'";
        }
        return $creation_date;
    }

    public function get_UPDATE_DATE_value($add_cote_if_needed = false)
    {
        $update_date = $this->UPDATE_DATE_val;
        if (!$update_date) {
            $update_date = date("Y-m-d H:i:s");
        } 

        if ($add_cote_if_needed) {
            $update_date = "'$update_date'";
        }
        return $update_date;
    }

    public function insertNew()
    {
        return $this->insert($pk = '', $check_if_exists_by_uk = false);
    }

    /**
     * insert
     * Insert row
     * @param int $pk : Optional, specify the primary key
     */
    public function insert($pk = '', $check_if_exists_by_uk = true)
    {
        return AfwSqlHelper::insertObject($this, $pk, $check_if_exists_by_uk);
    }
    public function getVersion()
    {
        $return = $this->getVal($this->fld_VERSION());
        if (!$return) $return = 0;
        return $return;
    }

    public function getUpdateDate()
    {
        return $this->getVal($this->fld_UPDATE_DATE());
    }

    public function getCreationDate()
    {
        return $this->getVal($this->fld_CREATION_DATE());
    }

    public function getUpdateUserId()
    {
        $return = $this->getVal($this->fld_UPDATE_USER_ID());
        //if(!$return) die(var_export($this,true));
        return $return;
    }

    public function getCreationUserId()
    {
        return $this->getVal($this->fld_CREATION_USER_ID());
    }

    public function commit()
    {
        if ($this->getId() > 0) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    public function resetUpdates()
    {
        $this->clearSelect();
        $this->FIELDS_UPDATED = [];
    }

    public function repareBeforeUpdate()
    {
        // to be overridden if needed
        return false;
    }

    public static final function updateWhere($sets_arr, $where_clause)
    {
        $obj = new static();
        foreach ($sets_arr as $col_name => $col_value) {
            $obj->set($col_name, $col_value);
        }

        $obj->where($where_clause);

        return $obj->update(false);
    }


    /**
     * update
     * Update row
     */
    public function update($only_me = true)
    {

        return AfwSqlHelper::updateObject($this, $only_me);
    }

    /**
     * hide  different then logicDelete by 2 things
     *     1. hide operate on one record only  and logicDelete can operate many records
     *     2. execute beforeHide and afterHide events
     * Hide row by setting AVAILABLE_IND = 'N'
     */
    public function hide()
    {
        return AfwSqlHelper::hideObject($this);
    }


    /**
     * switcherConfig
     * @param string $col
     * @param Auser $auser
     * should be overridden in subclasses if more columns should be switchable
     * return array[$switcher_authorized, $switcher_title, $switcher_text]
     * 
     * $switcher_authorized : if true means the column $col should be switchable
     * $switcher_title, $switcher_text are the title and warning that will be shown by the confirmation popup before do the switch
     */

    public function switcherConfig($col, $auser = null)
    {
        $switcher_authorized = false;
        $switcher_title = "";
        $switcher_text = "";

        if ($col == $this->fld_ACTIVE()) {
            $switcher_authorized = true;
        }

        return [$switcher_authorized, $switcher_title, $switcher_text];
    }



    /**
     * moveConfig
     * @param string $col
     * @param Auser $auser
     * should be overridden in subclasses 
     * return array[$move_authorized, $move_title, $move_text]
     * 
     * $move_authorized : if true means object is moveable
     * $move_title, $move_text are the title and warning that will be shown by the confirmation popup before do the move
     */

    public function moveConfig($col, $sens, $auser = null)
    {
        $move_authorized = false;
        $no_move_title = "";
        $no_move_reason = "";
        $move_limit = $this->moveLimit($col);

        if ($this->getVal($col)+$sens >= $move_limit) {
            $move_authorized = true;
        }
        else {
            $no_move_title = "limit reached";
            $no_move_reason = "move_limit=$move_limit reached";
        }



        return [$move_authorized, $no_move_title, $no_move_reason];
    }

    public final function userCanMoveMe($auser, $sens)
    {
        $col = $this->moveColumn();
        if(!$col) return [false, 'move rejected', 'no move column defined'];
        $desc = AfwStructureHelper::getStructureOf($this, $col);
        if (!$this->attributeCanBeEditedBy($col, $auser, $desc)) return [false, 'move rejected', 'move column is readonly for you'];
        if ($auser->isSuperAdmin()) return [true, '', ''];
        return $this->moveConfig($col, $sens, $auser);
    }


    public function getMyIndexArray()
    {
        if (is_array($this->UNIQUE_KEY) and count($this->UNIQUE_KEY) > 0) {
            $uk_arr = [];
            foreach ($this->UNIQUE_KEY as $key) {
                $uk_arr[$key] = $this->getVal($key);
            }
            return $uk_arr;
        }
        else throw new AfwRuntimeException("No UNIQUE_KEY index defined for ".get_class($this));
    }

    public function moveColumn()
    {
        return null; // to be overridden
    }

    public function moveLimit($col)
    {
        return 0;
    }

    public final function getMoveOrder()
    {
        $col = $this->moveColumn();
        if(!$col) return $this->id;
        return $this->getVal($col);
    }

    public final function moveMe($sens)
    {
        try
        {
            
            $secondObjSwitched = null;
            $newId = 0;
            $col = $this->moveColumn();
            $limitDown = $this->moveLimit($col);
            if(!$col) return [false, "No move column defined", null];
            $isInIndex = $this->isIndexAttribute($col);
            if($isInIndex)
            {
                $colVal = $this->getVal($col);
                $newVal = $this->getVal($col) + $sens;
                if($newVal<$limitDown) 
                {
                    return [false, "LIMIT-REACHED", null];
                }
                $newArrIndex = $this->getMyIndexArray();
                $newArrIndex[$col] = $newVal;
                $secondObjSwitched = $this->loadBrotherWithUniqueKey($newArrIndex);
            }

            if($secondObjSwitched)
            {
                $secondObjSwitched->set($col, -1);
                $secondObjSwitched->commit();
            }

            $this->set($col, $newVal);
            $this->commit();

            if($secondObjSwitched)
            {
                $secondObjSwitched->set($col, $colVal);
                $secondObjSwitched->commit();
                $newId = $secondObjSwitched->id;
            }
            $status = "NOTHING-DONE";
            if($sens<0) $status = "MOVED-UP-$newId-$limitDown";
            elseif($sens>0) $status = "MOVED-DOWN-$newId-$limitDown";

            return [true, $status, $secondObjSwitched];
        }
        catch(Exception $e)
        {
            return [false, $e->getMessage(), null];
        }

        

    }
    
    

    /**
     * @param Auser $auser
     * @param string $col
     * 
     */

    public final function userCanSwitchCol($auser, $col)
    {
        $desc = AfwStructureHelper::getStructureOf($this, $col);
        if (!$this->attributeCanBeEditedBy($col, $auser, $desc)) return false;
        if ($auser->isSuperAdmin()) return true;
        list($switcher_authorized,) = $this->switcherConfig($col, $auser);

        return $switcher_authorized;
    }

    public final function switchCol($swc_col)
    {
        try {
            $switch_mess = 'SWITCH FAILED ';
            $swc_col_old_val = $this->getVal($swc_col);
            if ($swc_col_old_val == "N") {
                $this->set($swc_col, "Y");
                $switch_mess = "SWITCHED-ON";
            } else {
                $this->set($swc_col, "N");
                $switch_mess = "SWITCHED-OFF";
            }

            $this->commit();
        } catch (Exception $e) {
            $switch_mess .= $e->getMessage() . "\n The stack trace is : " . $e->getTraceAsString();
        } catch (Error $e) {
            $switch_mess .= $e->__toString();
        }


        return $switch_mess;
    }


    /** APPROVED *** */

    public function singleTranslation($lang = 'ar')
    {
        // can be overrridden
        return $this->transClassSingle($lang);
    }






    public function transClassSingle($lang = 'ar', $short = false)
    {
        $tableLower = strtolower(static::$TABLE);
        $classLower = strtolower(AfwStringHelper::tableToClass(static::$TABLE));

        $classSingleOrigin = $classLower . '.single';

        if ($short) {
            $classSingle = $classSingleOrigin . '.short';
        } else {
            $classSingle = $classSingleOrigin;
        }

        $return = $this->translate($classSingle, $lang);
        // if($lang=="en") die("$return = this->translate($classSingle, $lang)");

        if ($return == $classSingle and $short) {
            $classSingle = $classSingleOrigin;
            $return = $this->translate($classSingle, $lang);
        }

        if ($return == $classSingle) {
            $return = AfwStringHelper::toEnglishText(trim($tableLower));
        }

        return $return;
    }

    /**
     * $maksour = false ex المسلمون
     * $maksour = true ex المسلمين
     */

    public function transClassPlural($lang = 'ar', $short = false, $maksour = false)
    {
        $tableLowerOrigin = strtolower(static::$TABLE);

        if ($short) {
            $tableLower = $tableLowerOrigin . '.short';
        } else {
            $tableLower = $tableLowerOrigin;
        }

        if ($maksour) {
            $tableLowerNotMaksour = $tableLower;
            $tableLower = $tableLower . "_";
        }

        $return = $this->translate($tableLower, $lang);
        if ($return == $tableLower and $maksour) {
            $tableLower = $tableLowerNotMaksour;
            $return = $this->translate($tableLower, $lang);
        }

        if ($return == $tableLower and $short) {
            $tableLower = $tableLowerOrigin;
            $return = $this->translate($tableLower, $lang);
        }

        if ($return == $tableLower) {
            $return = AfwStringHelper::toEnglishText(trim($return)) . 's';
        }

        return $return;
    }

    /**
     * delete
     * Delete row if beforeDelete event give authorization
     * @param int $id_replace is the ID of the replacement object if the delete is because of duplicated object
     * @return boolean true if the delete is done and false if not authorized
     */
    public function delete($id_replace = 0)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $objme = AfwSession::getUserConnected();

        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'can not call delete() method with virtual table : ' .
                    static::$TABLE .
                    '.'
            );
        } elseif (($delReturn=$this->userCanDeleteMe($objme)) <= 0) {
            $delReturnDecoded = self::decodeDeleteReturn($delReturn);
            throw new AfwRuntimeException(
                "the user [$objme] is not allowed to do delete operation on [" .
                    $this->getShortDisplay($lang)."] DEL-RETURN=$delReturnDecoded"
            );
        } else {
            

            $return = false;
            $this->majTriggered();
            if ($this->beforeDelete($this->id, $id_replace)) {
                $AUDIT_DISABLED = AfwSession::config("AUDIT_DISABLED", false);
                // for audit 
                if($this->AUDIT_DATA and !$AUDIT_DISABLED)
                {
                    $this->logicDelete();        
                }
                $query =
                    'DELETE FROM ' .
                    self::_prefix_table(static::$TABLE) .
                    " 
                       WHERE " .
                    $this->getPKField() .
                    " = '" .
                    $this->getAfieldValue($this->getPKField()) .
                    "'";
                $return = $this->execQuery($query);
                $this->majTriggered();
                //die("query : $query");
                $this->afterDelete(
                    $this->getAfieldValue($this->getPKField()),
                    $id_replace
                );
            }

            
        }
        return $return;
    }

    public function getPK()
    {
        return $this->getPKField();
    }

    final public static function removeWhere($where_clause)
    {
        return static::deleteWhere($where_clause);
    }

    /**
     * deleteWhere
     * Delete rows based on SQL condition
     * @param string $where : Clause where
     */
    final public static function deleteWhere($where)
    {

        if (!static::$TABLE) {
            throw new AfwRuntimeException('Impossible to call deleteWhere() with virtual entity for class '.get_called_class());
        } else {
            if ($where) {
                if (static::beforeDeleteWhere($where)) {
                    $query = 'DELETE FROM ' . self::_prefix_table(static::$TABLE) . ' WHERE ' . $where;
                    $return = self::executeQuery($query);
                    static::afterDeleteWhere($where);
                }

                return $return;
            } else {
                throw new AfwRuntimeException('Not allowed to call deleteWhere() without where param');
            }
        }
    }

    

    
    public function getDisplay($lang = 'ar')
    {
        return $this->getDefaultDisplay($lang);
    }

    final public function initDISPLAY_FIELD($lang="")
    {
        if((!$this->DISPLAY_FIELD) and isset($this->DISPLAY_FIELD_BY_LANG)) 
        {
            if(!$lang) $lang = AfwLanguageHelper::getGlobalLanguage();
            $this->DISPLAY_FIELD = $this->DISPLAY_FIELD_BY_LANG[$lang];
            // die(var_export($this->DISPLAY_FIELD, true)."=this->DISPLAY_FIELD = this->DISPLAY_FIELD_BY_LANG[$lang] this->DISPLAY_FIELD_BY_LANG=" . var_export($this->DISPLAY_FIELD_BY_LANG,true));
        }

        return $this->DISPLAY_FIELD;
    }

        

    final public function getDefaultDisplay($lang = 'ar', $implodeChar = " ")
    {
        $return = "";

        $this->initDISPLAY_FIELD($lang);
        $sep = $this->DISPLAY_SEPARATOR;
        if(!$sep) $sep = $implodeChar;
        //if ($this instanceof Applicant) die("df is ".var_export($this->DISPLAY_FIELD,true));
        if (!$this->id) {
            $return = $this->insertNewLabel($lang);

            // if ($this instanceof StudentFileStatus) $return .= "<!-- ".var_export($this,true)." -->";
            /*
            $return = $this->transClassSingle($lang) .
                ' ' .
                $this->translate('NEW', $lang, true);*/
        } 
        else
        {
            if (is_array($this->DISPLAY_FIELD) and count($this->DISPLAY_FIELD) > 0) {
                // if ($this instanceof Applicant) die("df is an array : ".var_export($this->DISPLAY_FIELD,true));
                $disp_decoded = [];
                foreach ($this->DISPLAY_FIELD as $key) {
                    $disp_decoded[] = $this->decode($key);
                }
                $return = implode($sep, $disp_decoded);
                if ($this instanceof Application) die("for instanceof Application return = $return because disp_decoded = ".var_export($disp_decoded,true)." from decode of this->DISPLAY_FIELD = ".var_export($this->DISPLAY_FIELD,true));
            } elseif ($this->DISPLAY_FIELD) {
                $return = $this->getVal($this->DISPLAY_FIELD);
            }
        }

        if (!$return) {
            if (is_array($this->UNIQUE_KEY) and count($this->UNIQUE_KEY) > 0) {
                $uk_decoded = [];
                foreach ($this->UNIQUE_KEY as $key) {
                    $uk_decoded[] = $this->decode($key);
                }
                $return = implode($sep, $uk_decoded);
                //if(AfwStringHelper::stringContain($return,"sara_4238_@hotmail.com")) throw new AfwRuntimeException("This is case of stange key display");
            } else {
                $return = $this->transClassSingle($lang) . ' ' . AfwStringHelper::arrow($lang) . ' ' . $this->id;
            }
        }


        if (!$return) {
            $return = $this->getMyClass() . " id " . $this->id;
        }
        return $return;
    }

    public function getDropDownDisplay($lang = 'ar')
    {
        return $this->getShortDisplay($lang);
    }

    public function getNodeDisplay($lang = 'ar')
    {
        return $this->getShortDisplay($lang);
    }

    public function getShortDisplay($lang = 'ar')
    {
        return $this->getDisplay($lang);
    }

    public function getRetrieveDisplay($lang = 'ar')
    {
        return $this->getShortDisplay($lang);
    }

    public function getWideDisplay($lang = 'ar')
    {
        return $this->getDisplay($lang);
    }

    /**
     * __toString
     * Display First Field
     */
    public function __toString()
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();

        return $this->getDefaultDisplay($lang);
    }

    /**
     * debug
     * Show debug
     * @param boolean $childrens : Optional, specify if we display or not object's childrens
     * @param string $indent : Optional, specify the indent to put in start line
     */
    public function debug($childrens = false, $indent = '')
    {
        $debug = '';
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $attribute) {
            $debug .=
                $indent .
                $attribute .
                ' : ' .
                ($this->getTypeOf($attribute) == 'FK' ||
                    $this->getTypeOf($attribute) == 'MFK'
                    ? $this->decode($attribute)
                    : $this->getVal($attribute)) .
                "\n";
            if (isset($this->OBJECTS_CACHE[$attribute]) && $childrens) {
                $debug .= $this->OBJECTS_CACHE[$attribute]->debug(
                    true,
                    $indent . "\t"
                );
            }
        }
        return $debug;
    }

    


    public function decodeTpl(
            $text_to_decode,
            $trad_erase = [],
            $lang = 'ar',
            $token_arr = []
    ) 
    {
        $token_arr = AfwPrevilegeHelper::prepareAfwTokens($this, $text_to_decode, $lang, $trad_erase, $token_arr);
        // if(get_class($this)=="Application") die("token_arr=".var_export($token_arr,true));
        foreach ($token_arr as $token => $val_token) {
            //if($token=="[travelStationList.no_icons]") die("for the token $token value is $val_token , token_arr = ".var_export($token_arr,true)." text_to_decode=$text_to_decode");
            $text_to_decode = str_replace($token, $val_token, $text_to_decode);
        }

        return $text_to_decode;
    }

    public function fullHijriDate($attribute)
    {
        return AfwDateHelper::fullHijriDate($this->getVal($attribute));
    }

    public function mediumHijriDate($attribute)
    {
        return AfwDateHelper::mediumHijriDate($this->getVal($attribute));
    }

    public function fullGregDate($attribute)
    {
        return AfwDateHelper::fullGregDate($this->getVal($attribute));
    }

    public function mediumGregDate($attribute)
    {
        return AfwDateHelper::mediumGregDate($this->getVal($attribute));
    }

    public function translateValue($attribute)
    {
        $attribute_val = $this->calc($attribute);
        $tvf_code = $attribute . '.' . $attribute_val;
        // die("attribute_val of $attribute = $attribute_val");
        return $this->translate($tvf_code);
    }

    // translate value of field
    public function tvf($attribute, $lang = 'ar')
    {
        $attribute_val = $this->calc($attribute);
        $tvf_code = $attribute . '.' . $attribute_val;
        return $this->tf($tvf_code, $lang);
    }


    public function showUsingPhpTemplate($html_template, $data)
    {
        $html_template_file = AfwStringHelper::getFileNameFullPath($html_template, static::$MODULE);
        if (file_exists($html_template_file)) {
            foreach ($data as $key => $kval) $$key = $kval;
            ob_start();
            include $html_template_file;
            $html_content = ob_get_clean();

            return $html_content;
        } else {
            throw new AfwRuntimeException("afw::showUsingPhpTemplate : $html_template_file not found");
        }
    }

    public function showUsingTpl(
        $html_template,
        $trad_erase = [],
        $lang = 'ar',
        $token_arr = []
    ) {
        // throw new AfwRuntimeException("token_arr = ".var_export($token_arr,true)." html_template=$html_template");
        //die("html_template=$html_template");
        $html_template_file = AfwStringHelper::getFileNameFullPath($html_template, static::$MODULE);
        if (file_exists($html_template_file)) {
            ob_start();
            include $html_template_file;
            $tpl_content = ob_get_clean();

            return $this->decodeTpl(
                $tpl_content,
                $trad_erase,
                $lang,
                $token_arr
            );
        } else {
            throw new AfwRuntimeException("afw::showUsingTpl : $html_template_file not found");
        }
    }

    public function showHTML($html_template = '', $data_template = null)
    {
        return AfwShowHelper::showObject(
            $this,
            $mode_affichage = 'HTML',
            $html_template,
            $color = false,
            $childrens = false,
            $decode = true,
            $virtuals = '',
            $indent = '',
            $data_template
        );
    }

    public function myDisplayStatus()
    {
        // to be overrridden in sublasses it allow to show panels, or boxes or frames that show me in a spcific css look
        return "nothing";
    }

    public function showMe($style = '', $lang = 'ar')
    {
        if ($style == 'retrieve') {
            return $this->getRetrieveDisplay($lang);
        }
        if ($style == 'short') {
            return $this->getShortDisplay($lang);
        }
        return $this->getDisplay($lang);
    }

    public function getTokenKeys($mode)
    {
        return [];
    }

    public function prepareTokensArrayFromRequestParams($mode)
    {
        $keys_arr = $this->getTokenKeys($mode);
        $token_arr = [];
        foreach ($keys_arr as $key) {
            $token_arr["[$key]"] = $_REQUEST[$key];
        }

        return $token_arr;
    }

    public function calcMinibox()
    {
        global $lang, $loop_counter;
        if (!$loop_counter) {
            $loop_counter = 0;
        } else {
            $loop_counter++;
        }
        if ($loop_counter > 10) {
            throw new AfwRuntimeException('seems infinite loop');
        }
        return AfwShowHelper::showMinibox($this, '', $lang);
    }

    

    public function tr($message, $lang = '')
    {
        if (!$lang) {
            $lang = AfwLanguageHelper::getGlobalLanguage();
        }

        $tr_message = $this->translateMessage($message, $lang);
        if (!$tr_message or $tr_message == $message) {
            $tr_message = $this->translateOperator($message, $lang);
        }

        if (!$tr_message or $tr_message == $message) {
            $tr_message = $this->translateText($message, $lang);
        }

        return $tr_message;
    }
    /**
     * translate
     * @param  string  $nom_col
     * @param  string  $langue
     * @return string
     */

    public function translate0($nom_col, $langue = 'ar', $operator = null)
    {
        return $nom_col;
    }

    public static function t($nom_col, $langue = 'ar', $operator = null)
    {
        $nom_table = static::$TABLE;
        $module = static::$MODULE;

        if(!$nom_col) $nom_col = $nom_table; // plural translation

        $return = AfwLanguageHelper::tarjem(
            $nom_col,
            $langue,
            $operator,
            $nom_table,
            $module
        );
        
        if(AfwStringHelper::stringStartsWith(trim($return), "??")
           and 
           AfwStringHelper::stringEndsWith(trim($return), "??"))
        {
            $return = AfwStringHelper::methodToTitle($nom_col);
        }
        $return_before = $return;
        $return = AfwReplacement::trans_replace($return, $module, $langue);

        /*if ($nom_col == 'trainingunittype.single') {
            throw new AfwRuntimeException("$return = AfwLanguageHelper::tarjem(col=$nom_col, lng=$langue, oper=$operator, tbl=$nom_table, module=$module) (intermediaire = $return_before)");
        }*/

        return $return;
    }

    public function translate($nom_col, $langue = 'ar', $operator = null)
    {
        return self::t($nom_col, $langue, $operator);
    }

    public function translateText($text, $langue = 'ar')
    {
        return $this->translate($text, $langue, false);
    }

    public function translateOperator($operator, $langue = 'ar')
    {
        $return = $this->translate($operator, $langue, true);
        if($return==$operator)
        {
            $return = $this->translate(strtoupper($operator), $langue, true);
        }
        return $return;
    }

    public function tm($message, $langue = '', $company = "")
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        return $this->translateMessage($message, $langue, $company);
    }

    public function tf($message, $langue = '')
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        $message_tm = $this->translate($message, $langue);
        return $message_tm;
    }

    public function translateMessage($message, $lang = 'ar', $company = "")
    {
        $module = static::$MODULE;
        if (!$module) throw new AfwRuntimeException("static::\$MODULE should be defined in class : " . get_class($this));
        return AfwLanguageHelper::translateCompanyMessage($message, $module, $lang, $company);
    }

    public static function transMess($message, $lang = 'ar', $company = "")
    {
        $module = static::$MODULE;
        if (!$module) throw new AfwRuntimeException("static::\$MODULE should be defined in class : " . static::class);
        return AfwLanguageHelper::translateCompanyMessage($message, $module, $lang, $company);
    }

    public function getAllMyDbStructure()
    {
        return $this->getMyDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );
    }

    public function getAllAttributes()
    {
        $this_db_structure = $this->getAllMyDbStructure();
        return array_keys($this_db_structure);
    }

    public function qsearchByTextEnabled()
    {
        return true;
    }

    public function setSpecialRetrieveCols()
    {
        // to be overriden in sub classes and define :
        $force_retrieve_cols = [];
        $hide_retrieve_cols = [];

        return [
            'force_retrieve_cols' => $force_retrieve_cols,
            'hide_retrieve_cols' => $hide_retrieve_cols,
        ];
    }
    

    /**
     * beforeDeleteWhere
     * method called before deleteWhere
     * @param string $where
     */
    protected static function beforeDeleteWhere($where)
    {
        return true;
    }

    /**
     * afterDeleteWhere
     * method called after deleteWhere
     * @param string $where
     */
    protected static function afterDeleteWhere($where) {}

    

    final public function canBeDeleted()
    {
        // 0,0 below to simulate delete not really delete (beforeDelete should be regenerated for old classes (before 13/3/2020) to generate simul param inside beforeDelete
        $this->majTriggered();
        $can = $this->beforeDelete(0, 0);
        if (
            !$can and
            !$this->deleteNotAllowedReason and
            ($objme = AfwSession::getUserConnected()) and
            $objme->isAdmin()
        ) {
            throw new AfwRuntimeException(
                "[$this] object can't be deleted without any reason specified" .
                    var_export($this, true)
            );
        }
        return [$can, $this->deleteNotAllowedReason];
    }

    /**
     * beforeDelete
     * method called before delete
     * @param string $id
     */
    public function beforeDelete($id, $id_replace)
    {
        $this->deleteNotAllowedReason =
            'beforeDelete should be implemented in the afw sub class : ' .
            static::$TABLE;
        return false;
    }

    /**
     * afterDelete
     * method called after delete
     * @param string $id
     */
    public function afterDelete($id, $id_replace) {}

    /**
     * beforeHide
     * method called before hide
     * @param string $id
     */
    public function beforeHide($id)
    {
        return true;
    }

    /**
     * afterHide
     * method called after hide
     * @param string $id
     */
    public function afterHide($id) {}

    public function beforeMaj($id, $fields_updated)
    {
        return true;
    }

    public function afterMaj($id, $fields_updated)
    {
        //to be implemented in sub-classes
    }



    /**
     * beforeInsert
     * method called before insert
     * @param string $id
     * @param array $fields_updated
     */
    public function beforeInsert($id, $fields_updated)
    {
        return $this->beforeMaj($id, $fields_updated);
    }

    /**
     * afterInsert
     * method called after insert
     * @param string $id
     * @param array $fields_updated
     */
    public function afterInsert($id, $fields_updated)
    {
        $this->afterMaj($id, $fields_updated);
    }

    /**
     * beforeUpdate
     * method called before update
     * @param string $id
     * @param array $fields_updated
     */
    public function beforeUpdate($id, $fields_updated)
    {
        return $this->beforeMaj($id, $fields_updated);
    }

    /**
     * afterUpdate
     * method called after update
     * @param string $id
     * @param array $fields_updated
     */
    public function afterUpdate($id, $fields_updated)
    {
        $this->afterMaj($id, $fields_updated);
    }

    public function getTypeOf($attribute)
    {
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        return $structure['TYPE'];
    }

    public function getCategoryOf($attribute)
    {
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        return $structure['CATEGORY'];
    }

    public function getStyle($table = '')
    {
        return 'afw_style.css';
    }

    public function getMyPrefixedTable()
    {
        return $this->getTableName(true);
    }

    public function getTableName($with_prefix = false)
    {
        if ($with_prefix) {
            return self::_prefix_table(static::$TABLE);
        } else {
            return static::$TABLE;
        }
    }

    public function dynamicVH()
    {
        return true;
    }

    final public function select_visibilite_horizontale_default(
        $dropdown = false,
        $selects = []
    ) {


        if ($dropdown or $this->hideDisactiveRowsFor($objme = AfwSession::getUserConnected())) {
            $selects[$this->fld_ACTIVE()] = 'Y'; // get_class($this).".".
        }
        /*
        if(static::$TABLE == "school_employee")
        { 
            echo "selects : <br>";
            echo var_export($selects,true);
            die();
        }*/
        foreach ($selects as $colselect => $valselect) {
            if (AfwStructureHelper::fieldExists($this, $colselect)) {
                //if($colselect == "employee_id") die("$this this->select($colselect,$valselect);");
                $this->select($colselect, $valselect);
            } else {
                throw new AfwRuntimeException(get_class($this)." : trying to sql-select the field '$colselect' but does not exist, selects =" . var_export($selects, true));
            }
        }
        /*
        if(static::$TABLE == "practice")
        { 
            die($this->SEARCH);
        }
        */
    }

    public function get_visibilite_horizontale($dropdown = false)
    {
        $this->select_visibilite_horizontale($dropdown);
        return $this->getSQL();
    }

    public function select_visibilite_horizontale($dropdown = false)
    {
        $selects = [];
        $this->select_visibilite_horizontale_default($dropdown, $selects);
    }

    public function getMyTable($prefix = false)
    {
        if ($prefix) return self::_prefix_table(static::$TABLE);
        else return static::$TABLE;
    }

    public function getMyClass()
    {
        return AfwStringHelper::tableToClass(static::$TABLE);
    }

    public function getMyModule()
    {
        //if(static::$TABLE=="acondition") die("for ".static::$TABLE." module is ".static::$MODULE);
        return static::$MODULE;
    }

    public static function getMyFactory()
    {
        return AfwStringHelper::getHisFactory(static::$TABLE, static::$MODULE);
    }



    public function getSeparatorFor($attribute)
    {
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        $sep = $structure['SEP'];
        if (!$sep) {
            $sep = self::$mfk_separator;
        }
        return $sep;
    }

    final public function userCanDeleteMeStandard($auser)
    {
        return AfwUmsPagHelper::userCanDoOperationOnObject($this, $auser, 'delete');
    }

    protected function userCanDeleteMeSpecial($auser)
    {
        // This is an example of business rules and conditions 
        // needed to be authorized to delete this record additional to UMS role/bf
        /*
        if ($auser->isAdmin()) {
            return true;
        }
        if ($auser->id == $this->getVal($this->fld_CREATION_USER_ID())) {
            return true;
        }
        return false;
        */

        // by default no specific rule for records
        // if UMS role/bf authorize delete so he can delete
        return true;
    }

    final private static function decodeDeleteReturn($ret)
    {
        if($ret==-1) return "UMS implementation does not allow this user to delete this record, see userCanDeleteMeStandard";
        if($ret==-2) return "The business rules and conditions of this afw-sub-class does not allow this user to delete this record see userCanDeleteMeSpecial";
        if($ret<=0) return "unknown no-delete reason $ret";

        return "YOU CAN DELETE !! so why get here ?";
        
    }

    final public function userCanDeleteMe($auser, $log = true)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $return = 1;
        // User roles check
        if (!$this->userCanDeleteMeStandard($auser)) {
            $return = -1;
        }
        // Business rules check
        if (($return > 0) and (!$this->userCanDeleteMeSpecial($auser))) {            
            // throw new AfwRuntimeException("return was $return and ".get_class($this)." -> userCanDeleteMeSpecial ($auser) failed ");
            $return = -2;
        }
        
        if ($log) {
            if ($return <= 0) {
                $returnDecoded = self::decodeDeleteReturn($return);
                AfwSession::contextLog(
                    sprintf(
                        $this->tm("user %d can't delete this object %s => return = %s"),
                        $auser->id,
                        $this->getShortDisplay($lang),
                        $returnDecoded
                    ),
                    'iCanDo'
                );
            } else {
                AfwSession::contextLog(
                    sprintf(
                        $this->tm(
                            '* success * : user %d can delete this object %s'
                        ),
                        $auser->id,
                        $this->getShortDisplay($lang)
                    ),
                    'iCanDo'
                );
            }
        }
        return $return;
    }

    protected function userCanEditMeWithoutRole($auser)
    {
        return [false, 'userCanEditMeWithoutRole not implemented'];
    }

    final public function userCanEditMeStandard($auser)
    {
        return AfwUmsPagHelper::userCanDoOperationOnObject($this, $auser, 'edit');
    }

    protected function userCanEditMeSpecial($auser)
    {
        // business rules and conditions needed to be authorized to edit this record additional to UMS role/bf
        return true;
    }

    protected function adminCanEditMe()
    {
        return [true, ''];
    }


    final public function userCanEditMe($auser)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        if ($auser and $auser->isAdmin()) {
            return $this->adminCanEditMe();
        }
        
        list(
            $editWithoutRole,
            $editWithoutRoleReason,
        ) = $this->userCanEditMeWithoutRole($auser);
        if ($editWithoutRole) {
            return [true, ''];
        }

        $editWithRole = $this->userCanEditMeStandard($auser);
        $editSpecial = $this->userCanEditMeSpecial($auser);
        if ($editWithRole and $editSpecial) {
            return [true, ''];
        }
        $auser_disp = $auser->getDisplay($lang);
        $this_disp = $this->getMyClass();
        // $mau = $auser->getMyModulesAndRoles($this->getMyModule());
        return [
            false,
            '1. ' .
                $editWithoutRoleReason .
                "<br>2. $auser_disp لا يملك صلاحية التحرير على " .
                $this_disp,
        ]; // ." mau = ".var_export($mau,true)
    }

    public function getVisibilteSpeciale($attribute)
    {
        return false;
    }

    public function getContextDisplay($lang = 'ar', $module = '')
    {
        return $this->getShortDisplay($lang);
    }

    public function displayMyLinkMode($mode = 'edit', $lang = 'ar')
    {
        return $this->getDisplay($lang) .
            ' ' .
            $this->showAttribute($mode, ['TYPE' => strtoupper($mode)]);
    }

    public function displayAttribute(
        $attribute,
        $merge = false,
        $lang = 'ar',
        $getlink = true
    ) {
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        return $this->showAttribute(
            $attribute,
            $structure,
            $merge,
            $lang,
            $getlink
        );
    }

    public function showYNValueForAttribute($ynCode, $key, $langue = '')
    {
        if(!$ynCode) return "";
        $lang = AfwLanguageHelper::getGlobalLanguage();
        // $objme = AfwSession::getUserConnected();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }
        $ynCodeForThis = "$key.$ynCode";
        $ynTranslationForThis = $this->translate($ynCodeForThis, $langue);
        
        // if($key=="attribute_1" and (!$ynCode)) throw new AfwRuntimeException("showYNValueForAttribute($ynCode, $key, $langue) : $ynTranslationForThis = this->translate($ynCodeForThis,$langue)");
        if ($ynTranslationForThis and $ynTranslationForThis != $ynCodeForThis) {
            return $ynTranslationForThis;
        }

        $return = $this->translateOperator($ynCode, $langue); // ." translation [$key][$lang][".$this->decode($key)."]"
        if($key=="attribute_1" and (!$ynCode)) throw new AfwRuntimeException("showYNValueForAttribute($ynCode, $key, $langue) : $return = this->translateOperator($ynCode,$langue)");
        return $return;
    }







    public function showMyLink($step = 0, $target = '', $what = "icon", $whatis = "view_ok", $mode = "edit")
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $val_id = $this->getId();
        $val_class = $this->getMyClass();
        $currmod = $this->getMyModule();

        if ($target) {
            $target = "target = '$target'";
        }

        if ($step) {
            $step_param = "&currstep=$step";
        } else {
            $step_param = '';
        }

        if ($what == "icon") $what = "<img src='../lib/images/$whatis.png' width='24' heigth='24'>";
        elseif ($what == "short") $what = $this->getShortDisplay($lang);
        elseif ($what == "long") $what = $this->getDisplay($lang);

        return "<div class='my_link'><a $target href='main.php?Main_Page=afw_mode_$mode.php$step_param&popup=&cl=$val_class&currmod=$currmod&id=$val_id' >$what</a></div>";
    }

    public function showAttribute(
        $attribute,
        $structure = null,
        $merge = true,
        $langue = '',
        $getlink = false,
        $getFormatLink = true
    ) {
        $val_class = $this->getMyClass();
        $lang = AfwLanguageHelper::getGlobalLanguage();
        //$objme = AfwSession::getUserConnected();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        $this->debugg_last_attribute = $attribute;

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($this, $structure, $attribute);
        }

        if (!$this->id_origin) {
            $id_origin = $this->getId();
            $class_origin = $this->getMyClass();
            $module_origin = $this->getMyModule();
        } else {
            $id_origin = $this->id_origin;
            $class_origin = $this->class_origin;
            $module_origin = $this->module_origin;
        }
        $key = $attribute;
        //if(($structure["TYPE"] == "ENUM") and ($structure["HZM-CSS"])) die("key=$key, TYPE=".$structure["TYPE"].", HZM-CSS=".$structure["HZM-CSS"]);

        $intelligent_category = $structure['SUPER_CATEGORY'];
        if (!$intelligent_category) {
            $intelligent_category = $structure['SUB-CATEGORY'];
        }
        if (!$intelligent_category) {
            $intelligent_category = $structure['CATEGORY'];
        }

        if (($intelligent_category == 'ITEMS') or ($intelligent_category == 'FORMULA')) {
            $value = '';
            if($intelligent_category == 'FORMULA') $value = $this->calc($key);
            $formatted = false;
        } else {
            // in case of we use shortname
            // ?? be more clear in your comments please, what means above (RB 21/02/2025)

            // in case of we use calcuated field
            if($this->shouldBeCalculatedField($key))
                $value = $this->calc($key);
            else
                $value = $this->getVal($key);
            // if($key == "adm_orgunit_id") die("$value = this->getVal($key)");
            list($formatted, $data_to_display, $link_to_display,) = AfwFormatHelper::formatValue($value, $key, $structure, $getFormatLink, $this);
            // if($key == "adm_orgunit_id") die("dbg 55477 rafik : list($formatted, $data_to_display, $link_to_display,) = AfwFormatHelper::formatValue($value, $key, ..)");
        }


        if ($formatted) {
            //if($key=="price5") throw new AfwRuntimeException("how we get here ???? data_to_display = $data_to_display = AfwFormatHelper::formatValue($value,$key, $structure, $getFormatLink)");
            // done
        } elseif ($structure['TYPE'] == 'FK') {
            if (empty($structure['CATEGORY'])) {
                list($data_to_display, $link_to_display) = AfwShowHelper::showFK($this, $attribute, $value, $langue, $structure, $getlink);
                // $data_to_display .= " comes from showFK";
            } else {
                list($data_to_display, $link_to_display) = AfwShowHelper::showVirtualAttribute($this, $attribute, $intelligent_category, $value, $id_origin, $class_origin, $module_origin, $langue, $structure, $getlink);
                /*
                if($key == "adm_orgunit_id" and $getlink) throw new AfwRuntimeException("dbg 44599925 rafik : list($data_to_display, $link_to_display) = AfwShowHelper::showVirtualAttribute($this, attribute=$attribute, <br>
                                 intelligent_category=$intelligent_category, value=$value, id_origin=$id_origin, class_origin=$class_origin, module_origin=$module_origin, <br>
                                 langue=$langue, structure=$structure, getlink=$getlink)");*/
            }
            
        } elseif ($structure['TYPE'] == 'MFK') {
            list($data_to_display, $link_to_display) = AfwShowHelper::showMFK($this, $attribute, $langue, $structure, $getlink);
        } elseif ($structure['TYPE'] == 'YN') {
            $ynCode = strtoupper($this->decode($key));
            $data_to_display = $this->showYNValueForAttribute($ynCode, $key, $langue);
        } elseif ($structure['TYPE'] == 'PK') {
            if (!$structure['OFFSET']) {
                $data_to_display = $this->getId();
            } else {
                $data_to_display = $this->getId() + $structure['OFFSET'];
            }
        } elseif ($structure['TYPE'] == 'DEL') {
            list($data_to_display, $link_to_display) = AfwShowHelper::showDeleteButton($this, $attribute, $langue, $structure);
        } elseif ($structure['TYPE'] == 'SHOW') {
            list($data_to_display, $link_to_display) = AfwShowHelper::showDisplayButton($this, $attribute, $langue, $structure);
        } elseif ($structure['TYPE'] == 'EDIT') {
            list($data_to_display, $link_to_display) = AfwShowHelper::showEditButton($this, $attribute, $class_origin, $langue, $structure);
        } elseif ($structure['TYPE'] == 'ENUM') {
            list($data_to_display, $link_to_display) = AfwShowHelper::showEnum($this, $attribute, $value, $langue, $structure);
        }         
        else {
            $data_to_display = $this->decode($key);
            //if($key=="response_templates") die("data_to_display of ($key val:$value) is $data_to_display");
        }

        //if($attribute=="warning_nb") die("Rafik CSSED($cssed_to_class) : data_to_display of ($key) is $data_to_display");

        if (!$merge) {
            // if($key == "response_templates") throw new AfwRuntimeException("no merge we will return [$data_to_display, $link_to_display]");
            return [$data_to_display, $link_to_display];
        } else {            
            $return = AfwShowHelper::mergeDisplayWithLinks($data_to_display, $link_to_display, $structure, $val_class, "", $key);
            //if($key == "response_templates") throw new AfwRuntimeException("need merge we will return AfwShowHelper::mergeDisplayWithLinks($data_to_display, $link_to_display, $structure, $val_class) = $return");
            return $return;
        }
    }

    public function getMySpecialFields()
    {
        $arrMySpecialFields = [];

        return $arrMySpecialFields;
    }

    public function decodeText(
        $text_to_decode,
        $prefix = '',
        $add_cotes = true,
        $sepBefore = null,
        $sepAfter = null
    ) {
        global $module_config_token, $MODULE;
        $objme = AfwSession::getUserConnected();
        $me = $objme ? $objme->id : 0;

        $server_db_prefix = AfwSession::config('db_prefix', "default_db_");

        if (!$sepBefore) {
            $sepBefore = '§';
        }
        if (!$sepAfter) {
            $sepAfter = '§';
        }

        if (
            $text_to_decode && strpos($text_to_decode, $sepBefore) !== false and
            strpos($text_to_decode, $sepAfter) !== false
        ) {
            $arr_tokens = [];

            $special_token = $sepBefore . 'TODAY' . $sepAfter;
            if (($sepAfter == '§') and (strpos($text_to_decode, $special_token) !== false)) {
                $hijri_current_date = AfwDateHelper::currentHijriDate();
                $arr_tokens[$special_token] = $hijri_current_date;
            }


            $arr_tokens[$sepBefore . 'DBPREFIX' . $sepAfter] = $server_db_prefix;
            $arr_tokens[$sepBefore . 'ME' . $sepAfter] = $me;

            /* OBSOLETE
            if ($objme) {
                $arr_tokens[$sepBefore . 'CONTEXT_ID' . $sepAfter] =
                    $objme->contextObjId;
            }
            if ($objme) {
                $arr_tokens[$sepBefore . 'SUB_CONTEXT_ID' . $sepAfter] =
                    $objme->subContextId;
            }*/

            if (($sepAfter == '§') and $objme) {
                $special_token = $sepBefore . 'MY_COMPANY' . $sepAfter;
                if (strpos($text_to_decode, $special_token) !== false) {
                    $orgId = $objme->getMyOrganizationId();
                    $arr_tokens[$special_token] = $orgId;
                }

                $special_token = $sepBefore . 'EMPL' . $sepAfter;
                if (strpos($text_to_decode, $special_token) !== false) {
                    $empId = $objme->getEmployeeId($orgId);
                    $arr_tokens[$special_token] = $empId;
                }
            }

            /* @rafik obsolete rahoo 3ib
            if($mySemplObj) $sme = $mySemplObj->getId();
            else $sme = 0;
            
            $arr_tokens[$sepBefore."SME".$sepAfter] = $sme;*/
            $this_db_structure = static::getDbStructure(
                $return_type = 'structure',
                $attribute = 'all'
            );
            foreach ($this_db_structure as $fieldname => $struct_item) {
                $token = $fieldname;
                if ($prefix) {
                    $token = $prefix . '.' . $token;
                }
                $token = $sepBefore . $token . $sepAfter;
                if (strpos($text_to_decode, $token) !== false) {
                    $field_val = $this->calc($fieldname);
                    /* if($fieldname == "afield_type_id")
                    {
                        die("debugg rafik 2024092808 <br> [$field_val] = $this => calc($fieldname)");
                    }*/
                    if ($add_cotes and !$struct_item['NO-COTE'] and $struct_item['TYPE'] != 'PK') {
                        $val_token = "'" . $field_val . "'";
                    } else {
                        $val_token = $field_val;
                    }

                    $arr_tokens[$token] = $val_token;
                }
            }

            //if($text_to_decode == "id_module_type=5 and id_system = §goal_system_id§ and id_pm = §goal_domain_id§ ") die("arr_tokens = ".var_export($arr_tokens,true));

            $arr_spec_fields = $this->getMySpecialFields();
            foreach (
                $arr_spec_fields
                as $spec_field_name => $spec_field_value
            ) {
                $token = $sepBefore . $spec_field_name . $sepAfter;

                if (strpos($text_to_decode, $token) !== false) {
                    if ($add_cotes) {
                        $val_token = "'" . $spec_field_value . "'";
                    } else {
                        $val_token = $spec_field_value;
                    }

                    $arr_tokens[$token] = $val_token;
                }
            }
            //die(var_export($module_config_token,true));
            foreach ($module_config_token as $mc_token => $mc_token_value) {
                $arr_tokens[$sepBefore . 'module_config_token_' . $mc_token . $sepAfter] = $mc_token_value;

                //if($mc_token=="file_types") die("arr_tokens = ".var_export($arr_tokens,true));
            }

            $file_types = AfwFileUploader::getDocTypes($this->getMyModule());
            $arr_tokens[$sepBefore ."file_types". $sepAfter] = implode(",",$file_types);

            // we start now the decode
            foreach ($arr_tokens as $token_item => $token_val) {
                // if($this->MY_DEBUG) AFWDebugg::log("text_to_decode before decode of $fieldname for $token with $val_token : $text_to_decode");
                $text_to_decode = str_replace(
                    $token_item,
                    $token_val,
                    $text_to_decode
                );
                // if($this->MY_DEBUG) AFWDebugg::log("text_to_decode after decode $text_to_decode");
            }
        }
        return $text_to_decode;
    }

    public function getLinkForAttribute(
        $table,
        $id,
        $operation,
        $module_code,
        $check_authorized = false,
        $step_link = ''
    ) {
        global $_SERVER;

        $classe = AfwStringHelper::tableToClass($table);
        if (!$module_code) {
            throw new AfwRuntimeException(
                "getLinkForAttribute($table,$id,$operation,[$module_code]) should specify module"
            );
        }
        if (!$operation) {
            throw new AfwRuntimeException(
                "getLinkForAttribute($table,$id,[$operation],$module_code) should specify action mode"
            );
        }

        $return = '';
        $objme = AfwSession::getUserConnected();
        if ($step_link === true) {
            $step_link = '';
        }
        //@todo cela ne suffit pas car pour les modes par id=$id il faut tester la visibilite horizontale aussi
        if (
            !$check_authorized or
            $objme and $objme->iCanDoOperation($module_code, $table, $operation)
        ) {
            $return =
                "main.php?Main_Page=afw_mode_$operation.php&cl=$classe&currmod=$module_code&id=" .
                $id .
                $step_link;
        }

        return $return;
    }

    final public function getOtherLinksForUser(
        $mode,
        $auser,
        $genereLog = false,
        $step = "all"
    ) {
        $final_other_links_arr = [];
        if (!$auser) return $final_other_links_arr;
        $other_links_arr = $this->getOtherLinksArray($mode, $genereLog, $step);
        // if($mode=="mode_responseList") die("Maintenance is ongoing : this->getOtherLinksArray($mode, $genereLog, $step) => other_links_arr = ".var_export($other_links_arr,true));
        $count_other_links_arr = count($other_links_arr);

        if ($genereLog) {
            AfwSession::contextLog(
                "for mode $mode count other_links_arr = $count_other_links_arr other_links_arr = " .
                    var_export($other_links_arr, true),
                'otherLink'
            );
        }


        foreach ($other_links_arr as $other_link) {
            $other_link_authorized = false;

            if (
                !$other_link['PUBLIC'] and
                !$other_link['SUPER-ADMIN-ONLY'] and
                !$other_link['ADMIN-ONLY'] and
                !$other_link['BF-ID'] and
                !$other_link['OWNER'] and
                (!is_array($other_link['UGROUPS']) or
                    !count($other_link['UGROUPS']))
            ) {
                $name_bf = [];
                $name_bf['ar'] = $other_link['TITLE'];
                $name_bf['en'] = $other_link['TITLE_EN'];
                // $other_link["PARAMS-IN-BF"] = true means if params of url change the BF change also it is not anymore the same
                $params_in_bf = $other_link['PARAMS-IN-BF'];
                // $other_link["CRE-NOT-FOUND-BF"] means to try to find BF ID of url and apply UMS rules
                // if not found the BF will be created if names are provided ($name_bf)
                // to avoid to use too much for optimisation
                // put provide your other_link with BF-ID
                $create_not_found_bf = $other_link['CRE-NOT-FOUND-BF'];

                if (!$other_link['MODULE']) $other_link['MODULE'] = static::$MODULE;

                list($bf_id, $params) = AfwUrlManager::decomposeUrl(
                    $other_link['MODULE'],
                    $other_link['URL'],
                    $create_not_found_bf,
                    $name_bf,
                    $params_in_bf
                );

                if ($bf_id > 0) {
                    $other_link['BF-ID'] = $bf_id;
                } else {
                    $other_link['SUPER-ADMIN-ONLY'] = true;
                }
            }

            $condition = $other_link['CONDITION'];
            if ($condition) {
                $condition_success = $this->$condition();
            } else {
                $condition_success = true;
            }
            if ($condition_success) {
                if (
                    !$other_link['ADMIN-ONLY'] and !$other_link['SUPER-ADMIN-ONLY']
                ) {
                    /*
                    if($other_link["OWNER"])
                    {
                    die("$this : uid = ".$auser->getId()." oid = ".$this->getMyOwnerId());
                    }
                    */
                    $public = $other_link['PUBLIC'];
                    if (true) {
                        if(!$public)
                        {
                            $ican_do_bf =
                            ($other_link['BF-ID'] and
                                $auser->iCanDoBF($other_link['BF-ID']));
                            // not like for data records where if ugroups are not defined we authorize
                            // here for links user group(s) or at least 1 should be defined and user should belongs to one of this user groups
                            $belongs_to_ugroup =
                                ($other_link['UGROUPS'] and
                                    $auser->i_belong_to_one_of_ugroups(
                                        $other_link['UGROUPS'],
                                        $this
                                    ));
                            $user_is_owner =
                                ($other_link['OWNER'] and
                                    $auser->getId() == $this->getMyOwnerId());
                        }
                        else
                        {
                            $ican_do_bf = null;
                            $belongs_to_ugroup = null;
                            $user_is_owner = null;
                        }
                        

                        if ($public or
                            $ican_do_bf or
                            $belongs_to_ugroup or
                            $user_is_owner
                        ) {
                            $attribute_related = $other_link['ATTRIBUTE_WRITEABLE'];
                            if ($ican_do_bf) {
                                $other_link['AUTH_TYPE'] = 'i-can-do-bf';
                            }
                            if ($belongs_to_ugroup) {
                                $other_link['AUTH_TYPE'] = 'belongs-to-ugroup';
                                // die("other_link = ".var_export($other_link,true));
                            }
                            if ($user_is_owner) {
                                $other_link['AUTH_TYPE'] = 'user-is-owner';
                            }
                            if ($public) {
                                $other_link['AUTH_TYPE'] = 'public';
                            }

                            if (!$attribute_related) {
                                $other_link_authorized = true;
                            } else {
                                list($other_link_authorized, $reason,) = AfwStructureHelper::attributeIsWriteableBy($this, $attribute_related, $auser);
                                if ($other_link_authorized) {
                                    $other_link['AUTH_TYPE'] .=
                                        "-$attribute_related-writeable-by-user" .
                                        $auser->id;
                                } else {
                                    $other_link['AUTH_TYPE'] = '';
                                }
                            }
                        } else {
                            if ($genereLog) {
                                AfwSession::contextLog(
                                    'not authorised : ' . var_export($other_link, true),
                                    'otherLink'
                                );
                            }
                            $other_link['AUTH_TYPE'] = '';
                            $reason = "not public and can't do bf and not belongs to ugroup and user is not owner";
                        }
                    }

                    // if($other_link["BF-ID"]==102308) echo $auser->showArr($objme->iCanDoBFLog);
                } else {
                    if ($other_link['SUPER-ADMIN-ONLY']) {
                        if ($auser->isSuperAdmin()) {
                            $other_link_authorized = true;
                            $other_link['AUTH_TYPE'] = 'super-admin';
                        } else {
                            if ($genereLog) {
                                AfwSession::contextLog(
                                    'authorised only for super-admin : ' .
                                        var_export($other_link, true),
                                    'otherLink'
                                );
                            }
                        }
                    }

                    if ($other_link['ADMIN-ONLY']) {
                        if ($auser->isAdmin()) {
                            $other_link_authorized = true;
                            $other_link['AUTH_TYPE'] = 'admin';
                        } else {
                            if ($genereLog) {
                                AfwSession::contextLog(
                                    'authorised only for admin : ' .
                                        var_export($other_link, true),
                                    'otherLink'
                                );
                            }
                        }
                    }
                }
            } else {
                if ($genereLog) {
                    AfwSession::contextLog(
                        "condition $condition not applied : " . var_export($other_link, true),
                        'otherLink'
                    );
                }
                $other_link['AUTH_TYPE'] = '';
                $other_link_authorized = false;
                $reason = "condition $condition failed";
            }
            
            if($other_link['CODE']=="stop.and.debugg")
            {
                // die("other_link_authorized=$other_link_authorized reason=$reason AUTH_TYPE=".$other_link['AUTH_TYPE']);
            }

            if ($other_link_authorized) {
                if (!$other_link['AUTH_TYPE']) {
                    $other_link['AUTH_TYPE'] = 'unknown-authorisation-type';
                }
                if($other_link['URL'] != "@help")
                {
                    $other_link['URL'] = AfwUrlManager::encodeMainUrl($other_link['URL']);
                    $other_link['URL'] = $this->decodeText(
                        $other_link['URL'],
                        '',
                        false
                    );
                }

                $final_other_links_arr[] = $other_link;

                if($other_link['CODE']=="stop.and.debugg")
                {
                    // die("stop.and.debugg final_other_links_arr=".var_export($final_other_links_arr,true));
                }
                
            }
        }
        // die("final_other_links_arr=".var_export($final_other_links_arr,true));
        return $final_other_links_arr;
    }

    protected function getOtherLinksArray($mode, $genereLog = false, $step = "all")
    {
        return $this->getOtherLinksArrayStandard($mode, $genereLog, $step);
    }


    protected final function getOtherLinksArrayStandard($mode, $genereLog = false, $step = "all")
    {
        return AfwWizardHelper::getOtherLinksArrayStandard($this, $mode, $genereLog, $step);
    }
    

    final public function getPublicMethodsForUser($auser, $mode = 'display')
    {
        $pbm_arr = $this->getPublicMethods();

        return UmsManager::getAllowedBFMethods($pbm_arr, $auser, $mode);
    }

    final public function getPublicMethodForUser($auser, $pMethodCode)
    {
        $pbm_arr = $this->getPublicMethodsForUser($auser, 'all');

        // code semble tres bete ci dessous
        foreach ($pbm_arr as $pbm_code => $pbm_item) {
            if ($pMethodCode == $pbm_code) {
                return $pbm_item;
            }
        }

        return null;
    }

    protected function getPublicMethods()
    {
        return [];
    }

    final public function executePublicMethodForUser(
        $auser,
        $pMethodCode,
        $lang
    ) {
        $pMethod = $this->getPublicMethodForUser($auser, $pMethodCode);
        if (!$pMethod) {
            return ["Error : codem invalid : [A$pMethodCode" . "B]", ''];
        }
        $pMethodName = $pMethod['METHOD'];
        if (!$pMethodName) {
            return ["Error : codem incomplete : [A$pMethodCode" . "B]", ''];
        }

        if ($pMethod['MAIN_PARAM']) {
            $pMethodParams = ['main_param' => $this->pbmethod_main_param];
        } else $pMethodParams = $pMethod['SEND_PARAMS'];

        if ($pMethodParams) {
            return $this->$pMethodName($pMethodParams, $lang);
        } else {
            return $this->$pMethodName($lang);
        }
    }

    public function insertNewLabel($lang)
    {
        $cl = $this->getMyClass();

        return $this->translate('INSERT', $lang, true) .
            ' ' .
            $this->singleTranslation($lang) .
            ' ' .
            $this->translate(strtolower("$cl.new"), $lang);
    }

    public function operationLabel($operation, $lang, $table_id)
    {
        $operation = '_' . strtoupper($operation);
        $operationTr = $this->translate($operation, $lang, true);

        $plural = false;

        if (AfwStringHelper::stringStartsWith($operation, '_')) {
            $plural = true;
        }

        if ($table_id) {
            $currAtable = Atable::getAtableById($table_id);
            if ($plural) {
                if (strtoupper($lang) == 'AR') {
                    $titre_u_field = 'titre_short';
                } else {
                    $titre_u_field = 'titre_short_en';
                }

                $cl_trans = $currAtable->getVal($titre_u_field);
            } else {
                if (strtoupper($lang) == 'AR') {
                    $titre_u_field = 'titre_u';
                } else {
                    $titre_u_field = 'titre_u_en';
                }

                $cl_trans = $currAtable->getVal($titre_u_field);
            }
        } else {
            return '';
        }

        return $operationTr . ' ' . $cl_trans;
    }

    /*
          userCan return if yes or no the user $auser who accessed module $from_module can do $operation on $this object
     
     */

    public function userCan($auser, $from_module, $operation)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();

        $from_module = ''; // car obsolete

        $file_dir_name = dirname(__FILE__);

        if (!$auser) {
            throw new AfwRuntimeException('user param can not be null in userCan method');
        }
        if (!$operation) {
            throw new AfwRuntimeException(
                'operation param can not be null in userCan method'
            );
        }

        if ($auser->isSuperAdmin()) {
            return [true, 99999999, ''];
        }

        $table = $this->getMyTable();
        $module = $this->getMyModule();

        return $auser->getUserCanTable($module, $table, $operation);
    }

    final protected function userCanDoOperationOnMeStandard(
        $auser,
        $operation,
        $operation_sql
    ) {
        AfwSession::contextLog(
            "userCanDoOperationOnMeStandard : getting ${operation_sql}_groups_mfk",
            'iCanDo'
        );
        $authorized_ugroups_val = $this->getVal("${operation_sql}_groups_mfk");
        $authorized_ugroups = $this->get("${operation_sql}_groups_mfk");
        $nb_authorized_ugroups = count($authorized_ugroups);
        AfwSession::contextLog(
            "userCanDoOperationOnMeStandard : got ${operation_sql}_groups_mfk : $authorized_ugroups_val (exploded to $nb_authorized_ugroups items)",
            'iCanDo'
        );
        if ($nb_authorized_ugroups == 0) {
            AfwSession::contextLog(
                "success : userCanDoOperationOnMeStandard($auser, $operation, $operation_sql) => true",
                'iCanDo'
            );
            return true;
        }

        $return_auth = $auser->i_belong_to_one_of_ugroups(
            $authorized_ugroups,
            $this
        );
        if (!$return_auth) {
            AfwSession::contextLog(
                "warning : userCanDoOperationOnMeStandard : fail user->i_belong_to_one_of_ugroups($authorized_ugroups_val,this) = false ",
                'iCanDo'
            );
        } else {
            AfwSession::contextLog(
                "success : userCanDoOperationOnMeStandard : user->i_belong_to_one_of_ugroups($authorized_ugroups_val,this) = false ",
                'iCanDo'
            );
        }

        return $return_auth;
    }

    public function userCanDoOperationOnMe(
        $auser,
        $operation,
        $operation_sql
    ) {
        /* we disable this until ugroups become used and sys-cached and optimized
        return $this->userCanDoOperationOnMeStandard(
            $auser,
            $operation,
            $operation_sql
        );*/

        return true;
    }

    public function canBePublicDisplayed()
    {
        return false;
    }

    public function canBeSpeciallyDisplayedBy($auser)
    {
        return false;
    }

    public function canBeDeletedWithoutRoleBy($auser)
    {
        return [false, 'canBeDeletedWithoutRoleBy - not implemented'];
    }


    public function getStructureObject($class, $id)
    {
        return self::getServerStructureObject($class, $id);
    }

    public static function getServerStructureObject($class, $id)
    {
        global $_SERVER;

        return $_SERVER['STR-OBJECTS'][$class][$id];
    }

    public function setStructureObject($class, $id, $obj)
    {
        self::setServerStructureObject($class, $id, $obj);
    }

    public function setServerStructureObject($class, $id, $obj)
    {
        global $_SERVER; //, $out_scr
        //$out_scr .= "<br>\nsetted $class [$id] = ".var_export($obj,true);
        $_SERVER['STR-OBJECTS'][$class][$id] = $obj;
    }







    public function getThisModuleAndAtable($id_main_sh = 0)
    {
        //$id_main_sh not needed anymore
        list(
            $mdl,
            $tbl,
            $mdl_id,
            $tbl_id,
            $mdl_new,
            $tbl_new,
        ) = AfwUmsPagHelper::getMyModuleAndAtable(
            $id_main_sh,
            static::$MODULE,
            static::$TABLE,
            false,
            false
        );

        return [$mdl, $tbl];
    }



    public function getTokenVal($token)
    {
        $me = AfwSession::getUserIdActing();

        if ($token == 'me') {
            return $me;
        }
    }

    public function showObjTech()
    {
        return "<pre style='text-align: left;float: left;direction: ltr'>" .
            var_export($this, true) .
            '</pre>';
    }

    public function debuggObj($obj)
    {
        throw new AfwRuntimeException('توقف لمشاهدة الكيان ' . $obj->showObjTech());
    }

    public function debuggObjList($objList, $attr = '', $show_array = true)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        $arr = [];

        foreach ($objList as $id => $obj) {
            if ($obj) {
                if ($attr) {
                    $obj_attr = $obj->getVal($attr);
                } else {
                    $obj_attr = $obj->getDisplay($lang);
                }
            } else {
                $obj_attr = $obj;
            }

            $arr[$id] = $obj_attr;
        }

        if ($show_array) {
            echo $this->showArr($arr);
        } else {
            $this->debuggObj($arr);
        }
    }

    public function showMyProps()
    {
        return $this->showArr($this->getAllfieldValues());
    }

    public function showArr($arr)
    {
        $html =
            '<table  width="60%" class="grid" cellspacing="3" cellpadding="4" style="background-color: white;">';
        foreach ($arr as $key => $val) {
            $html .= "<tr><th align='right' width='40%'>$key</th><td align='right' width='60%'>$val</td></tr>";
        }
        $html .= '</table>';

        return $html;
    }



    public function dataFollowConstraint($val_attr, $constraint)
    {
        if ($val_attr == '') {
            return true;
        } // in this case MANDATORY property that will reject
        list($operator, $params) = explode(';', $constraint);
        $param_arr = explode(',', $params);

        if (strtoupper($operator) == 'F-BETWEEN') {
            return floatval($val_attr) >= floatval($param_arr[0]) and
                floatval($val_attr) <= floatval($param_arr[1]);
        }

        if (strtoupper($operator) == 'I-BETWEEN') {
            return intval($val_attr) >= intval($param_arr[0]) and
                intval($val_attr) <= intval($param_arr[1]);
        }

        if (strtoupper($operator) == 'BETWEEN') {
            return $val_attr >= $param_arr[0] and $val_attr <= $param_arr[1];
        }

        throw new AfwRuntimeException(
            "Unknown operator $operator in constraint $constraint"
        );
    }

    public function dataFollowConstraints($val_attr, $constraints_arr)
    {
        foreach ($constraints_arr as $constraint) {
            if (!$this->dataFollowConstraint($val_attr, $constraint)) {
                return $constraint;
            }
        }

        return null;
    }


    /**
     * @description
     *  to be overridden to implement when attribute input is readonly and/or disabled
     * 
     */

    public function disableOrReadonlyForInput($field_name, $col_struct)
    {
        return '';
    }

    // NO-ERROR-CHECK : option to disable error check on attribute

    

    

    

    public function isComplete(
        $lang = 'ar',
        $step = 'all',
        $consider_bad_format_as_empty = true
    ) {
        $this_db_structure = static::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );
        foreach ($this_db_structure as $attribute => $desc) {
            if (AfwStructureHelper::isRealAttribute($this, $attribute, $desc)) {
                if (
                    $step == 'all' or
                    $desc['STEP'] == 'all' or
                    $desc['STEP'] == $step
                ) {
                    $val_attr = $this->getVal($attribute);
                    if (
                        $desc['MANDATORY'] and
                        $this->attributeIsApplicable($attribute) or
                        $desc['REQUIRED']
                    ) {
                        if (!$val_attr) {
                            return false;
                        }

                        if (
                            $consider_bad_format_as_empty and
                            AfwFormatHelper::isFormatted($desc)
                        ) {
                            list(
                                $correctFormat,
                                $correctFormatMess,
                            ) = AfwFormatHelper::isCorrectFormat($val_attr, $desc);
                            if (!$correctFormat) {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    final public function stepContainAttribute($step, $attribute, $desc = null)
    {
        if($step=='all') return true; // optimisation
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        return AfwStructureHelper::attributeBelongToStep($attribute, $desc, $step);
    }

    final public function stepOfAttribute($attribute, $desc = null)
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }
        if (!$desc['STEP']) {
            return 1;
        } else {
            return $desc['STEP'];
        }
    }

    public function getMySpecificDataErrors($lang = 'ar',
        $show_val = true,
        $step = 'all',
        $erroned_attribute = null,
        $stop_on_first_error = false,
        $start_step = null,
        $end_step = null)
    {
        return $this->getSpecificDataErrors($lang,
            $show_val,
            $step,
            $erroned_attribute,
            $stop_on_first_error,
            $start_step,
            $end_step);
    }
    

    // Action :
    // Check specific not known errors
    // Should be overwritten -if needed- by the child classes
    protected function getSpecificDataErrors(
        $lang = 'ar',
        $show_val = true,
        $step = 'all',
        $erroned_attribute = null,
        $stop_on_first_error = false,
        $start_step = null,
        $end_step = null
    ) {
        return [];
    }

    

    public function getDataErrorForAttribute($attribute)
    {
        return $this->arr_erros['all'][$attribute];
    }

    public function setDataErrorForAttribute($attribute, $error)
    {
        $this->arr_erros['all'][$attribute] = $error;
    }

    protected function paggableAttribute($attribute, $structure)
    {
        // can be overridden in subclasses
        return [true, ""];
    }

    public function attributeIsToPag($attribute, $structure = null)
    {
        list($paggable, $reason) = $this->paggableAttribute($attribute, $structure);
        if (!$paggable) {
            return [false, $reason];
        }
        if ($this->isTechField($attribute)) {
            return [false, 'isTechField'];
        }
        if ($this->isAdminField($attribute)) {
            return [false, 'isAdminField'];
        }
        if ($attribute == $this->getPKField()) {
            return [false, 'isPKField'];
        }
        if ($attribute == $this->getVirtualPKField()) {
            return [false, 'isVirtualPKField'];
        }
        if ($this->isSystemField($attribute)) {
            return [false, 'isSystemField'];
        }

        if (!$structure) $structure = AfwStructureHelper::getStructureOf($this, $attribute);

        if ($structure["OBSOLETE"]) {
            return [false, 'obsolete'];
        }

        return [true, ''];
    }

    public function pagMe($id_main_sh, $updateIfExists = false, $restrictToField = "")
    {
        $this_db_structure = static::getDbStructure(
            $return_type = 'structure',
            'all'
        );
        return AfwUmsPagHelper::pagObject($this, $this_db_structure, static::$MODULE, static::$TABLE, $id_main_sh, $updateIfExists, $restrictToField);
    }



    public function setContextAndPartitionCols($part_cols, $context_cols)
    {
        if ($part_cols) {
            $this->PARTITION_COLS = explode(',', $part_cols);
        }
        if ($context_cols) {
            $this->CONTEXT_COLS = explode(',', $context_cols);
        }
    }

    public function getContextCols()
    {
        return $this->CONTEXT_COLS;
    }

    protected function getGroupDefinitionObjects($my_type_id)
    {
        return null;
    }

    protected function userBelongToMe($auser, $my_type_id)
    {
        return false;
    }

    final public function userBelongToGroupDefinition($auser, $my_type_id)
    {
        $groupDefinitionObjects = $this->getGroupDefinitionObjects($my_type_id);

        if (!$groupDefinitionObjects) {
            throw new AfwRuntimeException(
                "group definition object(s) method should be overriden in this class and then defined for user group type : $my_type_id"
            );
        }
        if (count($groupDefinitionObjects) == 1) {
            $groupDefinitionObject = $groupDefinitionObjects[0];
            // throw new AfwRuntimeException("group definition object unique for [$this] for user group type : $my_type_id = ".var_export($groupDefinitionObject,true)." to be belong-checked with ".var_export($auser,true));
            return $groupDefinitionObject->userBelongToMe($auser, $my_type_id);
        }
        if (count($groupDefinitionObjects) > 1) {
            throw new AfwRuntimeException(
                'case group definition with multi objects is not implemented yet'
            );
        }

        return true;
    }

    protected function hideDisactiveRowsFor($auser)
    {
        return !$auser or !$auser->isAdmin();
    }

    

    // هنا نتكلم  عن البيانات في العمود بحسب السجل
    final public function answerTableForAttributeIsPublic(
        $attribute,
        $structure = null
    ) {
        $mycls = $this->getMyClass();
        if (!$structure) {
            $case = "getStructureOf";
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $case = "repareMyStructure";
            $structure = AfwStructureHelper::repareMyStructure($this, $structure, $attribute);
        }
        if ((!$structure) or (!$structure["ANSWER"])) {
            throw new AfwRuntimeException("$mycls : No asnwer table for attribute $attribute (case $case) : str = " . var_export($structure, true));
        }
        $cl = AfwStringHelper::tableToClass($structure["ANSWER"]);
        if (!$cl) {
            throw new AfwRuntimeException("$mycls : No asnwer class for attribute $attribute (case $case) : str = " . var_export($structure, true));
        }
        $obj = new $cl();

        if ($obj) return $obj->public_display;
        else return null;
    }

    

    public function quickRetrieveMethod()
    {
        return 'qshow';
    }

    public function getPercentEdited()
    {
        return round((100 * $this->getDoneSteps(-1)) / $this->editNbSteps);
    }

    public function getLastEditedStep($simulation = true)
    {
        // pb resolved : tabs inactive au milieu (mode edit)
        // code a revoir
        // if($this->isDraft()) return 1;

        if ($this->getVal('sci_id') > 0) {
            return $this->getVal('sci_id');
        } elseif ($simulation) {
            // die("table=static::$TABLE  editByStep=$this->editByStep ");
            return $this->getDoneSteps();
        } else {
            return ($this->id > 0) ? 1 : 0;
        }
    }

    /**
     *  the overridden version should be generated by command genere-php
     *  */
    public function getScenarioItemId($currstep)
    {
        return $currstep; // obsolete 
    }


    public function setLastEditedStep($currstep)
    {
        //$sci_id = $this->getScenarioItemId($currstep);
        $this->set('sci_id', $currstep);
    }

    public function getDoneSteps($error_offset = 0)
    {
        $lang = AfwLanguageHelper::getGlobalLanguage();
        //die("getDoneSteps for static::$TABLE ");
        if ($this->editByStep) {
            for ($istep = 1; $istep <= $this->editNbSteps; $istep++) {
                // die("istep=$istep before getStepErrors ");
                $err_arr = AfwDataQualityHelper::getStepErrors($this, 
                    $istep,
                    $lang,
                    $show_val = true,
                    $recheck = true
                );
                // die(var_export($err_arr,true));
                if (count($err_arr) > 0) {
                    return $istep + $error_offset;
                }
            }

            return 9999;
        } else {
            return 1;
        }
    }

    

    

    

    public function getHLClass()
    {
        return '';
    }



    public function editAction()
    {
        return ['edit', 'afw_mode_edit.php'];
    }

    public function displayAction()
    {
        return ['display', 'afw_mode_display.php'];
    }

    public function deleteAction()
    {
        return ['delete', ''];
    }

    public function getSpecificActions($step)
    {
        $actions_tpl_arr = [];

        return $actions_tpl_arr;
    }

    public function hasNoChild()
    {
        return true;
    }

    public function iconIsFileOrFolder()
    {
        return false;
    }

    protected function getSpecialIconType()
    {
        return static::$TABLE;
    }

    public function getIconType()
    {
        if ($this->iconIsFileOrFolder()) {
            if ($this->hasNoChild()) {
                return 'file';
            } else {
                return 'folder';
            }
        } else {
            return $this->getSpecialIconType();
        }
    }

    protected function getMyModeView()
    {
        return 'display';
    }

    public function getFullId()
    {
        $modeView = $this->getMyModeView();
        $moduleName = $this->getMyModule();
        $className = $this->getMyClass();
        $myId = $this->getId();

        return "$moduleName-$className-$myId-$modeView";
    }



    public function getSQL()
    {
        return $this->SEARCH;
    }


    public function getSelectedValueForAttribute($attribute)
    {
        return $this->SEARCH_TAB[$attribute];
    }


    /*
    public function getSQLConditionsArray()
    {
        return $this->SEARCH_TAB;
    }*/





    protected function optimizeMyMemory()
    {
        // should be overriden in sub class to optimize specific sub class data
        unset($this->PARTITION_COLS);
        unset($this->CONTEXT_COLS);
    }

    protected function unOptimizeMyMemory()
    {
        // should be overriden in sub class to un-optimize specific sub class data
        //@todo : restore $this->PARTITION_COLS if exists;
        //@todo : restore $this->CONTEXT_COLS if exists;
    }

    // used to remove non utile data before strage of big number of afw objects in memory
    // to avoid out of memory crash
    final public function optimizeMemory()
    {
        unset($this->AUDIT_DATA);
        unset($this->IS_VIRTUAL);

        unset($this->FIELDS_UPDATED);
        unset($this->FIELDS_INITED);
        unset($this->SEARCH);
        unset($this->SEARCH_TAB);
        unset($this->OBJECTS_CACHE);
        unset($this->debuggs);
        // unset($this->gotItemCache);
        // unset($this->gotItems Cache);
        unset($this->OPTIONS);
        unset($this->arr_erros);

        // $this->removeStructure();
        $this->optimizeMyMemory();
    }

    final public function destroyData()
    {
        $this->deleteAfieldValues();
        global $tab_instances, $nb_instances;
        if ($tab_instances[get_class($this)]) {
            $tab_instances[get_class($this)]--;
        }
        if (!$nb_instances) {
            $nb_instances = 0;
        } else {
            $nb_instances--;
        }
    }

    final public function unOptimizeMemory()
    {
        $this->IS_VIRTUAL = strtolower(substr(static::$TABLE, 0, 2)) == 'v_';

        $this->restoreStructure();
        $this->unOptimizeMyMemory();
    }

    

    public function createCopy($lang = 'ar', $field_vals = [])
    {
        $this->resetAsCopy($field_vals);
        $this->insert();
        return ['', 'done'];
    }

    public function mySubType()
    {
        if ($this->SubTypesField and $this->getVal($this->SubTypesField)) {
            return $this->showAttribute($this->SubTypesField);
        }

        return null;
    }

    public function myCategory()
    {
        return 0;
    }



    public function getMiniBoxTemplateArr($mode = 'qedit')
    {
        // should be rewritten in sub classes
        return null;
    }

    public function noRelaodAfterRunOfMethod($methodCode)
    {
        return false;
    }

    public function isIndexAttribute($key_col)
    {
        foreach ($this->UNIQUE_KEY as $key_item) {
            if ($key_item == $key_col) return true;
        }

        return false;
    }

    public function getUniqueCode()
    {
        $val_arr = [];

        foreach ($this->UNIQUE_KEY as $key_item) {
            $val_arr[] = $this->getVal($key_item);
        }

        return implode('-', $val_arr);
    }

    public function getParentObject()
    {
        return null;
    }

    public function getAttributesFriendOf($obj)
    {
        $arrAttributes = [];

        if ($obj) {
            $tabName = $obj->getTableName();
            $class_db_structure = $this->getMyDbStructure();
            foreach ($class_db_structure as $nom_col => $desc) {
                if ($desc['ANSWER'] == $tabName) {
                    $arrAttributes[] = $nom_col;
                }
            }
        }

        return $arrAttributes;
    }

    public final function canInsert()
    {
        foreach ($this->UNIQUE_KEY as $attribute) {
            if($this->attributeIsRequired($attribute))
            {
                if(!$this->getVal($attribute)) return false;
            }
        }
        return true;
    }

    
    public function canSaveOnly($current_step)
    {
        return false;
    }

    public function getMySpecialIcon()
    {
        $className = $this->getMyClass();
        return [$className, "../lib/images/icon-$className.png"];
    }

    public function canGoToNextStep($next_step)
    {
        return true;
    }

    

    protected function getNextTabButtonCodes($step)
    {
        return ['NEXT_TAB' => true];
    }

    final public function getNextTabButtonLabel($step, $lang)
    {
        $codes = $this->getNextTabButtonCodes($step);
        $codes_trans = [];
        foreach ($codes as $code_trans => $code_trans_oper) {
            $codes_trans[] = $this->translate(
                $code_trans,
                $lang,
                $code_trans_oper
            );
        }

        return implode(' ', $codes_trans);
    }

    public function getDefaultStep()
    {
        return 0;
    }

    public function getForceDefaultStep()
    {
        return false;
    }

    public function getFinishButtonLabel(
        $lang,
        $nextStep,
        $form_readonly = 'RO'
    ) {
        return AfwWizardHelper::getFinishButtonLabelDefault($this,
            $lang,
            $nextStep,
            $form_readonly
        );
    }

    public function canFinishOnCurrentStep()
    {
        return !$this->finishOnlyLastStep or
            ($this->currentStep == $this->editNbSteps);
    }

    // if class is displayed in edit mode so we can finish wizard by saving and remaining in same current step
    public function canFinishAsSaveAndRemainInCurrentStep()
    {
        $className = $this->getMyClass();
        return AfwWizardHelper::classIsDisplayedInEditMode($className);
    }

    

    public function getReadOnlyFormFinishButtonLabel()
    {
        return '';
    }

    

    public function getNextStepAfterFinish($current_step)
    {
        // should keep same current step if we have no display mode (only edit mode)
        return $current_step;
    }

    public function getFieldGroupArr($lang = 'ar', $all = false)
    {
        $field_group_arr = [];
        $this_db_structure = static::getDbStructure($return_type = 'structure', $attribute = 'all');
        foreach ($this_db_structure as $nom_col => $desc) {
            if ($desc['FGROUP'] and (!$desc['ITEMS'] or $all)) {
                if (!$field_group_arr[$desc['FGROUP']]) {
                    $field_group_arr[$desc['FGROUP']] = $this->translate($desc['FGROUP'], $lang);
                }
            }
        }

        return $field_group_arr;
    }

    public function getFieldGroupInfos($fgroup)
    {
        return AfwWizardHelper::getFieldGroupDefaultInfos($fgroup);
    }

    

    public function getMyTheme()
    {
        return 'default';
    }



    // By default you have a wizard and steps should be ordered it means that you can
    // not go to step 3 if step 2 is not completed,
    // But if steps are independant and not ordered (not a wizard, ie you can go to step 3 even if step 2 is not completed)
    // than override this to return false

    public function stepsAreOrdered()
    {
        return true;
    }

    final public function myHzmCode($prefix)
    {
        $string = $prefix . static::$TABLE . $this->getId();
        return AfwStringHelper::hzmEncode($string);
    }

    public function getDeniedEditMessage($lang)
    {
        return '';
    }

    protected function actionAllowedForEmployees($action)
    {
        return [false, 'actionAllowedForEmployees not implemented'];
    }

    protected function actionAllowedForStudents($action)
    {
        return [false, 'actionAllowedForStudents not implemented'];
    }

    protected function actionAllowedForPublicCustomers($action)
    {
        return [false, 'never accept public customers to do this action'];
    }

    protected function actionAllowedForLoggedOut($action)
    {
        return [false, 'never accept logged out to do this action'];
    }

    final public function userConvenientForAction($auser, $action)
    {
        // if((!$auser) or ((!$auser->getEmployeeId()) and (!$auser->getStudentId()))) throw new AfwRuntimeException("is customer here : ".var_export($auser,true));

        if (!$auser) {
            return $this->actionAllowedForLoggedOut($action);
        }

        if ($this->actionAllowedForEmployees($action)) {
            if ($auser and $auser->getEmployeeId() > 0) {
                return [true, ''];
            }
        }

        if ($this->actionAllowedForStudents($action)) {
            if ($auser and $auser->getStudentId() > 0) {
                return [true, ''];
            }
        }

        return $this->actionAllowedForPublicCustomers($action);
    }

    final public function connectedUserConvenientForAction($action, $connectedUser = null)
    {

        if (!$connectedUser) {
            $objme = AfwSession::getUserConnected();
            return $this->userConvenientForAction($objme, $action);
        } else {
            return $this->userConvenientForAction($connectedUser, $action);
        }
    }

    

    public function previewAttribute($attribute, $desc = '', $max_length = 56)
    {
        return AfwShowHelper::previewAttribute($this, $attribute, $desc, $max_length);
    }

    public function showAttributeAsLinkMode($attribute, $mode = 'EDIT', $icon = '')
    {
        $structure = [];

        $structure['TYPE'] = $mode;
        if ($icon) {
            $structure['ICON'] = $icon;
        } else {
            $structure['LABEL'] = $this->getVal($attribute);
        }

        $structure['TARGET'] = 'mypopup';

        return $this->showAttribute($attribute, $structure);
    }



    public function enabledIcon($attribute, $icon, $structure = null)
    {
        return AfwWizardHelper::standardEnabledIcon($this, $attribute, $icon, $structure);
    }

    public function getMyPicture()
    {
        return '';
    }

    

    /**
     * This function should be overridden in sub classes if needed
     * if this fucntion return an attribute name the value of this attribute will css-style the <tr> row
     * in retrieve mode display if this object is in ITEMS category field
     *
     */
    public function rowCategoryAttribute()
    {
        return $this->fld_ACTIVE();
    }

    public function getCssClassName()
    {
        return substr($this->getTableName(), 0, 5);
    }



    public function getCurrentFrontStep()
    {
        return 1;
    }

    public function forceMode()
    {
        $this->force_mode = true;
    }

    public function getFloatVal($attribute)
    {
        return floatval($this->getVal($attribute));
    }

    public function getIntVal($attribute)
    {
        return intval($this->getVal($attribute));
    }

    public function getDefautDisplaySettings()
    {
        // to be overridden
        return null;
    }

    public function isLourde()
    {
        return false;
    }

    public function maxRecordsUmsCheck()
    {
        return 50;
    }

    // should be overridden only if the ums Check is absolutely needed in Retrieve Mode
    // because it make the QSearch page go very slow
    public function umsCheckDisabledInRetrieveMode()
    {
        return true;
    }

    public function forceShowRetrieveErrorsIfSmallListe()
    {
        return true;
    }


    public final function canCheckErrors($small_liste, $option_CHECK_ERRORS)
    {
        return (
            ($small_liste and $this->forceShowRetrieveErrorsIfSmallListe()) or
            (
                $this->showRetrieveErrors and
                (
                    $option_CHECK_ERRORS or
                    $this->forceCheckErrors or
                    $this->forceShowRetrieveErrors
                )
            )
        );
    }


    public function optimizeQEditLookups($submode = "", $fgroup = "")
    {
        // return true; // suceeded to load and optimize
        return false; // failed because not overrridden yet
    }

    public function qeditHeaderFooterEmbedded($submode = "", $fgroup = "")
    {
        return false;
    }

    public function setMySmallCacheForAttribute($attribute, $return)
    {
        $this->OBJECTS_CACHE[$attribute] = $return;
    }


    /** APPROVE-DELAYED *** */
    public function iAcceptAction($action)
    {
        //throw new AfwRuntimeException("i will AcceptAction");
        return true;
    }

    /**
     * override editToDisplay() method to use edit for display mode
     * 
     */
    public function editToDisplay()
    {
        return false;
    }

    public function stepCanBeLeaved($current_step, $reason, $pushError)
    {
        return true;
    }

    public function instanciated($numInstance)
    {
        return true;
    }

    public function applyFilter($filter)
    {
        if (!$filter) return true;
        else return false;
    }

    /*************************     private methods       ************************/


    /****************************************************************************/


    public function getCategorizedAttribute($attribute, $attribute_category, $attribute_type, $structure, $what, $format, $integrity, $max_items, $lang, $call_method = "")
    {
        return AfwFormatHelper::getCategorizedAttribute($this, $attribute, $attribute_category, $attribute_type, $structure, $what, $format, $integrity, $max_items, $lang, $call_method);
    }

    public function getNonExistingAttribute($attribute, $what)
    {
        if (strtolower($what) == 'value') {
            $return = $this->getAfieldValue($attribute);
        } else {
            throw new AfwRuntimeException(
                "attribute '" .
                    $attribute .
                    "' does not exist in structure of entity : " .
                    static::$TABLE .
                    ' : DB_STRUCTURE = ' .
                    var_export(self::getDbStructure(), true)
            );
        }

        return $return;
    }





    public function getMfkArray($attribute)
    {
        $val = trim($this->getVal($attribute), ',');
        if ($val) {
            $val_arr = explode(',', $val);
        } else {
            $val_arr = [];
        }

        return $val_arr;
    }

    public function count()
    {
        return AfwSqlHelper::aggregCount($this);
    }

    public function func(
        $function,
        $group_by = '',
        $throw_error = true,
        $throw_analysis_crash = true
    ) {
        return AfwSqlHelper::aggregFunction($this, $function, $group_by, $throw_error, $throw_analysis_crash);
    }


    public function getTechnicalNotes()
    {
            return $this->debugg_tech_notes;
    }

    /*********************************XXXXXXXXXXXXXXXXXXXXXXXX**************************** */

}
