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

    private static $mfk_separator = ',';

    private static $my_debugg_db_structure = null;

    // params

    
    /**
     *
     * Table name
     * @var string
     */

    protected $AUDIT_DATA = false;

    private $arr_erros = null;

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

    private $force_mode = false;


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
            $server_db_prefix = AfwSession::config('db_prefix', 'c0');
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

        $this->init_row();

        $this->general_check_errors = true;
    }

    function __destruct() {
        $this->optimizeMemory();
        $this->destroyData();
    }

    /**
     * init_row
     * called by constructor to init state of object after creation
     */
    public final function init_row()
    {
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $field_name) {
            $struct = AfwStructureHelper::getStructureOf($this, $field_name);
            $def_type = $struct['TYPE'];
            if ($def_type == 'FK' and !isset($struct['DEFAUT'])) {
                $def_val = 0;
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

            if ($def_val || $def_val_force) {
                $this->setAfieldValue($field_name, $def_val);
                $this->setAfieldDefaultValue($field_name, $def_val);
                //if(($field_name=="active") and static::$TABLE == "bus_seat") die("this->FIELDS_INITED after setAfieldDefaultValue($field_name, $def_val) = ".var_export($this->FIELDS_INITED,true));
            }
        }

        $this->initObject();
        //if(static::$TABLE == "bus_seat") die("this->FIELDS_INITED = ".var_export($this->FIELDS_INITED,true));
    }

    

    public function getMyAnswerTableAndModuleFor($attribute, $struct=null)
    {
        if(!$struct) $struct = $this->getMyDbStructure($return_type = 'structure',$attribute);

        if (!$struct['ANSWER']) {
            throw new AfwRuntimeException("Missed ANSWER property for attribute $attribute : getMyDbStructure => structure = " .var_export($struct, true));
        }

        return [$struct['ANSWER'], $struct['ANSMODULE']];
    }

    public static function answerTableAndModuleFor($attribute)
    {
        $struct = self::getDbStructure($return_type = 'structure', $attribute);
        if (!$struct['ANSWER']) {
            throw new AfwRuntimeException("Missed ANSWER property for attribute $attribute : answerTableAndModuleFor => structure = " .var_export($struct, true));
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
        $step = 'all', $start_step=null, $end_step=null
    ) {
        if ($return_type == 'shortnames') {
            $attribute = 'all';
            $this_short_names = [];
        }

        if ($return_type == 'shortcuts') {
            $attribute = 'all';
            $this_shortcuts = [];
        }

        $got_first_time = false;
        $class_name = static::class;
        $module_code = static::$MODULE;
        if (!$module_code) {
            $module_code = AfwUrlManager::currentURIModule();
        }
        $table_name = static::$TABLE;

        $debugg_db_structure = AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute, $step, $start_step, $end_step);
        //if(($table_name=="invester") and ($attribute=="city_id")) die($table_name." AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute) returned debugg_db_structure = ".var_export($debugg_db_structure,true));
        if (isset($debugg_db_structure)) {
            foreach ($debugg_db_structure as $key => $value) {
                if ($value['ANSWER'] and $value['TYPE'] != 'ANSWER') {
                    if (!$value['ANSMODULE']) {
                        $debugg_db_structure[$key]['ANSMODULE'] = $value['ANSMODULE'] = static::$MODULE;
                    }
                }
                if ($value['SHORTCUT'] and $return_type == 'shortcuts') {
                    $this_shortcuts[$key] = true;
                }

                if ($value['SHORTNAME'] and $return_type == 'shortnames') {
                    // first be sure the this short name is not already used as attribute
                    if ($debugg_db_structure[$value['SHORTNAME']]) {
                        AfwRunHelper::simpleError(
                            "the short name '" .
                                $value['SHORTNAME'] .
                                "' for attribute $key already used in the same class as attribute name."
                        );
                    }
                    $this_short_names[$value['SHORTNAME']] = $key;
                }
            }
        } else {
            AfwRunHelper::simpleError(
                "Check if DB_STRUCTURE is defined for $attribute attribute(s) for class " .
                    static::$TABLE .
                    '. static::DB_STRUCTURE = ' .
                    var_export(static::$DB_STRUCTURE, true)
            );
        }
        // if(($table_name=="invester") and ($attribute=="city_id")) die($table_name." AfwStructureHelper::constructDBStructure($module_code, $class_name, $attribute) returned debugg_db_structure = ".var_export($debugg_db_structure,true));
        if ($return_type == 'structure') {
            if ($attribute != 'all') {
                $struct = $debugg_db_structure[$attribute];
                // if(($table_name=="school") and ($attribute=="roomList")) die(static::$TABLE.", struct of $attribute (before repare) = ".var_export($struct,true)." debugg_db_structure = ".var_export($debugg_db_structure, true));
                if ($struct) {
                    $struct = AfwStructureHelper::repareStructure($struct);
                }
                // if(($table_name=="school") and ($attribute=="roomList")) die(static::$TABLE.", struct of $attribute (after repare) = ".var_export($struct,true)." debugg_db_structure = ".var_export($debugg_db_structure, true));
                return $struct;
            } else {
                foreach ($debugg_db_structure as $key => $struct) {
                    if ($key != 'all') {
                        $debugg_db_structure[$key] = AfwStructureHelper::repareStructure($struct);
                    }
                }

                return $debugg_db_structure;
            }
        } elseif ($return_type == 'shortnames') {
            return $this_short_names;
        } elseif ($return_type == 'shortcuts') {
            return $this_shortcuts;
        }

        return ["momken"=>"unknown-requested-return_type $return_type"];
        
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
        if(!$can) return [$can, $reason];

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

    public static final function executeQuery($sql_query, $throw_error = true,$throw_analysis_crash = true) 
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
            $server_db_prefix = AfwSession::config('db_prefix', 'c0');        
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
        if (AfwStructureHelper::fieldExists($this,$this->fld_ACTIVE())) {
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
        if (AfwStructureHelper::fieldExists($this,$this->fld_ACTIVE())) {
            $this->set($this->fld_ACTIVE(), 'N');
            
            if ($commit) {
                
                return $this->update($only_me);
            }
        }
        else
        {
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
        if (AfwStructureHelper::fieldExists($this,$this->fld_ACTIVE())) {
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
        return $attribute == 'lookup_code' or
            $attribute == $this->fld_ACTIVE() or
            $attribute == 'draft';
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


    /*
        syncSameFieldsWith :
        I (this) take from him (obj) only what I need
        but after
        He (obj) take from me (this) all my fields except primary key and unique index columns
        so (this) is the master

    */

    public function syncSameFieldsWith($obj, $commit_obj = true, $commit_this = false)
    {
        $exception_fields = null;
        // I take from him only what I need (all fields not filled except primary key and unique index columns);
        $fields1 = $this->copyDataFrom($obj, $exception_fields, $avoid_if_filled_fields = "all");
        // and after he take from me all my fields (except primary key and unique index columns)
        $fields0 = $obj->copyDataFrom($this, $exception_fields, $avoid_if_filled_fields = []);

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
        $avoid_unique_index = true
    ) {
        $field_name_to_debugg = "prof_id-xxx-rr";
        $fields_updated = [];
        $all_real_fields = AfwStructureHelper::getAllRealFields($this);
        foreach ($all_real_fields as $field_name) {
            list($is_category_field, $is_settable) = AfwStructureHelper::isSettable($this,
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
                    $erase_even_if_filled = (($avoid_if_filled_fields != "all") and (!$avoid_if_filled_fields[$field_name]));
                    if (!$old_val or $erase_even_if_filled) {
                        $val = $obj->getVal($field_name);
                        if ($val and ($val !== $old_val)) {
                            $this->set($field_name, null);
                            $this->set($field_name, $val);
                            $fields_updated[] = $field_name;
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

    protected function initObject()
    {
        return true;
    }

    public function afterLoad()
    {
        return true;
    }

    private function setAfieldValue($field_name, $value, $reset = false)
    {
        // if(!isset($this->AFIELD _VALUE)) $this->AFIELD _VALUE = array();
        /*
        if(static::$TABLE == "cher_file") 
        {
            if(($field_name == "active") and (!$reset) and (!$value)) throw new AfwRuntimeException("case 2021-10-20 cher_file found for debugg");
        }*/
        $this->AFIELD_VALUE[$field_name] = $value;
        return $value;
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

        if (static::$TABLE == "period") {
            if (($field_name == "validated_at") and (!$value)) throw new AfwRuntimeException("rafik dbg : $field_name inited as = [$value] into " . static::$TABLE);
        }

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

    private function getAllfieldDefaultValues()
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
    ) 
    {
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
    ) 
    {
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
    public function loadCol($object, 
        $col_name,
        $distinct = false,
        $limit = '',
        $order_by = ''
    ) 
    {
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

    public function getPKField($add_me = '')
    {
        if (!$this->PK_FIELD and !$this->PK_MULTIPLE) {
            return $add_me."id";
        } elseif ($this->PK_FIELD) {
            return $add_me . $this->PK_FIELD;
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

            return 'concat(' . implode(",'$sep',", $pk_arr) . ')';
        }
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

    public function est($attribute)
    {
        // work with shortcuts
        return $this->is($attribute, false);
    }

    /**
     * is
     * Return true if Y / false if N / W if W
     * @param string $attribute
     */
    public function is($attribute, $w = true, $struct = null)
    {
        // work with shortcuts and shortnames
        if(!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        if (!$struct) {
            $def_val = null;
            $open_options = false;
        } else {
            $def_val = $struct['DEFAULT'];
            $open_options = $struct['OPEN_OPTIONS']; // means field YN can contain other choices than Y,N,W so then all other options will be conisdered here like W
        }
        $stored_val = $this->getVal($attribute);
        //if($attribute=="enum") die("attribute=$attribute, [$stored_val] =  this->getVal($attribute)");
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
        throw new AfwRuntimeException('can not check attribute '.$attribute.' with value '.$value." in method is(), stored_val=$stored_val, def_val=$def_val.");
    }

    /**
     * load
     * Load into object a specified row
     * @param string $value : Optional, specify the value of primary key
     */
    public function load($value = '', $result_row = '', $order_by_sentence = '', $optim_lookup=true) 
    {        
        return AfwLoadHelper::loadAfwObject($this, $value, $result_row, $order_by_sentence, $optim_lookup);
    }

    public function getRelation($attribute, $struct = null)
    {
        $attribute_old = $attribute;
        $attribute = AfwStructureHelper::shortNameToAttributeName($this,$attribute_old);
        // die("attribute_old=$attribute_old, $attribute = $attribute_old");

        if(!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);

        return new AFWRelation(
            $struct['ANSMODULE'],
            $struct['ANSWER'],
            $struct['ITEM'],
            $this->getId(),
            $struct['WHERE'],
            $this
        );
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
        if((!$this->isEmpty()) and ($attribute!="id") and !isset($this->AFIELD_VALUE[$attribute])) return true;
        if(strpos($attribute, '.') !== false) return true;
        if(strpos($attribute, '_') === 0) return true;
        if($this->shouldBeCalculatedField($attribute)) return true;

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
        $optim_lookup = true
    ) 
    {
        return AfwLoadHelper::getAttributeData($this,
        $attribute,
        $what,
        $format,
        $integrity,
        $max_items,
        $optim_lookup);
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

    public final function isOk($force = false, $returnErrors = false, $langue = null, $ignore_fields_arr = null, $start_step=null, $end_step=null)
    {
        global $lang;
        if (!$langue) $langue = $lang;
        // $objme = AfwSession::getUserConnected();
        if (!$force and !AfwSession::hasOption('CHECK_ERRORS') and !$this->forceCheckErrors) 
        {
            if (!$returnErrors) return true; else return [true, []];
        }
        $stop_on_first_error = (!$returnErrors);
        $returnErrorsStep = "all";
        if($returnErrors!="all")
        {            
            $start_step = $returnErrors;
            $end_step = $returnErrors;
        }
        
        
        
        $dataErr = $this->getDataErrors($langue, true, $force, $returnErrorsStep, $ignore_fields_arr, null, $stop_on_first_error, $start_step, $end_step);
        // die("showErrorsAsSessionWarnings::getDataErrors($langue, true, $force, $returnErrorsStep, $ignore_fields_arr, null, $stop_on_first_error, $start_step, $end_step) => ".var_export($dataErr,true));
        $is_ok = count($dataErr) == 0;
        if (!$returnErrors) return $is_ok; else return [$is_ok, $dataErr];
    }

    public final function findInMfk($attribute,$id_to_find, $mfk_empty_so_found = false, $struct=null) 
    {
        if(!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        if ($struct['TYPE'] != 'MFK' and $struct['TYPE'] != 'MENUM') {
            throw new AfwRuntimeException(
                "Only MFK Fields can use this method, $attribute is not MFK"
            );
        }

        $old_val = $this->getVal($attribute);
        if (!$old_val) {
            $old_val = AfwStructureHelper::getDefaultValue($this, $attribute);
        }
        if (!$old_val) {
            return $mfk_empty_so_found;
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

        return $old_index[$id_to_find];
    }

    public function addRemoveInMfk($attribute,$ids_to_add_arr,$ids_to_remove_arr, $struct = null)
    {
        $old_val = $this->getVal($attribute);
        if (!$old_val) {
            $old_val = AfwStructureHelper::getDefaultValue($this, $attribute);
        }
        //if(!$old_val) return $old_val;

        if(!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);
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

    protected function dynamicHelpCondition($attribute)
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
        return AfwFormulaHelper::calculateFormulaResult($this,$attribute, $what);
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

    /**
     * getVal
     * Return the value of an attribute
     * @param string $attribute
     */
    public function getVal($attribute, $integrity = false)
    {
        return $this->get($attribute, 'value', '', $integrity);
    }

    /**
     * decode
     * Decode an attribute switch his type and display it through a specified format
     * @param string $attribute
     * @param string $format
     */
    public function decode($attribute, $format = '', $integrity = false)
    {
        // if($attribute == "session_status_id") die("decode($attribute, $format, $integrity)");
        if (strtolower($format) == 'value') {
            return $this->getVal($attribute);
        } else {
            return $this->get($attribute, 'decodeme', $format, $integrity);
        }
    }

    public function setOrder($order)
    {
        return false;
    }

    /**
     * set
     * Set attribute's value for next insert or update
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
        if ($attribute == 'id' and !$value and !$this->authorize_empty_of_id) {
            throw new AfwRuntimeException('trying to empty id ...');
        }

        $attribute = AfwStructureHelper::shortNameToAttributeName($this,$attribute);
        $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        if (!$structure) {
            throw new AfwRuntimeException("attribute $attribute doesn't exist in strcuture of this class : " . $this->getMyClass());
        }
        if ($structure['TYPE'] == 'DATE') {
            if ($value and $value != 'now()') {
                $value = AfwDateHelper::formatDateForDB($value);
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
            $return = $this->setNotSecure(
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
        return $this->setNotSecure($attribute, $value, false, true, true);
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
        if(!$struct) $struct = AfwStructureHelper::getStructureOf($this, $attribute);

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
    }

    public function setSlient($attribute, $value)
    {
        return $this->setNotSecure($attribute, $value, true, true);
    }

    // il faut utiliser setForce si on essaye de vider attribut dans un multi update (many records not only one) donc on va utiliser
    // un objet vide et on essaye de vider un attribut deja vide et donc par conclusion l'optimisateur va ignorer l'operation
    // si on n'utilise pas le mode force
    public function setForce($attribute, $value, $is_numeric_field = false)
    {
        return $this->setNotSecure(
            $attribute,
            $value,
            $check = true,
            $nothing_updated = false,
            $simul_do_not_save = false,
            $forceSet = true,
            $is_numeric_field
        );
    }

    /**
     * setNotSecure
     * Set attribute's value for next insert or update
     * @param string $attribute
     * @param string $value
     * @param boolean $check
     */
    public function setNotSecure(
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
        if (AfwStructureHelper::fieldExists($this,'draft')) {
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

    public function myShortNameToAttributeName($attribute)
    {
        return $attribute;
    }
    

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
    }

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
                    AfwStructureHelper::isObjectEasyAttribute($this,$attribute)
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
            $attribute = AfwStructureHelper::shortNameToAttributeName($this,$attribute);
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
            case 'cal':
                return $this->calc($attribute);
                break;
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
                return $this->est($attribute);
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
     * select
     * Set attribute's value in the Search criteria
     * @param string $attribute
     * @param string $value
     */
    public function select($attribute, $value)
    {
        if ((self::$TABLE == "acondition_origin") and ($attribute == "cvalid")) {
            throw new AfwRuntimeException(self::$TABLE."->select($attribute, $value) ya rafik !!!!");
        }
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible faire call to the method select() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {

            $attribute = AfwStructureHelper::shortNameToAttributeName($this,$attribute);
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
            if ($structure['UTF8']) {
                $_utf8 = '_utf8';
            } else {
                $_utf8 = '';
            }
            $this->SEARCH_TAB[$attribute] = AfwStringHelper::_real_escape_string($value);

            if ($structure['FIELD-FORMULA']) {
                $attribute_sql = $structure['FIELD-FORMULA'];
            } else {
                $attribute_sql = 'me.' . $attribute;
            }

            $this->SEARCH .=
                ' and ' .
                $attribute_sql .
                " = $_utf8'" .
                AfwStringHelper::_real_escape_string($value) .
                "'";
            // if($attribute=="cvalid") throw new AfwRuntimeException("this->SEARCH = ".$this->SEARCH." because structure=".var_export($structure,true));
            return true;
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
            $attribute = AfwStructureHelper::shortNameToAttributeName($this,$attribute);
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
        $obj = new self();
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
                if(!is_array($val0))
                {
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
                if(!is_array($val0))
                {
                    $this->select($attr0, $val0);
                }
                else
                {
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
            return 'now()';
        } else {
            if ($add_cote_if_needed) {
                $creation_date = "'$creation_date'";
            }
            return $creation_date;
        }
    }

    public function get_UPDATE_DATE_value($add_cote_if_needed = false)
    {
        $update_date = $this->UPDATE_DATE_val;
        if (!$update_date) {
            return 'now()';
        } else {
            if ($add_cote_if_needed) {
                $update_date = "'$update_date'";
            }
            return $update_date;
        }
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
        if(!$return) $return = 0;
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

    /** APPROVED *** */

    public function singleTranslation($lang = 'ar')
    {
        // can be overrridden
        return $this->transClassSingle($lang = 'ar');
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

        if ($return == $classSingle and $short) {
            $classSingle = $classSingleOrigin;
            $return = $this->translate($classSingle, $lang);
        }

        if ($return == $classSingle) {
            $return = AfwStringHelper::toEnglishText(trim($tableLower));
        }

        return $return;
    }

    public function transClassPlural($lang = 'ar', $short = false, $maksour = false)
    {
        $tableLowerOrigin = strtolower(static::$TABLE);

        if ($short) {
            $tableLower = $tableLowerOrigin . '.short';
        } else {
            $tableLower = $tableLowerOrigin;
        }

        if($maksour) {
            $tableLowerNotMaksour = $tableLower;
            $tableLower = $tableLower."_";
        }
        
        $return = $this->translate($tableLower, $lang);
        if ($return == $tableLower and $maksour) 
        {
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
     * Delete row
     */
    public function delete($id_replace = 0)
    {
        global $lang;
        $objme = AfwSession::getUserConnected();

        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'can not call delete() method with virtual table : ' .
                    static::$TABLE .
                    '.'
            );
        } elseif ($this->userCanDeleteMe($objme) <= 0) {
            throw new AfwRuntimeException(
                "the user [$objme] is not allowed to do delete operation on " .
                    $this->getShortDisplay($lang)
            );
        } else {
            $this->logicDelete();

            $return = false;
            if ($this->beforeDelete($this->id, $id_replace)) 
            {
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
                //die("query : $query");
                $this->afterDelete(
                    $this->getAfieldValue($this->getPKField()),
                    $id_replace
                );
            }

            /*
            rafik : @todo all this should be generated in beforeDelete
            $can_delete_or_hide = false;
            if($id_replace)
            {
                   $this->affected_rows = $this->replaceAllObjUsingMeBy($id_replace);
                   $can_delete_or_hide = true;
            }
            else
            {
                list($not_allow_objs, $not_allow_objs_nb) = $this-> getAllObjUsingMe("DEL-ACTION","not-allow");
                if($not_allow_objs_nb>0)
                {
                       $html_error = "";
                       foreach($not_allow_objs as $fk_on_me_table => $not_allow_objs_arr)
                       {
                         
                         foreach($not_allow_objs_arr as $fk_on_me_col => $not_allow_obj_list)
                         {
                            foreach($not_allow_obj_list as $fk_obj_id => $fk_obj)
                            {
                                $html_error .= "<br>".$fk_obj->transClassSingle() . " : " . $fk_obj->__toString(). " (" . $fk_obj->getId() . ") <br>";                        
                            }
                         }
                       }
                       throw new AfwRuntimeException("used_record_error",$html_error);
                       $can_delete_or_hide = false;                
                }
                else $can_delete_or_hide = true;
            }      
            
            if($can_delete_or_hide)
            {
                list($not_avail_objs, $not_avail_objs_nb) =  $this-> getAllObjUsingMe("DEL-ACTION","not-avail",1);
                if($not_avail_objs_nb>0)
                {
                    return $this->hide(); 
                }
                else
                {
                    $return = false;
        			if($this->beforeDelete($this->getAfieldValue($this->getPKField()))) 
                    {
        				$query = "DELETE FROM " . self::_prefix_table(static::$TABLE) . " 
                               WHERE " . $this->getPKField() . " = '" . $this->getAfieldValue($this->getPKField()) . "'";
                        $return = $this->execQuery($query);
                        //die("query : $query");
                        $this->afterDelete($this->getAfieldValue($this->getPKField()));
        			}
        			
        			return $return;
                }
			}
            else return false;
            */
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
            throw new AfwRuntimeException('Impossible to call deleteWhere() with virtual entity');
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

    public function inModeDev()
    {
        $var1 = 'development_mode';
        $var2 = 'development_mode_' . static::$TABLE;

        // mode dev general or for this table (for temporary debugg in prod)
        return AfwSession::config($var1, false) or
            AfwSession::config($var2, false);
    }

    /**
     * audit_before_update
     * Insert into _audit table before execute Update Query
     */
    public function audit_before_update($arr_fields_updated)
    {
        if ($this->IS_VIRTUAL) {
            throw new AfwRuntimeException(
                'Impossible to do call to the method audit_before_update() with the virtual table ' .
                    static::$TABLE .
                    '.'
            );
        } else {
            global $update_context;

            $table_name = self::_prefix_table(static::$TABLE);

            if (!$update_context) {
                $objme = AfwSession::getUserConnected();
                if (
                    $objme and
                    $objme->isAdmin() and
                    true /*$MODE_DEVELOPMENT*/
                ) {
                    throw new AfwRuntimeException(
                        "update context not specified when auditing table $table_name"
                    );
                }
            }

            $rowsCount = 0;

            foreach ($arr_fields_updated as $key => $new_value) {
                if ($this->keyIsAuditable($key)) {
                    $table_audit = $table_name . '_' . $key . '_haudit';
                    $id = $this->getId();
                    $version = $this->getVersion();
                    $old_value = $this->getVal($key);
                    $update_date = $this->getUpdateDate();
                    $update_auser_id = $this->getUpdateUserId();

                    $update_date_col = $this->fld_UPDATE_DATE();
                    $update_auser_id_col = $this->fld_UPDATE_USER_ID();

                    $rowsAffected = $this->execQuery("INSERT INTO $table_audit(id, version, val, update_date, update_auser_id, update_context)
        					     select $id, version, $key, $update_date_col, $update_auser_id_col, _utf8'$update_context' from $table_name where id = $id");

                    $rowsCount += $rowsAffected;
                } else {
                    // if($key=="subject") die("$table_name -> $key : not auditable");
                }
            }
        }

        return $rowsCount;
    }

    public function getTransDisplayField($lang = 'ar')
    {
        if ($lang == 'fr') {
            $lang = 'en';
        }

        if (!$this->DISPLAY_FIELD) {
            $all_real_fields = AfwStructureHelper::getAllRealFields($this);
            $this->DISPLAY_FIELD = $all_real_fields[1];
        }

        if (!$this->DISPLAY_FIELD) {
            $this->DISPLAY_FIELD = $this->getPKField();
        }

        if (
            se_termine_par($this->DISPLAY_FIELD, '_ar') or
            se_termine_par($this->DISPLAY_FIELD, '_fr') or
            se_termine_par($this->DISPLAY_FIELD, '_en')
        ) {
            $disp_fld_std = substr(
                $this->DISPLAY_FIELD,
                0,
                strlen($this->DISPLAY_FIELD) - 3
            );
        } else {
            $disp_fld_std = $this->DISPLAY_FIELD;
        }

        $display_field_trad = $disp_fld_std . '_' . $lang;

        if (AfwStructureHelper::fieldExists($this,$display_field_trad)) {
            return $display_field_trad;
        }

        return $this->DISPLAY_FIELD;
    }

    public function getDisplay($lang = 'ar')
    {
        return $this->getDefaultDisplay($lang);
    }

    final public function getDefaultDisplay($lang = 'ar')
    {
        //if ($this instanceof Applicant) die("df is ".var_export($this->DISPLAY_FIELD,true));
        if (!$this->id) 
        {
            $return = $this->insertNewLabel($lang);
            
            // if ($this instanceof StudentFileStatus) $return .= "<!-- ".var_export($this,true)." -->";
            /*
            $return = $this->transClassSingle($lang) .
                ' ' .
                $this->translate('NEW', $lang, true);*/
        } 
        elseif(is_array($this->DISPLAY_FIELD) and count($this->DISPLAY_FIELD)>0)
        {
            // if ($this instanceof Applicant) die("df is an array : ".var_export($this->DISPLAY_FIELD,true));
            $disp_decoded = [];
            foreach($this->DISPLAY_FIELD as $key)
            {
                $disp_decoded[] = $this->decode($key);
            }
            $return = implode(" ", $disp_decoded);
            //if ($this instanceof Applicant) die("for instanceof Applicant return = $return because $disp_decoded = ".var_export($disp_decoded,true));
        } 
        elseif ($this->DISPLAY_FIELD) 
        {
            $return = $this->getVal($this->DISPLAY_FIELD);
        }
        elseif(is_array($this->UNIQUE_KEY) and count($this->UNIQUE_KEY)>0)
        {
            $uk_decoded = [];
            foreach($this->UNIQUE_KEY as $key)
            {
                $uk_decoded[] = $this->decode($key);
            }
            $return = implode("-", $uk_decoded);
        } 
        else
        {
            $return =
                $this->transClassSingle($lang) . ' &larr; ' . $this->id;
        }

        if (!$return) {
            $return = $this->getMyClass()." id " . $this->id;
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
        global $lang;

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
    ) {

        //throw new AfwRuntimeException("token_arr = ".var_export($token_arr,true)." text_to_decode=$text_to_decode");
        if (is_array($this->otherTokens)) {
            foreach ($this->otherTokens as $tok => $tok_val) {
                $token_arr["[$tok]"] = $tok_val;
            }
        }
        $token_arr['[LANG]'] = $lang;
        $token_arr['[OBJECT_ID]'] = $this->getId();
        $token_arr['[OBJECT_DISPLAY]'] = $this->getDisplay($lang);
        $token_arr['[OBJECT_WIDE_DISPLAY]'] = $this->getWideDisplay($lang);
        $token_arr['[OBJECT_SHORT_DISPLAY]'] = $this->getShortDisplay($lang);
        $token_arr['[OBJECT_NODE_DISPLAY]'] = $this->getNodeDisplay($lang);
        $token_arr['[OBJECT_RETRIEVE_DISPLAY]'] = $this->getRetrieveDisplay(
            $lang
        );
        if (strpos($text_to_decode, '[ADMIN_START]') !== false) {
            $objme = AfwSession::getUserConnected();
            if ($objme and $objme->isAdmin()) {
                $token_arr['[ADMIN_START]'] = '';
                $token_arr['[ADMIN_END]'] = '';
            } else {
                $token_arr['[ADMIN_START]'] = '<!-- ';
                // if($objme) $token_arr["[ADMIN_START]"] .= "because ".$objme->getDisplay($lang)." id = " .$objme->getId()." is not admin";
                $token_arr['[ADMIN_END]'] = ' -->';
            }
        }

        if (strpos($text_to_decode, '[OBJECT_ERRORS]') !== false) {
            list($is_ok, $dataErr) = $this->isOk($force = true, true);
            if ($is_ok) {
                $token_arr['[ERROR_STATUS]'] = 'ok';
                $token_arr['[OBJECT_ERRORS]'] = '';
                $token_arr['[OBJECT_ERRORS_START]'] = '<!-- ';
                $token_arr['[OBJECT_ERRORS_END]'] = ' -->';
            } else {
                $errors_html = implode("<br>\n", $dataErr);
                $errors_html = trim($errors_html, "<br>\n");
                $token_arr['[ERROR_STATUS]'] = 'err';
                $token_arr['[OBJECT_ERRORS]'] = $errors_html;
                $token_arr['[OBJECT_ERRORS_START]'] = '';
                $token_arr['[OBJECT_ERRORS_END]'] = '';
            }
        } elseif (strpos($text_to_decode, '[ERROR_STATUS]') !== false) {
            if ($this->isOk($force = true)) {
                $token_arr['[ERROR_STATUS]'] = 'ok';
            } else {
                $token_arr['[ERROR_STATUS]'] = 'err';
            }
        }

        $this_db_structure = static::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );

        foreach ($this_db_structure as $fieldname => $struct_item) {
            $token_fcl = '[fcl:' . $fieldname . ']';

            $token_is = '[is:' . $fieldname . ']';
            $token_is_en = '[is-en:' . $fieldname . ']';
            $token_value = '[value:' . $fieldname . ']';
            $token_data = '[' . $fieldname . ']';
            $token_label = '[' . $fieldname . '_label]';
            $token_showme = '[' . $fieldname . '_showme]';

            if ($struct_item['TYPE'] == 'DATE') {
                $token_full_date = '[' . $fieldname . '.full]';
                $token_medium_date = '[' . $fieldname . '.medium]';
            }

            if ($struct_item['CATEGORY'] == 'ITEMS') {
                $token_data_no_icons = '[' . $fieldname . '.no_icons]';
            }

            if ($struct_item['TO_TRANSLATE']) {
                $token_to_translate = '[' . $fieldname . '.translate]';
            }

            if (strpos($text_to_decode, $token_fcl) !== false) {
                $token_fcl_val = AfwStringHelper::firstCharLower(
                    $this->getVal($fieldname)
                );
                $token_arr[$token_fcl] = $token_fcl_val;
            }

            if (strpos($text_to_decode, $token_is) !== false) {
                $this_token_is_arr = $this->token_is_arr;
                $this_token_not_is_arr = $this->token_not_is_arr;
                $this_token_null_is_arr = $this->token_null_is_arr;

                if (!$this_token_is_arr[$fieldname]) {
                    $this_token_is_arr[$fieldname] = 'YES';
                }
                if (!$this_token_not_is_arr[$fieldname]) {
                    $this_token_not_is_arr[$fieldname] = 'NO';
                }
                if (!$this_token_null_is_arr[$fieldname]) {
                    $this_token_null_is_arr[$fieldname] = 'NOT YET';
                }

                $field_val = $this->getVal($fieldname);
                if ($field_val == 'Y') {
                    $token_is_val = $this->translateOperator(
                        $this_token_is_arr[$fieldname],
                        $lang
                    );
                } elseif ($field_val == 'N') {
                    $token_is_val = $this->translateOperator(
                        $this_token_not_is_arr[$fieldname],
                        $lang
                    );
                } else {
                    $token_is_val = $this->translateOperator(
                        $this_token_null_is_arr[$fieldname],
                        $lang
                    );
                }

                $token_arr[$token_is] = $token_is_val;
            }

            if (strpos($text_to_decode, $token_is_en) !== false) {
                $this_token_is_en_arr = $this->token_is_en_arr;
                $this_token_not_is_en_arr = $this->token_not_is_en_arr;
                $this_token_null_is_en_arr = $this->token_null_is_en_arr;

                if (!$this_token_is_en_arr[$fieldname]) {
                    $this_token_is_en_arr[$fieldname] = 'required'; // YES
                }
                if (!$this_token_not_is_en_arr[$fieldname]) {
                    $this_token_not_is_en_arr[$fieldname] = ''; // NO
                }
                if (!$this_token_null_is_en_arr[$fieldname]) {
                    $this_token_null_is_en_arr[$fieldname] = ''; // NOT-YET
                }

                $field_val = $this->getVal($fieldname);
                if ($field_val == 'Y') {
                    $token_is_en_val = $this_token_is_en_arr[$fieldname];
                } elseif ($field_val == 'N') {
                    $token_is_en_val = $this_token_not_is_en_arr[$fieldname];
                } else {
                    $token_is_en_val = $this_token_null_is_en_arr[$fieldname];
                }

                $token_arr[$token_is_en] = $token_is_en_val;
            }

            if (strpos($text_to_decode, $token_data) !== false) {
                // if($fieldname=="prices_buttons") AfwRunHelper::safeDie("this->tokens = ".var_export($this->tokens,true));
                $struct_item['IN_TEMPLATE'] = true;
                $token_arr[$token_data] = $this->showAttribute(
                    $fieldname,
                    $struct_item
                );
                // if($fieldname=="prices_buttons") AfwRunHelper::safeDie("token value of token $token_data = ".var_export($token_arr[$token_data],true));
            }

            if (strpos($text_to_decode, $token_value) !== false) {
                $token_arr[$token_value] = $this->getVal($fieldname);
            }

            if (
                $struct_item['CATEGORY'] == 'ITEMS' and
                strpos($text_to_decode, $token_data_no_icons) !== false
            ) {
                $struct_item['ICONS'] = false;
                $token_arr[$token_data_no_icons] = $this->showAttribute(
                    $fieldname,
                    $struct_item
                );
                //die("token_arr[$token_data_no_icons] = this->showAttribute($fieldname, struct_item) with struct_item = ".var_export($struct_item,true)." = ".var_export($token_arr[$token_data_no_icons],true));
            }

            if (strpos($text_to_decode, $token_showme) !== false) {
                $token_arr[$token_showme] = '';
                $objToShowIt = $this->het($fieldname);
                if ($objToShowIt) {
                    $token_arr[$token_showme] = $objToShowIt->showMe('', $lang);
                }
            }

            if (strpos($text_to_decode, $token_label) !== false) {
                $trad_col = $trad_erase[$fieldname];
                if (!$trad_col) {
                    $trad_col = $this->getAttributeLabel($fieldname, $lang);
                }

                $token_arr[$token_label] = $trad_col;
            }

            if (
                $struct_item['TYPE'] == 'DATE' and
                strpos($text_to_decode, $token_full_date) !== false
            ) {
                $token_arr[$token_full_date] = $this->fullHijriDate($fieldname);
            }

            if (
                $struct_item['TYPE'] == 'DATE' and
                strpos($text_to_decode, $token_medium_date) !== false
            ) {
                $token_arr[$token_medium_date] = $this->mediumHijriDate(
                    $fieldname
                );
            }

            if (
                $struct_item['TO_TRANSLATE'] and
                strpos($text_to_decode, $token_to_translate) !== false
            ) {
                $token_arr[$token_to_translate] = $this->translateValue(
                    $fieldname
                );
            }
        }

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
        return $this->showMinibox('', $lang);
    }

    public function showMinibox(
        $structure = '',
        $lang = 'ar',
        $token_arr = null,
        $objme = null,
        $public_show = false
    ) {
        //

        $obj_table = $this->getTableName();
        if ($structure) {
            $file_tpl = $structure['MINIBOX-TEMPLATE'];
        } else {
            $file_tpl = 'AUTO';
        }
        // die($this->getDisplay($lang)." minibox tpl : ".$file_tpl);
        if ($file_tpl != 'AUTO') {
            if (!$token_arr) {
                $token_arr = $this->prepareTokensArrayFromRequestParams(
                    'minibox'
                );
            }
            if ((!$file_tpl) or (strtoupper($file_tpl) == "DEFAULT")) {
                $file_tpl = "tpl/tpl_mb_$obj_table.php";
            }

            $this->tokens = $token_arr;
            //die("this->tokens = ".var_export($this->tokens,true));

            if ($structure['MINIBOX-TEMPLATE-PHP']) {
                $objKey = $structure['MINIBOX-OBJECT-KEY'];
                $data = [];
                $data[$objKey] = $this;
                $data_to_display = $this->showUsingPhpTemplate($file_tpl, $data);
            } else {
                $data_to_display = $this->showUsingTpl(
                    $file_tpl,
                    [],
                    $lang,
                    $token_arr
                );
            }
        } else {
            $items_objs = [];

            $first_item = $this;
            $items_objs[$first_item->getId()] = $first_item;

            if (!$this->mb_context) {
                $this->mb_context = 'mb_auto';
            }
            //die($this->getDisplay($lang)." manyMiniBoxes show for : ".var_export($items_objs,true));
            if (!$objme) $objme = AfwSession::getUserConnected();
            list(
                $data_to_display,
                $items_objs,
                $ids,
                $report
            ) = AfwShowHelper::manyMiniBoxes(
                $items_objs,
                $first_item,
                $objme,
                null,
                array(),
                $public_show
            );

            //die($this->getDisplay($lang)." AfwShowHelper::manyMiniBoxes showed [for $ids, public_show=$public_show] : ".$data_to_display." report=$report");
        }

        if ($data_to_display == '') {
            $data_to_display = AfwFormatHelper::getItemsEmptyMessage($this, $structure, $lang);
        }

        return $data_to_display;
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
    public function translate($nom_col, $langue = 'ar', $operator = null)
    {
        $nom_table = static::$TABLE;
        $module = static::$MODULE;

        $return = AfwLanguageHelper::tarjem(
            $nom_col,
            $langue,
            $operator,
            $nom_table,
            $module
        );
        /*
        if ($nom_col == 'address_type_enum') {
            throw new AfwRuntimeException("$return = AfwLanguageHelper::tarjem($nom_col, $langue,$operator,$nom_table, $module)");
        }
        */
        return $return;
    }

    public function translateText($text, $langue = 'ar')
    {
        return $this->translate($text, $langue, false);
    }

    public function translateOperator($text, $langue = 'ar')
    {
        return $this->translate($text, $langue, true);
    }



    public function getTranslatedAttributeProperty(
        $attribute,
        $attribute_property,
        $lang,
        $desc = null
    ) {
        if (!$desc) $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        $attribute_property_code = $desc[$attribute_property];

        if (!$attribute_property_code) {
            $attribute_property_code = $attribute . '_' . $attribute_property;
        }

        $attribute_property_code = strtoupper($attribute_property_code);
        $attribute_property_trans = $this->translateMessage(
            $attribute_property_code,
            $lang
        );
        if ($attribute_property_trans == $attribute_property_code) {
            $attribute_property_trans = '';
        }
        $attribute_property_code = strtolower($attribute_property_code);
        if (!$attribute_property_trans) {
            $attribute_property_trans = $this->translate(
                $attribute_property_code,
                $lang
            );
        }

        //if(($attribute=="picture_height") and ($attribute_property=="UNIT")) die(" $attribute_property_trans = this->translate($attribute_property_code,$lang) ");

        if ($attribute_property_trans == $attribute_property_code) {
            $attribute_property_trans = '';
        }

        if (!$attribute_property_trans) {
            $attribute_property_trans = $desc[$attribute_property];
        }

        return $attribute_property_trans;
    }

    public function tm($message, $langue = '')
    {
        global $lang;
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        return $this->translateMessage($message, $langue);
    }

    public function tf($message, $langue = '')
    {
        global $lang;
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        $message_tm = $this->translate($message, $langue);
        return $message_tm;
    }

    public function translateMessage($message, $lang = 'ar')
    {
        $file_dir_name = dirname(__FILE__);
        $module = static::$MODULE;

        include "$file_dir_name/../../$module/messages_$lang.php";
        /*
        if($message == "Please choose more refined criteria")
        {
            die("tm($message) from $file_dir_name/../pag/messages_$lang.php and $file_dir_name/../$module/messages_$lang.php : ".var_export($messages,true));
        }
        */
        if ($messages[$message]) {
            return $messages[$message];
        }

        include "$file_dir_name/../../lib/messages_$lang.php";
        /*
        if($message == "Please choose more refined criteria")
        {
            die("tm($message) from $file_dir_name/../pag/messages_$lang.php and $file_dir_name/../$module/messages_$lang.php : ".var_export($messages,true));
        }*/
        if ($messages[$message]) {
            return $messages[$message];
        }

        include "$file_dir_name/../../pag/messages_$lang.php";
        /*
        if($message == "Please choose more refined criteria")
        {
            die("tm($message) from $file_dir_name/../pag/messages_$lang.php and $file_dir_name/../$module/messages_$lang.php : ".var_export($messages,true));
        }*/
        if ($messages[$message]) {
            return $messages[$message];
        }

        return $message;
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

    public function getColsByMode($mode)
    {
        $mode = strtoupper($mode);
        if ($mode == 'SHOW') {
            return $this->getToShowCols();
        }
        if ($mode == 'MINIBOX') {
            return $this->getMiniBoxCols();
        }
        if ($mode == 'RETRIEVE') {
            return $this->getRetrieveCols();
        }

        throw new AfwRuntimeException(
            "mode $mode unknown when calling afw::getColsByMode($mode)"
        );
    }

    public function isToShowCol($attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        return !isset($desc['SHOW']) and $desc['EDIT'] or $desc['SHOW'];
    }

    public function getToShowCols()
    {
        $tableau = [];

        $all_FIELDS = $this->getAllAttributes();

        foreach ($all_FIELDS as $attribute) {
            if ($this->isToShowCol($attribute)) {
                $tableau[] = $attribute;
            }
        }
        return $tableau;
    }

    public function isMiniBoxCol($attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        return (!isset($desc['MINIBOX']) and $desc['RETRIEVE'] and !$desc['MINIBOX-PREVENT']) or $desc['MINIBOX'];
    }

    public function getMiniBoxCols()
    {
        $tableau = [];

        $FIELDS_ALL = $this->getAllAttributes();

        foreach ($FIELDS_ALL as $attribute) {
            if ($this->isMiniBoxCol($attribute)) {
                $tableau[] = $attribute;
            }
        }
        if (count($tableau) == 0) die("no MiniBoxCols => FIELDS_ALL = " . var_export($FIELDS_ALL, true));
        if (self::$TABLE == "school") die(self::$TABLE . " => FIELDS_ALL = " . var_export($FIELDS_ALL, true));
        return $tableau;
    }

    public function isSearchCol($attribute, $desc = '')
    {
        global $lang;



        $SEARCH_LANG = 'SEARCH-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        $is_searchable =
            ($desc['TYPE'] == 'PK' or
                $desc['SEARCH'] or
                $desc['SEARCH-BY-ONE'] or
                $desc[$SEARCH_LANG] or
                $attribute == $this->fld_ACTIVE() and ($objme = AfwSession::getUserConnected()) and $objme->isAdmin());

        //@todo : rafik implemeter cas d'un shortcut et le parent du shortcut est un PART-JOIN
        //    "SHORTCUT-PART-JOIN" est un attribue temporaire n'as aucun sens sauf activer le QSearch pour un shortcut
        //    pour mes print screen pour ecriture des specifications
        $can_be_searched_technically =
            ($desc['CATEGORY'] == '' or
                $desc['FIELD-FORMULA'] or
                $desc['SHORTCUT'] and $desc['SHORTCUT-PART-JOIN']);

        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = $this->keyIsToDisplayForUser(
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            if (!$objme) $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = $this->keyIsToDisplayForUser(
                $attribute,
                $objme
            );
        }



        $return =
            ($attributeIsToDisplayForMe and
                $can_be_searched_technically and
                $is_searchable);
        //die("$attribute : return=$return = $attributeIsToDisplayForMe and $can_be_searched_technically and $is_searchable ".var_export($desc,true));
        return $return;
    }

    public function isQSearchCol($attribute, $desc = '')
    {
        // $objme = AfwSession::getUserConnected();

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }
        $is_searchable = $this->isSearchCol($attribute, $desc);
        $can_qsearch =
            ($desc['QSEARCH'] or
                !isset($desc['QSEARCH']) and $desc['SEARCH-BY-ONE']);
        $is_qsearchable =
            ($can_qsearch and
                (($desc['TYPE'] == 'PK' or
                    $desc['TYPE'] == 'FK' or
                    $desc['TYPE'] == 'ENUM' or
                    $desc['TYPE'] == 'YN' or
                    $desc['TYPE'] == 'DATE' // or $desc['TYPE'] == 'TEXT' => strange it make all TEXT fields SEARCHABLE-SEPARATED
                    )
                    or
                    $desc['TEXT-SEARCHABLE-SEPARATED']));
        // if($attribute=="academic_program_id") die("attribute $attribute is_searchable=$is_searchable, can_qsearch=$can_qsearch, is_qsearchable=$is_qsearchable, desc=".var_export($desc,true));
        $return = ($is_searchable and $is_qsearchable);

        return $return;
    }

    public function isInternalSearchableCol($attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }
        return $desc['TYPE'] == 'FK' and $desc['INTERNAL_QSEARCH'];
    }

    public function isTextSearchableCol($attribute, $desc = '')
    {
        //$objme = AfwSession::getUserConnected();

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        // INTERNAL-QSEARCH means we search inside Text Searchable Cols inside the FK object
        return ($desc['TYPE'] == 'TEXT' or
            $this->isInternalSearchableCol($attribute, $desc)) and
            !$desc['TEXT-SEARCHABLE-SEPARATED'] and
            $this->isSearchCol($attribute, $desc);
    }

    public function getTextSearchableCols()
    {
        return AfwFrameworkHelper::getAllAttributesInMode($this,'text-searchable');
    }

    public function getAllTextSearchableCols()
    {
        return AfwFrameworkHelper::getAllAttributesInMode($this,
            'text-searchable',
            $step = 'all',
            $typeArr = ['ALL' => true],
            $submode = '',
            $for_this_instance = true,
            $translate = false,
            $translate_to_lang = 'ar',
            $implode_char = '',
            $elekh_nb_cols = 9999,
            $alsoAdminFields = false,
            $alsoTechFields = false,
            $alsoNAFields = true,
            $max_elekh_nb_chars = 9999,
            $alsoVirtualFields = true
        );
    }

    public function translateCols($cols, $lang = 'ar', $short = false)
    {
        $tableau = [];

        foreach ($cols as $attribute) {
            if ($short) {
                $tableau[$attribute] = $this->translate(
                    $attribute . '.short',
                    $lang
                );
            }

            if (
                !$tableau[$attribute] or
                $tableau[$attribute] == $attribute . '.short'
            ) {
                $tableau[$attribute] = $this->translate($attribute, $lang);
            }
        }
        return $tableau;
    }

    // return Y : yes,
    //    N: no,
    //    W: undefined
    public function isRetrieveColForMode(
        $attribute,
        $mode,
        $lang = 'ar',
        $all = false,
        $desc = null
    ) {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        if ($desc['ALL-RETRIEVE']) {
            return true;
        }
        $mode_up = strtoupper($mode);

        // @doc : to make the id or any other attribute is shown for qsearch with view TECH_FIELDS, just put "TECH_FIELDS-RETRIEVE" => true in structure of attribute
        //
        //if($mode_up=="TECH_FIELDS") echo("mode TECH_FIELDS : $attribute desc = ".var_export($desc,true)."<br><br><br>");
        if (
            $all and
            $desc['SHOW'] and
            !$desc['NO-RETRIEVE'] and
            strtoupper($desc['FGROUP']) == $mode_up
        ) {
            return true;
        }

        $retrieve_att = "$mode_up-RETRIEVE";
        if ($retrieve_att == 'DISPLAY-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        if ($retrieve_att == 'SEARCH-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        if ($retrieve_att == '-RETRIEVE') {
            $retrieve_att = 'RETRIEVE';
        }
        $retrieve_lang = "$retrieve_att-" . strtoupper($lang);

        if (!isset($desc[$retrieve_att]) and !isset($desc[$retrieve_lang])) {
            return 'W';
        }
        if (!$desc[$retrieve_att] and !$desc[$retrieve_lang]) {
            return 'N';
        }
        return 'Y';
    }

    protected function setSpecialRetrieveCols()
    {
        // to be overriden in sub classes and define :
        $force_retrieve_cols = [];
        $hide_retrieve_cols = [];

        return [
            'force_retrieve_cols' => $force_retrieve_cols,
            'hide_retrieve_cols' => $hide_retrieve_cols,
        ];
    }

    public function isRetrieveCol(
        $attribute,
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $desc = null
    ) {

        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = $this->keyIsToDisplayForUser(
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = $this->keyIsToDisplayForUser(
                $attribute,
                $objme
            );
        }

        if (!$attributeIsToDisplayForMe) {
            return false;
        }

        $RETRIEVE_LANG = 'RETRIEVE-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        $is_force_retrieve =
            ($this->force_retrieve_cols and
                is_array($this->force_retrieve_cols) and
                in_array($attribute, $this->force_retrieve_cols));

        $is_general_retrieve =
            (isset($desc['RETRIEVE']) and $desc['RETRIEVE'] or
                isset($desc[$RETRIEVE_LANG]) and $desc[$RETRIEVE_LANG]);

        $retForMode = $this->isRetrieveColForMode(
            $attribute,
            $mode,
            $lang,
            $all,
            $desc
        );
        // if($is_general_retrieve) die("attribute=$attribute, retForMode=$retForMode, mode=$mode, is_force_retrieve=$is_force_retrieve");
        // if($attribute == "ongoing_requests_count") die("attribute=$attribute, retForMode=$retForMode, mode=$mode, is_force_retrieve=$is_force_retrieve, is_general_retrieve=$is_general_retrieve, this->force_retrieve_cols=".var_export($this->force_retrieve_cols,true));

        // rafik : @todo need more explanation
        $generalRetrieveModeAllowed =
            ($retForMode == 'W' and
                (($mode == 'display' or !$mode) and $is_general_retrieve));
        $return =
            ($retForMode == 'Y' or
                $generalRetrieveModeAllowed or
                $is_force_retrieve);

        /*
         if((static::$TABLE=="practice_vote") and ($attribute=="id"))
         {
        $message = "return = $return";
        $message .= "<br>desc[RETRIEVE] = ".$desc["RETRIEVE"];
        $message .= "<br>desc[$RETRIEVE_LANG] = $desc[$RETRIEVE_LANG]";
        $message .= "<br>retForMode = $retForMode";
        $message .= "<br>is_general_retrieve = $is_general_retrieve";
        throw new AfwRuntimeException("isRetrieveCol : debugg : $message");
         }
         */

        return $return;
    }

    public function getReasonAttributeNotRetrievableOrRetrievable(
        $attribute,
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $desc = null
    ) {
        $attributeIsToDisplayForMe = $attributeIsToDisplayForAll = $this->keyIsToDisplayForUser(
            $attribute,
            null
        );

        if (!$attributeIsToDisplayForMe) {
            $objme = AfwSession::getUserConnected();
            $attributeIsToDisplayForMe = $this->keyIsToDisplayForUser(
                $attribute,
                $objme
            );
        }
        if (!$attributeIsToDisplayForMe) {
            return "when I can't see attribute $attribute how can I retrieve it";
        }

        $RETRIEVE_LANG = 'RETRIEVE-' . strtoupper($lang);

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        $is_force_retrieve =
            ($this->force_retrieve_cols and
                is_array($this->force_retrieve_cols) and
                in_array($attribute, $this->force_retrieve_cols));

        $is_general_retrieve =
            (isset($desc['RETRIEVE']) and $desc['RETRIEVE'] or
                isset($desc[$RETRIEVE_LANG]) and $desc[$RETRIEVE_LANG]);

        $retForMode = $this->isRetrieveColForMode(
            $attribute,
            $mode,
            $lang,
            $all,
            $desc
        );

        ($generalRetrieveModeAllowed = $retForMode == 'W') and
            (($mode == 'display' or !$mode) and $is_general_retrieve);

        $reason =
            "retForMode($attribute, $mode)=[$retForMode] generalRetrieveModeAllowed = ($generalRetrieveModeAllowed) is_force_retrieve=($is_force_retrieve) this->force_retrieve_cols = " .
            var_export($this->force_retrieve_cols, true);

        return [$reason, $desc];
    }

    /** audit columns ***/

    public function getAuditableCols()
    {
        $tableau = [];

        $FIELDS_ALL = $this->getAllAttributes();

        foreach ($FIELDS_ALL as $attribute) {
            if ($this->keyIsAuditable($attribute)) {
                $attribute_to_remove_from_audit = false;
                if (!$attribute_to_remove_from_audit) {
                    $tableau[] = $attribute;
                }
            }
        }
        return $tableau;
    }
    /**
     * retrieve
     * for display mode get retrieve columns
     * @param array $array
     */

    public function getRetrieveCols(
        $mode = 'display',
        $lang = 'ar',
        $all = false,
        $type = 'all',
        $debugg = false,
        $hide_retrieve_cols = null,
        $force_retrieve_cols = null,
        $category = 'all'
    ) {
        if (!$hide_retrieve_cols and !$force_retrieve_cols) {
            list(
                $hide_retrieve_cols,
                $force_retrieve_cols,
            ) = $this->setSpecialRetrieveCols();
        }

        // die("setSpecialRetrieveCols returned hide_retrieve_cols = ".var_export($hide_retrieve_cols,true).", force_retrieve_cols = ".var_export($force_retrieve_cols,true));
        $tableau = [];

        $db_struct_all = $this->getAllMyDbStructure();

        foreach ($db_struct_all as $attribute => $descAttr) {
            if (
                $this->isRetrieveCol($attribute, $mode, $lang, $all, $descAttr)
            ) {
                if (
                    !$hide_retrieve_cols or
                    !is_array($hide_retrieve_cols) or
                    !count($hide_retrieve_cols) or
                    !in_array($attribute, $hide_retrieve_cols)
                ) {
                    // debugg why $attribute is shown when it should be hidden
                    // if($attribute=="man" and $hide_retrieve_cols) throw new AfwRuntimeException("$attribute is not in hide_retrieve_cols = ".var_export($hide_retrieve_cols,true));
                    $take = false;
                    if ($type == 'all') {
                        $take = true;
                    } else {
                        // if(!$descAttr) $descAttr = AfwStructureHelper::getStructureOf($this,$attribute);
                        if ($descAttr['TYPE'] == $type) {
                            $take = true;
                        }
                    }

                    $takeCateg = false;
                    if ($category == 'all') {
                        $takeCateg = true;
                    } else {
                        // if(!$descAttr) $descAttr = AfwStructureHelper::getStructureOf($this,$attribute);
                        if ($descAttr['CATEGORY'] == $category) {
                            $takeCateg = true;
                        } elseif ((!$descAttr['CATEGORY']) and ($category == "empty")) {
                            $takeCateg = true;
                        }
                    }

                    if ($take and $takeCateg) {
                        $tableau[] = $attribute;
                    }
                }
                if ($debugg) {
                    // list($AttributeRetrievableWhy, $descAttr2)  = $this->getReasonAttributeNotRetrievableOrRetrievable($attribute);
                    // if($attribute == "currentRequests") die("$attribute is RetrieveCol in mode $mode reason=$AttributeRetrievableWhy descAttr2=".var_export($descAttr2,true));
                }
            } elseif ($debugg) {
                // list($AttributeNotRetrievableWhy, $descAttr2)  = $this->getReasonAttributeNotRetrievableOrRetrievable($attribute);
                // if($attribute == "ongoing_requests_count") die("$attribute is not RetrieveCol in mode $mode reason=$AttributeNotRetrievableWhy descAttr2=".var_export($descAttr2,true));
            }
        }
        /*
        if(static::$TABLE=="practice")
        {
            $message = "tableau = ".var_export($tableau,true);
            throw new AfwRuntimeException("getRetrieveCols : debugg : $message");
        }
        */
        return $tableau;
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
    protected static function afterDeleteWhere($where)
    {
    }

    public function deleteOneByOneWhere($where, $simul = false)
    {
        global $lang;

        $this->where($where);

        $ObjectsToDeleteList = $this->loadMany();
        $ObjectsToDeleteListCount = count($ObjectsToDeleteList);

        $delete_blocked_reasons = [];

        foreach ($ObjectsToDeleteList as $ObjectsToDeleteItem) {
            list($can, $reason) = $ObjectsToDeleteItem->canBeDeleted();
            if (!$can) {
                $delete_blocked_reasons[] =
                    $ObjectsToDeleteItem->getShortDisplay($lang) .
                    ' : ' .
                    $reason;
            }
        }

        if (count($delete_blocked_reasons) > 0) {
            return [
                false,
                $delete_blocked_reasons,
                $ObjectsToDeleteListCount,
                $ObjectsToDeleteList,
            ];
        }

        if (!$simul) {
            // بسم الله توكلت على الله
            foreach ($ObjectsToDeleteList as $obj_id => $ObjectsToDeleteItem) {
                $success = $ObjectsToDeleteItem->delete();
                if (!$success) {
                    $delete_blocked_reasons[] =
                        $ObjectsToDeleteItem->getShortDisplay($lang) .
                        ' : فشلت عملية المسح';
                    return [
                        false,
                        $delete_blocked_reasons,
                        $ObjectsToDeleteListCount,
                        $ObjectsToDeleteList,
                    ];
                }

                unset($ObjectsToDeleteList[$obj_id]);
            }
        }
        // $ObjectsToDeleteList below should be empty but I put it in case of ....
        // لن يحصل هذا إلا أن يشاء الله
        // إلا في حالة المحادات simul=true
        return [
            true,
            $delete_blocked_reasons,
            $ObjectsToDeleteListCount,
            $ObjectsToDeleteList,
        ];
    }

    final public function canBeDeleted()
    {
        // 0,0 below to simulate delete not really delete (beforeDelete should be regenerated for old classes (before 13/3/2020) to generate simul param inside beforeDelete
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
    public function afterDelete($id, $id_replace)
    {
    }

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
    public function afterHide($id)
    {
    }

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

    /**
     * getLink
     * get into array objects from LINK_TABLE
     * @param string $tbl : table name
     */
    public function getLink($index)
    {
        if ($index) {
            if (
                is_array($this->DB_LINK[$index]) &&
                $this->DB_LINK[$index]['TARGET_TABLE'] &&
                $this->DB_LINK[$index]['LINK_TABLE'] &&
                $this->DB_LINK[$index]['MY_KEY'] &&
                $this->DB_LINK[$index]['HIS_KEY']
            ) {
                if ($this->getId()) {
                    $module_server = $this->getModuleServer();
                    $result_rows = AfwDatabase::db_recup_rows(
                        'select ' .
                            $this->DB_LINK[$index]['HIS_KEY'] .
                            ' as PK from ' .
                            $this->DB_LINK[$index]['LINK_TABLE'] .
                            ' where ' .
                            $this->DB_LINK[$index]['MY_KEY'] .
                            ' = ' .
                            $this->getId(),
                        true,
                        true,
                        $module_server
                    );
                    if (count($result_rows) > 0) {
                        $array = [];
                        $className = AfwStringHelper::tableToClass(
                            $this->DB_LINK[$index]['TARGET_TABLE']
                        );
                        $fileName = AfwStringHelper::tableToFile(
                            $this->DB_LINK[$index]['TARGET_TABLE']
                        );
                        if ($this->MY_DEBUG and false) {
                            AFWDebugg::log("require_once $fileName");
                        }
                        require_once $fileName;
                        foreach ($result_rows as $result_row) {
                            $object = new $className();
                            // $object->setMyDebugg($this->MY_DEBUG);
                            $object->load($result_row['PK']);
                            $array[$result_row['PK']] = $object;
                        }
                        return $array;
                    } else {
                        return [];
                    }
                } else {
                    throw new AfwRuntimeException(
                        "L'id of current object est vide dans the method getLink()."
                    );
                }
            } else {
                if (AfwSession::config('LOG_SQL', true)) {
                    AFWDebugg::log($this->DB_LINK, true);
                }
                throw new AfwRuntimeException(
                    "attribute DB_LINK not defined pour la clé " .
                        $index .
                        ' dans the method getLink().'
                );
            }
        } else {
            throw new AfwRuntimeException(
                'the method getLink() nécessite le paramètre $index qui semble vide.'
            );
        }
    }

    /**
     * setLink
     * insert values into LINK_TABLE
     * @param string $tbl : table name
     * @param string $id : HIS_KEY
     */
    public function setLink($index, $id)
    {
        if ($index && $id) {
            if (
                is_array($this->DB_LINK[$index]) &&
                $this->DB_LINK[$index]['LINK_TABLE'] &&
                $this->DB_LINK[$index]['MY_KEY'] &&
                $this->DB_LINK[$index]['HIS_KEY']
            ) {
                if ($this->getId()) {
                    $query =
                        'insert into ' .
                        $this->DB_LINK[$index]['LINK_TABLE'] .
                        ' set ' .
                        $this->DB_LINK[$index]['MY_KEY'] .
                        " = '" .
                        $this->getId() .
                        "', " .
                        $this->DB_LINK[$index]['HIS_KEY'] .
                        " = '" .
                        $id .
                        "'";
                    return $this->execQuery($query);
                } else {
                    throw new AfwRuntimeException(
                        "L'id of current object est vide dans the method setLink()."
                    );
                }
            } else {
                if (AfwSession::config('LOG_SQL', true)) {
                    AFWDebugg::log($this->DB_LINK, true);
                }
                throw new AfwRuntimeException(
                    "attribute DB_LINK not defined pour la clé " .
                        $index .
                        ' dans the method setLink().'
                );
            }
        } else {
            throw new AfwRuntimeException(
                'the method getLink() nécessite les deux paramètres $index ' .
                    $index .
                    ' et $id ' .
                    $id .
                    'qui semblent vides.'
            );
        }
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

    /*obso
	public function getClass FilePackage($table="")
	{
		if(!$table) $table = static::$TABLE;
		return AFWI NI::getClass FilePackage($table);
	} 
    

	public function getMyFilePrefix()
	{
		$array=$this->getClass FilePackage();
		$str_prefix = "";
		if($array['pk'])  $str_prefix .=  $array['pk']."_";
		if($array['spk'])  $str_prefix .=  $array['spk']."_";

		return $str_prefix;

	}
	*/
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
            $selects[$this->fld_ACTIVE()] = 'Y';
        }
        /*
        if(static::$TABLE == "school_employee")
        { 
            echo "selects : <br>";
            echo var_export($selects,true);
            die();
        }*/
        foreach ($selects as $colselect => $valselect) {
            if (AfwStructureHelper::fieldExists($this,$colselect)) {
                //if($colselect == "employee_id") die("$this this->select($colselect,$valselect);");
                $this->select($colselect, $valselect);
            } else {
                throw new AfwRuntimeException("trying to sql-select the field '$colselect' but does not exist, selects =" . var_export($selects, true));
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

    public function getMyTable($prefix=false)
    {
        if($prefix) return self::_prefix_table(static::$TABLE);
        else return static::$TABLE;
    }

    public function getMyClass()
    {
        return AfwStringHelper::tableToClass(static::$TABLE);
    }

    public function getMyModule()
    {
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

    final public function userCanDeleteMe($auser, $log = true)
    {
        global $lang;
        $return = 1;
        // User roles check
        if (!$this->userCanDeleteMeStandard($auser)) {
            $return = -1;
        }
        // Business rules check
        if ($return > 0 and !$this->userCanDeleteMeSpecial($auser)) {
            $return = -2;
        }
        if ($log) {
            if ($return <= 0) {
                AfwSession::contextLog(
                    sprintf(
                        $this->tm("user %d can't delete this object %s"),
                        $auser->id,
                        $this->getShortDisplay($lang)
                    ),
                    'iCanDo'
                );
            } else {
                AfwSession::contextLog(
                    sprintf(
                        $this->tm(
                            '* success * : user %d can delete this object %s => return = %d'
                        ),
                        $auser->id,
                        $this->getShortDisplay($lang),
                        $return
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

    final public function userCanEditMe($auser)
    {
        global $lang;
        if ($auser and $auser->isAdmin()) {
            return [true, ''];
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
        global $lang;
        // $objme = AfwSession::getUserConnected();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }
        $ynCodeForThis = "$key.$ynCode";
        $ynTranslationForThis = $this->translate($ynCodeForThis, $langue);
        //return $ynTranslationForThis;
        //if($key=="auto_approved") throw new AfwRuntimeException("showYNValueForAttribute($ynCode, $key, $langue) : $ynTranslationForThis = this->translate($ynCodeForThis,$langue)");
        if ($ynTranslationForThis and $ynTranslationForThis != $ynCodeForThis) {
            return $ynTranslationForThis;
        }

        return $this->translateOperator($ynCode, $langue); // ." translation [$key][$lang][".$this->decode($key)."]"
    }







    public function showMyLink($step = 0, $target = '', $what = "icon", $whatis = "view_ok", $mode = "display")
    {
        global $lang;
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
        global $lang;
        //$objme = AfwSession::getUserConnected();
        if (!$langue) {
            $langue = $lang;
        }
        if (!$langue) {
            $langue = 'ar';
        }

        $target = '';
        $popup_t = '';
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

        if ($intelligent_category == 'ITEMS') {
            $value = '';
            $formatted = false;
        } else {
            // in case of we use shortname
            $value = $this->getVal($key);
            //if($key == "session_status_id") die("$value = this->getVal($key)");
            list($formatted, $data_to_display, $link_to_display,) = AfwFormatHelper::formatValue($value, $key, $structure, $getFormatLink, $this);
            // if($key == "session_status_id") die("list($formatted, $data_to_display, $link_to_display,) = AfwFormatHelper::formatValue($value, $key, ..)");
        }


        if ($formatted) {
            //if($key=="price5") throw new AfwRuntimeException("how we get here ???? data_to_display = $data_to_display = AfwFormatHelper::formatValue($value,$key, $structure, $getFormatLink)");
            // done
        } elseif ($structure['TYPE'] == 'FK') {
            if (empty($structure['CATEGORY'])) {
                if ($value) {
                    if (!$structure['DISPLAY']) {
                        $structure['DISPLAY'] = $structure['FORMAT'];
                    }
                    if (
                        strtoupper($structure['DISPLAY']) == 'SHOW' and
                        $value > 0
                    ) {
                        $valObj = $this->get($key);
                        if ($valObj) {
                            $data_to_display = $valObj->showMe(
                                $structure['STYLE']
                            );
                        } else {
                            $data_to_display = '';
                        }
                        $link_to_display = '';
                    } elseif (strtoupper($structure['DISPLAY']) == 'MINIBOX') {
                        $valObj = $this->get($key);
                        if ($valObj) {
                            $data_to_display = $valObj->showMinibox(
                                $structure['STYLE']
                            );
                        } else {
                            $data_to_display = '';
                        }
                        $link_to_display = '';
                    } elseif (strtoupper($structure['DISPLAY']) == 'SHORT') {
                        $valObj = $this->get($key);
                        if ($valObj) {
                            $data_to_display = $valObj->getShortDisplay($lang);
                        } else {
                            $data_to_display = '';
                        }
                        $link_to_display = '';
                    } else {
                        $data_to_display = $this->decode($key);
                        // if(($key == "cher_id") and (!contient(trim(strtolower($data_to_display)),"<img"))) die($this->getDisplay("ar")."rafik::data_to_display=$data_to_display");
                        // if(($key == "cher_id") and (!trim($data_to_display))) die($this->getDisplay("ar")."->decode($key) empty ->getVal($key) = ".$this->getVal($key));
                        if ($getlink) {
                            $link_to_display = $this->getLinkForAttribute(
                                $structure['ANSWER'],
                                $value,
                                'display',
                                $structure['ANSMODULE'],
                                false,
                                $getlink
                            );
                        }
                    }
                } else {
                    $data_to_display = '';
                    if ($structure['EMPTY_IS_ALL']) {
                        $all_code = "ALL-$attribute";
                        $data_to_display = $this->translate($all_code, $lang);
                        if ($data_to_display == $all_code) {
                            $data_to_display = $this->translateOperator(
                                'ALL',
                                $lang
                            );
                        }
                    }
                }
            } else {


                switch ($intelligent_category) {
                    case 'VIRTUAL':
                        $data_to_display = $this->getVirtual($key, 'value', '');
                        if ($getlink) {
                            $link_to_display = $this->getLinkForAttribute(
                                $structure['ANSWER'],
                                $value,
                                'display',
                                $structure['ANSMODULE']
                            );
                        }
                        break;

                    case 'ITEMS':
                        if ($structure['SHOW_DATA'] != 'EXAMPLE') {
                            $items_objs = $this->get($key, 'object', '', false);
                            // if($key=="attendanceList") throw new AfwRuntimeException("$this - > get($key) = ".var_export($items_objs,true));
                        } else {
                            $max_items_to_show = $structure['SHOW_MAX_DATA'];
                            if (!$max_items_to_show) {
                                $max_items_to_show = 600;
                            }
                            $items_objs = $this->get(
                                $key,
                                'object',
                                '',
                                false,
                                $max_items_to_show
                            );
                        }
                        if (strtoupper($structure['FORMAT']) == 'TREE') {
                            reset($items_objs);
                            $first_item = current($items_objs);
                            $data_to_display = '';
                            if ($first_item) {
                                //$objme = AfwSession::getUserConnected();
                                $first_item->deleteIcon = $this->enabledIcon(
                                    $attribute,
                                    'DELETE',
                                    $structure
                                );
                                $first_item->editIcon = $this->enabledIcon(
                                    $attribute,
                                    'EDIT',
                                    $structure
                                );
                                $first_item->viewIcon = $this->enabledIcon(
                                    $attribute,
                                    'VIEW',
                                    $structure
                                );
                                $first_item->attachIcon = $this->enabledIcon(
                                    $attribute,
                                    'ATTACH',
                                    $structure
                                );
                                $first_item->showId = $structure['SHOW-ID'];

                                list(
                                    $html_tree,
                                    $js_tree,
                                    $countNodes,
                                ) = AfwShowHelper::showTree(
                                    $key . 'tree',
                                    $items_objs,
                                    $structure['LINK_COL'],
                                    $structure['ITEMS_COL'],
                                    $structure['FEUILLE_COL'],
                                    $structure['FEUILLE_COND_METHOD'],
                                    $objme = AfwSession::getUserConnected(),
                                    $langue,
                                    $structure['ALL_ITEMS'],
                                    !$structure['IFRAME_BELOW']
                                );

                                //die("showTree($key tree = $countNodes, $html_tree");

                                if (!$countNodes) {
                                    if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                        $empty_code =
                                            $structure['EMPTY-ITEMS-MESSAGE'];
                                    } else {
                                        $empty_code = 'atr-empty';
                                    }

                                    $data_to_display =
                                        "<div class='empty_message'>" .
                                        $this->translate($empty_code, $langue) .
                                        '</div>';
                                } else {
                                    $data_to_display =
                                        $html_tree .
                                        "\n<script>\n$js_tree\n</script>\n\n\n";
                                }
                            }
                        } elseif (strtoupper($structure['FORMAT']) == 'CROSSED') {
                            reset($items_objs);
                            $first_item = current($items_objs);
                            $data_to_display = '';
                            if ($first_item) {
                                // $ret_cols_arr = $first_item->getRetrieveCols($mode);

                                $cross_col = $structure['CROSS_COL'];
                                $crossed_field_col =
                                    $structure['CROSSED_FIELD_COL'];
                                $crossed_value_col =
                                    $structure['CROSSED_VALUE_COL'];
                                $data = [];
                                $index_cross = [];

                                $indexc = 1;
                                $header_trad = [];
                                $header_trad[$cross_col] = $first_item->translate($cross_col, $langue);
                                foreach ($items_objs as $objI) {
                                    $cross_val = $objI->showAttribute($cross_col); //$objI->getVal($cross_col);
                                    if (!$index_cross[$cross_val]) {
                                        $index_cross[$cross_val] = $indexc;
                                        $indexc++;
                                    }
                                    $data[$index_cross[$cross_val] - 1][$cross_col] = $cross_val;

                                    if (
                                        !$objI->attributeIsApplicable(
                                            $crossed_value_col
                                        )
                                    ) {
                                        list(
                                            $icon,
                                            $textReason,
                                            $wd,
                                            $hg,
                                        ) = $objI->whyAttributeIsNotApplicable(
                                            $crossed_value_col
                                        );
                                        if (!$wd) {
                                            $wd = 20;
                                        }
                                        if (!$hg) {
                                            $hg = 20;
                                        }
                                        $data[$index_cross[$cross_val] - 1][$objI->calc($crossed_field_col)] = "<img src='../lib/images/$icon' data-toggle='tooltip' data-placement='top' title='$textReason'  width='$wd' heigth='$hg'>";
                                    } else {
                                        $data[$index_cross[$cross_val] - 1][$objI->calc($crossed_field_col)] = $objI->showAttribute(
                                            $crossed_value_col
                                        );
                                    }

                                    $header_trad[$objI->calc($crossed_field_col)]
                                        = $objI->translate($objI->decode($crossed_field_col), $langue);
                                }

                                list($html, $ids) = AfwShowHelper::tableToHtml($data, $header_trad);

                                $data_to_display = $html; //." data=".var_export($data,true)." header=".var_export($header_trad,true)
                            }
                        } 
                        elseif (strtoupper($structure['FORMAT']) == 'RETRIEVE') 
                        {
                            reset($items_objs);
                            $first_item = current($items_objs);
                            $data_to_display = '';
                            if ($first_item) {
                                //$objme = AfwSession::getUserConnected();
                                $first_item->deleteIcon = $this->enabledIcon(
                                    $attribute,
                                    'DELETE',
                                    $structure
                                );
                                $first_item->editIcon = $this->enabledIcon(
                                    $attribute,
                                    'EDIT',
                                    $structure
                                );
                                $first_item->viewIcon = $this->enabledIcon(
                                    $attribute,
                                    'VIEW',
                                    $structure
                                );
                                $first_item->attachIcon = $this->enabledIcon(
                                    $attribute,
                                    'ATTACH',
                                    $structure
                                );
                                $first_item->showId = $structure['SHOW-ID'];
                                //if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("first_item = ".var_export($first_item,true));

                                // if($attribute=="allEmployeeList") die("structure = ".var_export($structure,true));
                                $hide_retrieve_cols =
                                    $structure['DO-NOT-RETRIEVE-COLS'];
                                if (!$hide_retrieve_cols) {
                                    $hide_retrieve_cols = [];
                                }
                                if ($structure['ITEM']) {
                                    $hide_retrieve_cols[] = $structure['ITEM'];
                                }

                                // if($attribute=="currentRequests") die("structure = ".var_export($structure,true)." first_item->hide_retrieve_cols = ".var_export($first_item->hide_retrieve_cols,true)." structure[DO-NOT-RETRIEVE-COLS]=".var_export($structure["DO-NOT-RETRIEVE-COLS"],true));

                                $force_retrieve_cols =
                                    $structure['FORCE-RETRIEVE-COLS'];
                                $nowrap_cols = $structure['NOWRAP-COLS'];

                                $group_retieve_arr = $structure['RETRIEVE-GROUPS']
                                    ? $structure['RETRIEVE-GROUPS']
                                    : $structure['RETRIEVE_GROUPS'];

                                if (!$group_retieve_arr) {
                                    $group_retieve_arr = [];
                                    $group_retieve_arr[] = 'display';
                                    $no_tabs = true;
                                } else {
                                    $no_tabs = false;
                                }

                                $html_display = [];

                                foreach ($group_retieve_arr as $group_retieve) {
                                    $first_item->mode_retieve = $group_retieve;
                                    $first_item->showAsDataTable =
                                        count($items_objs) > 20 ? ($structure['DATA_TABLE'] ? $structure['DATA_TABLE'] : "dtb_$key") : '';
                                    if ($first_item->showAsDataTable) 
                                    {
                                        $first_item->showAsDataTable .= '_' . $group_retieve;
                                    }
                                    $options = [];
                                    $options['hide_retrieve_cols'] = $hide_retrieve_cols;
                                    $options['force_retrieve_cols'] = $force_retrieve_cols;
                                    $options['nowrap_cols'] = $nowrap_cols;
                                    list(
                                        $html_display[$group_retieve],
                                        $items_objs,
                                        $ids,
                                    ) = AfwShowHelper::showManyObj(
                                        $items_objs,
                                        $first_item,
                                        $objme = AfwSession::getUserConnected(),
                                        $options
                                    );
                                    if ($html_display[$group_retieve] == '') {
                                        if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                            $empty_code =
                                                $structure['EMPTY-ITEMS-MESSAGE'];
                                        } else {
                                            $empty_code = 'atr-empty';
                                        }

                                        $html_display[$group_retieve] =
                                            "<div class='empty_message'>" .
                                            $this->translate(
                                                $empty_code,
                                                $langue
                                            ) .
                                            '</div>';
                                    }
                                }

                                //if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("html_display = ".var_export($html_display,true));

                                if ($no_tabs) {
                                    $data_to_display = $html_display['display'];
                                } else {
                                    $data_to_display =
                                        "<ul class='nav nav-tabs'>\n";
                                    $div_tabs = "<div class='tab-content'>\n";

                                    $itab = 0;
                                    foreach ($html_display
                                        as $group_retieve =>
                                        $html_group_retrieve) {
                                        if ($first_item) {
                                            $group_retieve_label = $first_item->translate(
                                                $group_retieve,
                                                $langue
                                            );
                                        } else {
                                            $group_retieve_label = $this->translate(
                                                $group_retieve,
                                                $langue
                                            );
                                        }
                                        if ($itab == 0) {
                                            $tab_active =
                                                " class='hzm-tab active'";
                                        } else {
                                            $tab_active = " class='hzm-tab'";
                                        }
                                        if ($itab == 0) {
                                            $div_tab_active = ' in active';
                                        } else {
                                            $div_tab_active = '';
                                        }

                                        $data_to_display .= "   <li $tab_active><a data-toggle='tab' href='#tab${attribute}$itab' class='hzm-tab-link'>$group_retieve_label</a></li>\n";
                                        $div_tabs .= "<div id='tab${attribute}$itab' class='tab-pane fade $div_tab_active'>\n";
                                        $div_tabs .=
                                            $html_group_retrieve . "\n";
                                        $div_tabs .= "</div>\n";
                                        $itab++;
                                    }
                                    $data_to_display .= "</ul>\n";
                                    $div_tabs .= "</div>\n";
                                    $data_to_display .= $div_tabs;
                                }
                                // if(isset($structure["ICONS"]) and (!$structure["ICONS"])) die("data_to_display : <br> ".var_export($data_to_display,true));
                            }
                        } elseif (
                            strtoupper($structure['FORMAT']) == 'MINIBOX'
                        ) {
                            reset($items_objs);
                            $first_item = current($items_objs);
                            $data_to_display = '';
                            if ($first_item) {

                                $first_item->deleteIcon = $this->enabledIcon(
                                    $attribute,
                                    'DELETE',
                                    $structure
                                );
                                // if($first_item->deleteIcon) die("attribute $attribute has in minibox mode the icon delete, see structure : ".var_export($structure,true));
                                $first_item->editIcon = $this->enabledIcon(
                                    $attribute,
                                    'EDIT',
                                    $structure
                                );
                                // if($first_item->editIcon) die("attribute $attribute has in minibox mode the icon edit, see structure : ".var_export($structure,true));
                                $first_item->viewIcon = $this->enabledIcon(
                                    $attribute,
                                    'VIEW',
                                    $structure
                                );
                                // if($first_item->viewIcon) die("attribute $attribute has in minibox mode the icon view, see structure : ".var_export($structure,true));
                                $first_item->attachIcon = $this->enabledIcon(
                                    $attribute,
                                    'ATTACH',
                                    $structure
                                );

                                $first_item->showId = $structure['SHOW-ID'];

                                $first_item->id_origin = $id_origin;
                                $first_item->class_origin = $class_origin;
                                $first_item->module_origin = $module_origin;
                                $first_item->del_level =
                                    $structure['ITEMS_DEL_LEVEL'];

                                list(
                                    $data_to_display,
                                    $items_objs,
                                    $ids,
                                ) = AfwShowHelper::manyMiniBoxes(
                                    $items_objs,
                                    $first_item,
                                    $objme = AfwSession::getUserConnected(),
                                    $structure
                                );
                            }

                            if ($data_to_display == '') {
                                if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                    $empty_code =
                                        $structure['EMPTY-ITEMS-MESSAGE'];
                                } else {
                                    $empty_code = 'atr-empty';
                                }

                                $data_to_display =
                                    "<div class='empty_message'>" .
                                    $this->translate($empty_code, $langue) .
                                    '</div>';
                            }
                        } elseif (
                            strtoupper($structure['FORMAT']) == 'CUSTOM'
                        ) {

                            $methodCustom = $structure['CUSTOM_FORMAT'];
                            $data_to_display = '';
                            reset($items_objs);
                            $first_item = current($items_objs);
                            if ($first_item) {
                                $first_item->deleteIcon = $this->enabledIcon(
                                    $attribute,
                                    'DELETE'
                                );
                                $first_item->editIcon = $this->enabledIcon(
                                    $attribute,
                                    'EDIT'
                                );
                                $first_item->viewIcon = $this->enabledIcon(
                                    $attribute,
                                    'VIEW'
                                );
                                $first_item->showId = $structure['SHOW-ID'];
                                $first_item->id_origin = $id_origin;
                                $first_item->class_origin = $class_origin;
                                $first_item->module_origin = $module_origin;
                                $first_item->del_level =
                                    $structure['ITEMS_DEL_LEVEL'];
                            }
                            list($data_to_display, $ids) = $this->$methodCustom(
                                $items_objs,
                                $first_item,
                                $objme = AfwSession::getUserConnected(),
                                $structure
                            );

                            if ($data_to_display == '') {
                                if ($structure['EMPTY-ITEMS-MESSAGE']) {
                                    $empty_code =
                                        $structure['EMPTY-ITEMS-MESSAGE'];
                                } else {
                                    $empty_code = 'atr-empty';
                                }

                                $data_to_display =
                                    "<div class='empty_message'>" .
                                    $this->translate($empty_code, $langue) .
                                    '</div>';
                            }
                        } else {
                            $data_to_display = '';
                            foreach ($items_objs as $objs_item) {
                                if ($getlink) {
                                    $data_to_display .=
                                        "<a href=\"" .
                                        $this->getLinkForAttribute(
                                            $structure['ANSWER'],
                                            $objs_item->getId(),
                                            'display',
                                            $structure['ANSMODULE']
                                        ) .
                                        "\" >";
                                }
                                $data_to_display .= (string) $objs_item;
                                if ($getlink) {
                                    $data_to_display .= '</a><br/>';
                                }
                            }
                        }
                        break;
                    case 'SHORTCUT':
                        $data_to_display = $this->decode($key);
                        break;
                    case 'FORMULA':
                        if (!$structure['DISPLAY']) {
                            $structure['DISPLAY'] = $structure['FORMAT'];
                        }
                        if (strtoupper($structure['DISPLAY']) == 'MINIBOX') {
                            $data_to_display = $this->get($key)->showMinibox(
                                $structure['STYLE']
                            );
                            $link_to_display = '';
                        } else {
                            $data_to_display = $this->decode($key);
                            // if($key == "session_status_id") die("$data_to_display = this->decode($key)");
                            if ($getlink) {
                                if (
                                    !$structure['ANSWER'] or
                                    !$structure['ANSMODULE']
                                ) {
                                    throw new AfwRuntimeException(
                                        " cannot get link for attribute $key , ANSWER table and ANSMODULE should be specified"
                                    );
                                }
                                $link_to_display = $this->getLinkForAttribute(
                                    $structure['ANSWER'],
                                    $value,
                                    'display',
                                    $structure['ANSMODULE']
                                );
                            }
                        }
                        break;
                    default:
                        $data_to_display = $this->decode($key);
                        /*foreach ($temp_obj as $val)
							$str .= $val."<br/>";
							$data_to_display = $str;*/
                        break;
                }
            }
        } elseif ($structure['TYPE'] == 'MFK') {
            if (true) {
                $temp_obj = $this->get($key, 'object', '', false);
                if (!is_array($temp_obj)) throw new AfwRuntimeException("get($key, object) returned non array type");
                // if($key=="attendanceList") throw new AfwRuntimeException("$this - > get($key) = ".var_export($temp_obj,true));

                if (strtoupper($structure['FORMAT']) == 'RETRIEVE') {
                    reset($temp_obj);
                    $first_item = current($temp_obj);
                    if ($first_item) {
                        if (!isset($structure['ICONS']) or $structure['ICONS']) {
                            // DELETE-ICON is not allowed for MFK as the items are not owned by this object (not like ITEMS)
                            if ($structure['EDIT-ICON']) {
                                $first_item->editIcon = $structure['EDIT-ICON'];
                            }
                            if (
                                !isset($structure['VIEW-ICON']) or
                                $structure['VIEW-ICON']
                            ) {
                                $first_item->viewIcon = true;
                            }
                            if ($structure['SHOW-ID']) {
                                $first_item->showId = true;
                            }
                        }

                        $objme = AfwSession::getUserConnected();

                        $options = [];
                        $options['hide_retrieve_cols'] =
                            $structure['DO-NOT-RETRIEVE-COLS'];
                        $options['force_retrieve_cols'] =
                            $structure['FORCE-RETRIEVE-COLS'];
                        $options['nowrap_cols'] = $structure['NOWRAP-COLS'];

                        list($data_to_display) = AfwShowHelper::showManyObj(
                            $temp_obj,
                            $first_item,
                            $objme,
                            $options
                        ); //todo ici il faut utiliser un mode a developper qui n'affiche pas les boutons edit/delete
                        $link_to_display = '';
                        //die("rafik : [$data_to_display] ".var_export($temp_obj,true));
                    }
                } else {
                    unset($data_to_display);
                    unset($link_to_display);
                    $data_to_display = [];
                    $link_to_display = [];
                    foreach ($temp_obj as $id => $val) {
                        // if(!is_object($val)) throw new AfwRuntimeException("strang non object in mfk array ".var_export($temp_obj,true));
                        if (is_object($val)) {
                            $data_to_display[$id] = $val->getDisplay($langue);
                            if ($getlink) {
                                $link_to_display[$id] = $this->getLinkForAttribute(
                                    $structure['ANSWER'],
                                    $val->getId(),
                                    'display',
                                    $structure['ANSMODULE']
                                );
                            }
                        }
                    }

                    //if($attribute=="arole_mfk") die("data_to_display ($attribute) = ".var_export($data_to_display));
                }
            } else {
                $data_to_display = '';
            }
        } elseif ($structure['TYPE'] == 'YN') {
            $ynCode = strtoupper($this->decode($key));
            $data_to_display = $this->showYNValueForAttribute(
                $ynCode,
                $key,
                $langue
            );
        } elseif ($structure['TYPE'] == 'PK') {
            if (!$structure['OFFSET']) {
                $data_to_display = $this->getId();
            } else {
                $data_to_display = $this->getId() + $structure['OFFSET'];
            }
        } elseif ($structure['TYPE'] == 'DEL') {
            $objme = AfwSession::getUserConnected();
            $val_id = $this->getId();
            if ($this->userCanDeleteMe($objme) > 0) {

                $currmod = $this->getMyModule();
                $lbl = $this->getDisplay($langue);
                $lvl = $structure['DEL_LEVEL'];
                if (!$lvl) {
                    $lvl = 2;
                }
                if ($attribute == 'atr') {
                    die('structure = ' . var_export($structure, true));
                }
                //$data_to_display = "<a target='popup' href='main.php?Main_Page=afw_mode_delete.php&popup=1&id_origin=$id_origin&class_origin=$class_origin&module_origin=$module_origin;&cl=$val_class&currmod=$currmod&id=$val_id' ><img src='../lib/images/delete.png' width='24' heigth='24'></a>";
                $data_to_display = "<a href='#' here='afw_shwr' id='$val_id' cl='$val_class' md='$currmod' lbl='$lbl' lvl='$lvl' class='trash afw-authorised'><img id='del_from_mfk_${val_id}_$key' src='../lib/images/trash.png' width='24' heigth='24'></a>";
            } else {
                $data_to_display = "<img id='del_not_authorised_${val_id}_$key' src='../lib/images/lockme.png' width='24' heigth='24'></a>";
            }
        } elseif ($structure['TYPE'] == 'SHOW') {
            $val_id = $this->getId();
            $currmod = $this->getMyModule();
            if ($structure['LABEL']) {
                $my_label = $structure['LABEL'];
            }
            if ($structure['ICON']) {
                $my_icon = $structure['ICON'];
            }
            if (!$my_icon) {
                $my_icon = 'view_ok';
            }
            if (!$my_label) {
                $my_label = "<img src='../lib/images/$my_icon.png' width='24' heigth='24'>";
            }
            if ($structure['TARGET']) {
                $target = "target='" . $structure['TARGET'] . "'";
            }

            $data_to_display = "<a $target href='main.php?Main_Page=afw_mode_display.php&popup=$popup_t&cl=$val_class&currmod=$currmod&id=$val_id' >$my_label</a>";
        } elseif ($structure['TYPE'] == 'EDIT') {
            $val_id = $this->getId();
            $currmod = $this->getMyModule();
            if ($structure['LABEL']) {
                $my_label = $structure['LABEL'];
            }
            if ($structure['ICON']) {
                $my_icon = $structure['ICON'];
            }
            if (!$my_icon) {
                $my_icon = 'modifier';
            }
            if (!$my_label) {
                $my_label = "<img src='../lib/images/$my_icon.png' width='24' heigth='24'>";
            }
            if ($structure['TARGET']) {
                $target = "target='" . $structure['TARGET'] . "'";
            }

            $data_to_display = "<a $target href='main.php?Main_Page=afw_mode_edit.php&popup=$popup_t&cl=$val_class&currmod=$currmod&id=$val_id' >$my_label</a>";
        } elseif ($structure['TYPE'] == 'ENUM') {
            $val = $value;
            $display_val = $this->decode($key);
            if ($display_val and $structure['FORMAT-INPUT'] == 'hzmtoggle') {
                //if(!$display_val) $display_val = "...";
                // die("key=$key, val=$val, display_val=$display_val, HZM-CSS=".$structure["HZM-CSS"]);
                $css_arr = AfwStringHelper::afw_explode($structure['HZM-CSS']);
                $css_val = $css_arr[$val];
                $data_to_display = "<div class='$css_val'>$display_val</div>";
            } else {
                $data_to_display = $display_val;
            } // ." ==> ".$structure["FORMAT-INPUT"]
        } else {
            $data_to_display = $this->decode($key);
            //if($key=="days_nb") die("data_to_display of ($key val:$value) is $data_to_display");
        }

        //if($attribute=="warning_nb") die("Rafik CSSED($cssed_to_class) : data_to_display of ($key) is $data_to_display");

        if (!$merge) {
            //if($key == "session_status_id") throw new AfwRuntimeException("we will return [$data_to_display, $link_to_display]");
            return [$data_to_display, $link_to_display];
        } else {
            //if($key == "session_status_id") die("merge for ($key) ??!!");
            if (!is_array($data_to_display)) {
                $data_to_display_arr = [];
                $data_to_display_arr[] = $data_to_display;
                $link_to_display_arr = [];
                $link_to_display_arr[] = $link_to_display;
            } else {
                $data_to_display_arr = $data_to_display;
                $link_to_display_arr = $link_to_display;
            }

            $disp_attr = '';

            $mfk_show_sep = $structure['LIST_SEPARATOR'];
            if (!$mfk_show_sep) {
                $mfk_show_sep = $structure['MFK-SHOW-SEPARATOR'];
            }
            if (!$mfk_show_sep) {
                $mfk_show_sep = "<br>\n";
            }

            foreach ($data_to_display_arr as $ii => $data_to_display_item) {
                if ($disp_attr) {
                    $disp_attr .= $mfk_show_sep;
                }
                $disp_attr .= $link_to_display_arr[$ii]
                    ? '<a class=\'afw cl_' . $val_class . '\'' .
                    $target .
                    ' href="' .
                    $link_to_display_arr[$ii] .
                    '">'
                    : ''; // '.$key.'
                $disp_attr .= $data_to_display_arr[$ii];
                $disp_attr .= $link_to_display_arr[$ii] ? '</a>' : '';
            }

            if ($disp_attr and $structure['TITLE_AFTER']) {
                $disp_attr .= ' ' . $structure['TITLE_AFTER'];
            }

            return $disp_attr;
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

        $server_db_prefix = AfwSession::config('db_prefix', 'c0');

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
                if (strpos($text_to_decode, $token) !== false) 
                {
                    $field_val = $this->calc($fieldname);
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
            foreach ($arr_spec_fields
                as $spec_field_name => $spec_field_value) {
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

    protected function getLinkForAttribute(
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
        //if($mode=="edit") die("getOtherLinksForUser($mode, ..) => other_links_arr = ".var_export($other_links_arr,true));
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

                if(!$other_link['MODULE']) $other_link['MODULE'] = static::$MODULE;

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

                        if (
                            $ican_do_bf or
                            $belongs_to_ugroup or
                            $user_is_owner or
                            $public
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
                                list($other_link_authorized,$reason,) = AfwStructureHelper::attributeIsWriteableBy($this, $attribute_related, $auser);
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
            }

            if ($other_link_authorized) {
                if (!$other_link['AUTH_TYPE']) {
                    $other_link['AUTH_TYPE'] = 'unknown-authorisation-type';
                }
                $other_link['URL'] = AfwUrlManager::encodeMainUrl($other_link['URL']);
                $other_link['URL'] = $this->decodeText(
                    $other_link['URL'],
                    '',
                    false
                );
                $final_other_links_arr[] = $other_link;
            }
        }

        return $final_other_links_arr;
    }

    protected function getOtherLinksArray($mode, $genereLog = false, $step = "all")
    {
        return $this->getOtherLinksArrayStandard($mode, $genereLog, $step);
    }

    

    protected final function getOtherLinksArrayStandard($mode, $genereLog = false, $step = "all") 
    {
        global $lang;
        $objme = AfwSession::getUserConnected();
        // $me = $objme ? $objme->id : 0;
        // $genereLog = true;

        $otherLinksArray = [];
        $my_id = $this->getId();
        $this_otherLinkLog = [];
        if ($mode == 'display' or $mode == 'edit') 
        {
            $FIELDS_ALL = $this->getAllMyDbStructure();
            $log = "mode=$mode, FIELDS_ALL=" . var_export($FIELDS_ALL, true);
            if ($genereLog) {
                $this_otherLinkLog[] = $log;
            }

            foreach ($FIELDS_ALL as $attribute => $struct) {
                $link_label = null;
                // rafik optimization : 23/12/2023
                // Momken V3.0 the getAllMyDbStructure method now return the structure repared 
                // no need below to re-call getStructureOf 
                // $struct = AfwStructureHelper::getStructureOf($this, $attribute);
                
                // $isAdminField = $this->isAdminField($attribute);
                // $isTechField = $this->isTechField($attribute);

                //if($attribute=="mainwork_start_paragraph_num") die("strange case, step = $step struct = ".var_export($struct,true));
                //if($attribute=="previous_paragraph_id") die("strange case, step = $step struct = ".var_export($struct,true)." struct['STEPS'][$step] = ".$struct['STEPS'][$step]);
                if (
                    $struct['TYPE'] == 'FK' and
                    ($struct['RELATION'] == 'OneToMany' or $struct['RELATION'] == 'OneToOneB' or $struct['RELATION'] == 'OneToOneU' or $struct['RELATION-SUPER'] == 'IMPORTANT') and
                    ($step == "all" or $struct['STEP']=="all" or $struct['STEP'] == $step or $struct['STEPS'][$step])
                ) {
                    // if($attribute=="previous_paragraph_id") die("case of OneToXX or RELATION-SUPER is IMPORTANT, struct of $attribute = ".var_export($struct,true));
                    $log ="$attribute attribute is FK RELATION is OneToXX or RELATION-SUPER is IMPORTANT: " .$struct['RELATION'];
                    if ($struct['RELATION'] == 'OneToMany') {
                        $parent_struct = AfwStructureHelper::getParentStruct($this, $attribute, $struct);
                        $parent_step = $parent_struct['STEP'];
                        if ($parent_step) {
                            $log =
                                "$attribute attribute has parent step : " .
                                $parent_step;
                            if ($genereLog) {
                                $this_otherLinkLog[] = $log;
                            }
                            list($displ2, $link_url2) = $this->displayAttribute(
                                $attribute,
                                false,
                                $lang,
                                "&currstep=$parent_step"
                            );
                            $displ2 = trim($displ2);
                            if (!$displ2) {
                                $displ2 = "case 1 : this->displayAttribute($attribute,false, $lang, &currstep=$parent_step)";
                            } else {
                                $displ2 .= "<!-- case 1: this->displayAttribute($attribute,false, $lang, &currstep=$parent_step) -->";
                            }

                            if (!$struct['NO-RETURNTO']) {
                                $struct['OTM-RETURNTO'] = true;
                            }
                        } else {
                            $log = "$attribute attribute has no parent step ";
                            if ($genereLog) {
                                $this_otherLinkLog[] = $log;
                            }
                            list($displ2, $link_url2) = $this->displayAttribute(
                                $attribute,
                                false,
                                $lang
                            );
                            $displ2 = trim($displ2);
                            if (!$displ2) {
                                $displ2 = "case 2 : this->displayAttribute($attribute,false, $lang)";
                            } else {
                                $displ2 .= "<!-- case 2 : this->displayAttribute($attribute,false, $lang) -->";
                            }
                        }

                        if (!isset($struct['OTM-TITLE'])) {
                            $struct['OTM-TITLE'] = true;
                        }

                        if (!isset($struct['OTM-NO-LABEL'])) {
                            if (!isset($struct['OTM-REMOVE-AUTO-LABEL'])) {
                                $struct['OTM-NO-LABEL'] = false;
                            } elseif ($struct['OTM-LABEL']) {
                                $link_label = $struct['OTM-LABEL'];
                            } else {
                                $struct['OTM-NO-LABEL'] = true;
                            }
                        }

                        if (!$struct['OTM-NO-LABEL'] and !$link_label) {
                            $link_label = $this->getAttributeLabel(
                                $attribute,
                                $lang,
                                $short = true
                            );
                        }
                    } else {
                        if (!isset($struct['OTM-TITLE'])) {
                            $struct['OTM-TITLE'] = true;
                        }
                        list($displ2, $link_url2) = $this->displayAttribute(
                            $attribute,
                            false,
                            $lang
                        );
                        $displ2 = trim($displ2);
                        if (!$displ2) {
                            $displ2 = "case 3 : this->displayAttribute($attribute,false, $lang)";
                        } else {
                            $displ2 .= "<!-- case 3 : this->displayAttribute($attribute,false, $lang) -->";
                        }
                        if (!isset($struct['OTM-NO-LABEL'])) {
                            if (!isset($struct['OTM-REMOVE-AUTO-LABEL'])) {
                                $struct['OTM-NO-LABEL'] = false;
                            } elseif ($struct['OTM-LABEL']) {
                                $link_label = $struct['OTM-LABEL'];
                            } else {
                                $struct['OTM-NO-LABEL'] = true;
                            }
                        }

                        if (!$struct['OTM-NO-LABEL'] and !$link_label) {
                            $link_label = $this->getAttributeLabel(
                                $attribute,
                                $lang,
                                $short = true
                            );
                        }
                    }

                    $displ2 = trim($displ2);

                    if ($displ2 and $link_url2) {
                        // if((!$struct["OTM-NO-LABEL"]) and (!$link_label)) $link_label = $this->getAttributeLabel($attribute, $lang, $short=true);
                        unset($link);
                        $link = [];
                        $title = '';
                        if ($struct['OTM-SHOW']) {
                            $title .= 'عرض ';
                        }
                        if ($struct['OTM-CARD']) {
                            $title .= 'بطاقة ';
                        }
                        if ($struct['OTM-FILE']) {
                            $title .= 'ملف ';
                        }
                        if ($struct['OTM-RETURNTO']) {
                            $title .= 'الانتقال إلى ';
                        }
                        if (!$struct['OTM-NO-LABEL']) {
                            $title .= $link_label . ' : ';
                        }
                        // else $title .= "debugg_rafik : ".var_export($struct,true);
                        if ($struct['OTM-TITLE']) {
                            $title .= $displ2;
                        }
                        $title = trim($title);

                        $title_detailed = $title . ' : ' . $displ2;
                        $link['URL'] = $link_url2;
                        $link['TITLE'] = $title;
                        if ($struct["STEP"]) {
                            // rafik 28/9/2022
                            // if the field cause of this OTM relation that has generated this other link standard
                            // is in a defined step the other link standard also should be related to this step
                            $link['STEP'] = $struct["STEP"];
                            $link['STEPS'] = $struct["STEPS"];
                        }
                        // no public opened like this in new UMS
                        // $link["PUBLIC"] = true;
                        $otherLinksArray[] = $link;
                        // if($attribute=="mainwork_start_paragraph_num") die("otherLinksArray = ".var_export($otherLinksArray,true));
                    } else {
                        $log = "for $attribute attribute display-title or link is missed ($displ2,$link_url2)";
                        // if($attribute=="mainwork_start_paragraph_num") die($log);
                        if ($genereLog) {
                            $this_otherLinkLog[] = $log;
                        }
                    }
                } elseif (
                    $struct['TYPE'] == 'MFK' and
                    $struct['LINK_TO_MFK_ITEMS']
                ) {
                    list($displ_arr, $link_url_arr) = $this->displayAttribute(
                        $attribute,
                        false,
                        $lang
                    );
                    foreach ($displ_arr as $displ_id => $displ2) {
                        unset($link);
                        $link = [];
                        $title = '';
                        if ($struct['OTM-SHOW']) {
                            $title .= 'عرض ';
                        } else {
                            $title .=
                                $this->tf($struct['LINK_TO_MFK_ITEMS']) . ' ';
                        }
                        $title .= $displ2;
                        $title = trim($title);

                        $title_detailed = $title . ' : ' . $displ2;
                        $link['URL'] = $link_url_arr[$displ_id];
                        $link['TITLE'] = $title;

                        // no public opened like this in new UMS
                        // $link["PUBLIC"] = true;
                        $otherLinksArray[] = $link;
                    }
                } else {
                    if ($genereLog) {

                        $this_otherLinkLog[] = "Attribute $attribute has not been selected as OneToXXX relation because ";
                        $this_otherLinkLog[] = "TYPE = " . $struct['TYPE'];
                        $this_otherLinkLog[] = "RELATION = " . $struct['RELATION'];
                        $this_otherLinkLog[] = "STEP = " . $struct['STEP'];
                        $this_otherLinkLog[] = "step = " . $step;
                    }
                    //if($attribute=="courses_template_id") AfwStructureHelper::dd(var_export($this_otherLinkLog,true));
                }
            }
        } else {
            $log = "mode is not edit or display : mode=$mode";
            if ($genereLog) {
                $this_otherLinkLog[] = $log;
            }
        }
        /*
        foreach ($this_otherLinkLog as $this_otherLinkLogItem) {
            AfwSession::contextLog($this_otherLinkLogItem, 'otherLink');
        }
        if(($mode == 'display' or $mode == 'edit') and ($this->getMyClass()=="CpcBookParagraph"))
        {
            die("otherLinksArray = ".var_export($otherLinksArray,true)." this_otherLinkLog : ".implode("<br>",$this_otherLinkLog));
        }
        */

        return $otherLinksArray;
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

        if (se_termine_par($operation, '_')) {
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
        global $lang;

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
        return $this->userCanDoOperationOnMeStandard(
            $auser,
            $operation,
            $operation_sql
        );
    }

    public function canBePublicDisplayed()
    {
        return false;
    }

    public function canBeSpeciallyDisplayedBy($auser)
    {
        return false;
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
        global $lang;
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

    // NO-ERROR-CHECK : option to disable error check on attribute

    private static function structureCheckable($desc)
    {
        return !$desc['NO-ERROR-CHECK'] and
            (!$desc['CATEGORY'] or $desc['ERROR-CHECK']);
    }

    public function getJsOfOnChangeOf(
        $attribute,
        $desc = '',
        $name_only = true,
        $original_attribute = ''
    ) {
        global $lang;
        $attribute_onchange_fn = $attribute . '_onchange';
        if ($name_only) {
            return "$attribute_onchange_fn()";
        }
        // $objme = AfwSession::getUserConnected();
        if (!$original_attribute) {
            $original_attribute = $attribute;
        }
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $original_attribute);
        }
        $qedit_suffix = substr($attribute, strlen($original_attribute));
        $js_source = '';

        $js_source .= "function $attribute_onchange_fn() { \n";
        foreach ($desc['DEPENDENT_OFME'] as $fld) {
            $fld_suffixed = $fld . $qedit_suffix;
            $js_source .= "   " . $fld_suffixed . "_reload(); \n";
            $js_source .= "   " . $fld_suffixed . "_onchange(); \n";
        }
        $js_source .= "\n} \n/*******************************  end of  $attribute_onchange_fn  *****************************/  ";

        return $js_source;
    }

    public function getJsOfReloadOf(
        $attribute,
        $desc = '',
        $original_attribute = ''
    ) {
        global $lang;
        // $objme = AfwSession::getUserConnected();
        if (!$original_attribute) {
            $original_attribute = $attribute;
        }

        $qedit_suffix = substr($attribute, strlen($original_attribute));

        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $original_attribute);
        }
        if ($desc['REQUIRED'] or $desc['MANDATORY']) {
            $option_empty_value = '';
        } else {
            $option_empty_value = ' value=0';
        }

        $thisid = $this->getId();
        $className = $this->getMyClass();
        $currmod = $this->getMyModule();
        $js_source = '';
        $attribute_reload_fn = $attribute . '_reload';

        if ($desc['DEPENDENCY']) {
            $desc['DEPENDENCIES'] = [$desc['DEPENDENCY']];
        }

        $dependencies_values = '';
        $fld_deps = '';
        $fld_deps_vals = '';
        foreach ($desc['DEPENDENCIES'] as $fld) {
            $fld_suffixed = $fld . $qedit_suffix;
            //  $fld_deps .= "/".$fld;
            //  $fld_deps_vals .= "+";
            //  $fld_deps_vals .= "'/'+\$(\"#$fld\").val()";
            if ($dependencies_values) {
                $dependencies_values .= ",\n";
            }
            $dependencies_values .= "                    post_attr_$fld: \$(\"#$fld_suffixed\").val()";
        }

        $js_source .= "function $attribute_reload_fn() {  \n";
        $js_source .= "     // alert(\"\"+\$(\"#$fld\").val());
                    // fld_deps_vals = '' $fld_deps_vals ;
                    // alert(\"$attribute_reload_fn running deps = [$fld_deps] = [\"+fld_deps_vals+\"] \");
                    \$.getJSON(\"../lib/api/anstab.php\", 
                    {
                    keepCurrent: 1,
                    cl:\"$className\",
                    currmod:\"$currmod\",
                    objid:\"$thisid\",
                    attribute: \"$original_attribute\",
                    attributeval: \$(\"#$attribute\").val(), 
                    
$dependencies_values
                        
                    },
                    
                    function(result)
                    {
                    var \$select = \$('#$attribute'); 
                    \$select.find('option').remove();
                    \$select.append('<option$option_empty_value></option>');
                    \$.each(result, function(i, field) {
                         \$select.append('<option value=' + i + '>' + field + '</option>');
                    });
                    });
                   }  
                   /*******************************  end of  $attribute_reload_fn  *****************************/  ";
        return $js_source;
    }

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
    /*
     rafik : obsolete replaced by attributeIsRequired better done;
     
     protected final function isMandatoryAttributeStandard($attribute, $desc="")
     {
           if(!$desc) $desc = AfwStructureHelper::getStructureOf($this,$attribute);
           else $desc = AfwStructureHelper::repareMyStructure($this,$desc, $attribute); 
           return (($desc["MANDATORY"] and $this->attributeIsApplicable($attribute)) or ($desc["REQUIRED"]));
     }
     
     
     public function isMandatoryAttribute($attribute, $desc="")
     {
           return $this->isMandatoryAttributeStandard($attribute, $desc);
     }
     */

    // Action :
    // Check common known errors
    // 1. Mandatory fields values
    // 2. Format of formatted fields
    // 3. Constraints on values for Constrainted fields
    // 4. Errors eventually in 'pillar-part' fields
    // return array of errors
    private function getCommonDataErrors(
        $lang = 'ar',
        $show_val = true,
        $step = 'all', 
        $erroned_attribute = null,
        $stop_on_first_error = false, $start_step=null, $end_step=null
    ) {
        global $errors_check_count, $errors_check_count_max;

        $cm_errors = [];

        if(!$erroned_attribute)
        {
            $this_db_structure = static::getDbStructure(
                $return_type = 'structure',
                $attrib = 'all',
                $step, $start_step, $end_step
            );
            // die("showErrorsAsSessionWarnings::getDbStructure($return_type, $attrib, $step, $start_step, $end_step) = ".var_export($this_db_structure,true));
            // if($step!="all") die("static::getDbStructure($return_type, $attrib, $step) = ".var_export($this_db_structure,true));
        }
        else
        {
            $this_db_structure = static::getDbStructure(
                $return_type = 'structure',
                $attrib = $erroned_attribute
            );
            //die("static::getDbStructure($return_type, $attrib) = ".var_export($this_db_structure,true));
        }

        
        

        foreach ($this_db_structure as $attribute => $desc) {
            $error_attribute = $desc['ERROR_ATTRIBUTE'];
            if (!$error_attribute) {
                $error_attribute = $attribute;
            }

            $attribute_is_required = $this->attributeIsRequired(
                $attribute,
                $desc
            );

            $attr_sup_categ = $desc['SUPER_CATEGORY'];
            $attr_categ = $desc['CATEGORY'];
            $attr_scateg = $desc['SUB-CATEGORY'];

            if ($attr_categ == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }
            if ($attr_scateg == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }
            if ($attr_sup_categ == 'ITEMS') {
                $desc['TYPE'] = 'MFK';
            }

            if ($this->stepContainAttribute($step, $attribute, $desc)) {
                /*
                if(($step==1) and ($attribute=="first_name_ar"))
                {
                    throw new AfwRuntimeException("step==$step : desc = ".var_export($desc,true));
                }*/
                // DEPENDENCY : for formula fields that are checkable (default not), we can define dependency field
                // so that no error check is performed until DEPENDENCY field has no errors
                if (
                    self::structureCheckable($desc) and
                    !$cm_errors[$desc['DEPENDENCY']]
                ) {
                    /*
                    if($attribute=="concernedGoalList")
                    {
                        throw new AfwRuntimeException("getCommonDataErrors for $attribute is reached at step $step");
                    }
                    */

                    if (!isset($cm_errors[$error_attribute])) {
                        $cm_errors[$error_attribute] = '';
                    }
                    //if($attribute=="tome") throw new AfwRuntimeException("kifech w step = $step w desc = ".var_export($desc,true));
                    $val_attr = $this->getVal($attribute);

                    //if($attribute=="monitoring") throw new AfwRuntimeException("rafik : this->getVal($attribute)=$val_attr", array("FIELDS_UPDATED"=>true, "AFIELD_ VALUE"=>true));
                    if ($show_val) {
                        $showed_val = " = $val_attr";
                    } else {
                        $showed_val = '';
                    }

                    if ($desc['TYPE'] == 'TEXT' or $desc['TYPE'] == 'MTEXT') {
                        $showed_val = '';
                    }

                    //if((static::$TABLE=="practice") and ($attribute=="explain")) throw new AfwRuntimeException("kifech val_attr($attribute) = [$val_attr] w step = $step w desc = ".var_export($desc,true));

                    // 1. required fields values
                    if ($desc['TYPE'] != 'MFK' and $attribute_is_required) {
                        if ($desc['TYPE'] == 'YN' and $val_attr == 'W') {
                            $val_attr = '';
                        }

                        if (
                            !$val_attr and
                            (!$desc['CAN_ZERO'] or $val_attr === '')
                        ) {
                            $spec_field_manda_token = "$attribute.FIELD_MANDATORY";
                            $spec_field_manda_token_message = $this->translate($spec_field_manda_token,$lang);
                            if ($spec_field_manda_token_message == $spec_field_manda_token) {
                                $tabName = $this->getMyTable();
                                $cm_errors[$error_attribute] .= $this->translateOperator('FIELD MANDATORY',$lang) .
                                                                    ' : ' .
                                                                    $this->translate($attribute, $lang);
                                
                                // below code we can not do because the tooltip can t support html
                                // if(AfwSession::config('MODE_DEVELOPMENT', false)) $cm_errors[$error_attribute] .= "<!-- $tabName.$attribute -->";    
                            } else {
                                $cm_errors[$error_attribute] .=
                                    $spec_field_manda_token_message . ", \n";
                            }

                            if($stop_on_first_error) break;
                        }
                        //if((static::$TABLE=="practice") and ($attribute=="explain")) throw new AfwRuntimeException("$attribute : kifech val_attr=[$val_attr] w step = $step w cm_errors = ".var_export($cm_errors,true));
                    }

                    // 2. Format of formatted fields
                    if (AfwFormatHelper::isFormatted($desc)) {
                        list(
                            $correctFormat,
                            $correctFormatMess,
                        ) = AfwFormatHelper::isCorrectFormat($val_attr, $desc);
                        if (!$correctFormat) {
                            if (!$desc['RESUME_TEXT_ERROR']) {
                                $cm_errors[$error_attribute] .=
                                    $this->translateOperator(
                                        'FIELD VALUE',
                                        $lang
                                    ) .
                                    ' ' .
                                    $this->translate($attribute, $lang) .
                                    $showed_val .
                                    ' : ';
                            }
                            $cm_errors[$error_attribute] .=
                                $this->translateOperator(
                                    $correctFormatMess,
                                    $lang
                                ) . ", \n";

                            if($stop_on_first_error) break;
                        }
                    }

                    // 3. Constraints on values for Constrainted fields
                    if ($desc['CONSTRAINTS']) {
                        // and ($val_attr != "")
                        $halted_constraint = $this->dataFollowConstraints(
                            $val_attr,
                            $desc['CONSTRAINTS']
                        );
                        if ($halted_constraint) {
                            $cm_errors[$error_attribute] .=
                                $this->translateOperator(
                                    'WRONG DATA FOR FIELD',
                                    $lang
                                ) .
                                ' : ' .
                                $this->translate($attribute, $lang) .
                                $showed_val .
                                ", \n <!--" .
                                var_export($halted_constraint, true)." -->";
                            if($stop_on_first_error) break;
                        }
                    }

                    // 4. Errors eventually in pillar or 'pillar-part' fields
                    //   pole or     is same as pillar but only if applicable
                    //   pillar-part   is same as pillar if attribute Is Required
                    if (
                        $desc['PILLAR'] or
                        $desc['POLE'] and
                        $this->attributeIsApplicable($attribute) or
                        $desc['PILLAR-PART'] and
                        $this->attributeIsRequired($attribute)
                    ) {
                        // only for FK or MFK Fields
                        if ($desc['TYPE'] == 'FK') {
                            if (intval($val_attr) > 0) {
                                $objVal = $this->get(
                                    $attribute,
                                    'object',
                                    '',
                                    false
                                );
                                if (
                                    !$objVal or
                                    !is_object($objVal) or
                                    $objVal->getId() != $val_attr or
                                    $attribute_is_required and !$objVal->getId()
                                ) {
                                    $cm_errors[$error_attribute] .=
                                        $this->translateOperator(
                                            'DELETED OR WRONG MANDATORY OBJECT',
                                            $lang
                                        ) .
                                        ' : ' .
                                        $this->translate($attribute, $lang) .
                                        $showed_val .
                                        ", \n";

                                    if($stop_on_first_error) break;
                                }

                                if (is_object($objVal)) {
                                    $err_obj_arr = $objVal->getDataErrors(
                                        $lang,
                                        $show_val
                                    );
                                    $objVal_disp = $objVal->getShortDisplay();
                                    $err_count = count($err_obj_arr);
                                    if ($err_count > 0) {
                                        $cm_errors[$error_attribute] .=
                                            $this->translateOperator(
                                                'PILLAR OBJECT',
                                                $lang
                                            ) .
                                            ' ' .
                                            $this->translate(
                                                $attribute,
                                                $lang
                                            ) .
                                            " = $objVal_disp " .
                                            $this->translateOperator(
                                                'CONTAIN',
                                                $lang
                                            ) .
                                            " $err_count " .
                                            $this->translateOperator(
                                                'ERRORS',
                                                $lang
                                            ) .
                                            ' :';
                                        foreach ($err_obj_arr as $err_text) {
                                            $cm_errors[$error_attribute] .=
                                                $err_text . "\n";
                                        }
                                        $cm_errors[$error_attribute] .=
                                            "______________________\n";

                                        if($stop_on_first_error) break;
                                    }
                                }
                            }
                        }

                        if ($desc['TYPE'] == 'MFK') {
                            $obj_arr = $this->get($attribute);
                            $errors_html = '';
                            $errors_max = 10;
                            $errors_i = 0;

                            foreach ($obj_arr as $obj_id => $objVal) {
                                if (
                                    is_object($objVal) and
                                    $errors_i < $errors_max
                                ) {
                                    if (
                                        $errors_check_count >
                                        $errors_check_count_max
                                    ) {
                                        throw new AfwRuntimeException(
                                            "too mauch commomn errors found by getCommonDataErrors for attribute $attribute (nb=$errors_check_count), be carefull on infinite loops"
                                        );
                                    }
                                    $err_obj_arr = $objVal->getDataErrors(
                                        $lang,
                                        $show_val
                                    );
                                    $err_count = count($err_obj_arr);
                                    if (
                                        $err_count > 0 and
                                        $errors_i < $errors_max
                                    ) {
                                        $errors_html .=
                                            "\n" .
                                            'السجل : ' .
                                            $objVal->getDisplay($lang);
                                        if ($err_count > 1) {
                                            $errors_html .=
                                                ' ' .
                                                $this->translateOperator(
                                                    'CONTAIN',
                                                    $lang
                                                ) .
                                                " $err_count " .
                                                $this->translateOperator(
                                                    'ERRORS',
                                                    $lang
                                                ) .
                                                ' : ';
                                            foreach ($err_obj_arr
                                                as $err_text) {
                                                $errors_html .=
                                                    "\n       " . $err_text;
                                                $errors_i++;
                                            }
                                        } else {
                                            $errors_html .=
                                                ' : ' .
                                                implode(' ', $err_obj_arr);
                                        }
                                    }
                                }
                            }
                            if ($errors_html) {
                                $fld_desc =
                                    $this->translateOperator(
                                        'PILLAR OBJECT',
                                        $lang
                                    ) .
                                    ' ' .
                                    $this->translate($attribute, $lang);
                                $cm_errors[$error_attribute] .=
                                    "يوجد أخطاء في $fld_desc : \n" .
                                    $errors_html .
                                    ", \n";

                                if($stop_on_first_error) break;
                            }
                        }
                    }

                    if ($desc['TYPE'] == 'MFK') {
                        $attribute_val0 = $this->calc($attribute);
                        if (!is_array($attribute_val0)) {
                            $attribute_val = trim($attribute_val0, ',');
                        } else {
                            $attribute_val = count($attribute_val0);
                        }

                        if ($attribute_is_required and !$attribute_val) {
                            $cm_errors[$error_attribute] .=
                                $this->translateOperator(
                                    'EMPTY LIST FOR REQUIRED FIELD',
                                    $lang
                                ) .
                                ' : ' .
                                $this->translate($attribute, $lang) .
                                ", \n";

                            if($stop_on_first_error) break;
                        }
                    }

                    if (!$cm_errors[$error_attribute]) {
                        unset($cm_errors[$error_attribute]);
                    } else {
                        $cm_errors[$error_attribute] = str_replace(
                            "\n",
                            '<br>',
                            $cm_errors[$error_attribute]
                        );
                        $cm_errors[$error_attribute] = str_replace(
                            ',',
                            '،',
                            $cm_errors[$error_attribute]
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            "\n"
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            ' '
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            ','
                        );
                        $cm_errors[$error_attribute] = trim(
                            $cm_errors[$error_attribute],
                            '/'
                        );
                        /*
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],"\n");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute]," ");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],",");
                        $cm_errors[$attribute] = trim($cm_errors[$attribute],"/");
                        
                        $cm_errors[$attribute] = mas_complete_len($cm_errors[$attribute], 36," ");
                        */
                    }
                }
            }
        }
        /*
        if($step==1)
        {
            throw new AfwRuntimeException("step==$step : cm_errors = ".var_export($cm_errors,true));
        }


        if(count($cm_errors)>0)
        {
            throw new AfwRuntimeException("There are errors : step==$step : cm_errors = ".var_export($cm_errors,true));
        }*/

        return $cm_errors;
    }

    // Action :
    // Check specific not known errors
    // Should be overwritten -if needed- by the child classes
    protected function getSpecificDataErrors(
        $lang = 'ar',
        $show_val = true,
        $step = 'all', 
        $erroned_attribute = null,
        $stop_on_first_error = false, $start_step=null, $end_step=null
    ) {
        return [];
    }

    // final because Should never been overwritten
    final public function getDataErrors(
        $lang = 'ar',
        $show_val = true,
        $recheck = false,
        $step = 'all',
        $ignore_fields_arr = null, 
        $attribute = null,
        $stop_on_first_error = false, $start_step=null, $end_step=null
    ) {
        global $errors_check_count, $errors_check_count_max;

        //if($errors_check_count>$errors_check_count_max) throw new AfwRuntimeException("too mauch errors found by getDataErrors (nb=$errors_check_count)");
        $errors_check_count++;

        // throw new AfwRuntimeException("what you do here");

        //rafik this line below is commented since 17/5/2022 because very strange why not saved objects can not be checked if contains errors before save
        //if($this->getId()<=0) return array();

        if (!isset($this->arr_erros[$step]) or $recheck) {
            $common_e_arr   =   $this->getCommonDataErrors($lang, $show_val, $step, $attribute, $stop_on_first_error, $start_step, $end_step);
            // die("showErrorsAsSessionWarnings::getCommonDataErrors($lang, $show_val, $step, $attribute, $stop_on_first_error, $start_step, $end_step) => ".var_export($common_e_arr,true));
            $specific_e_arr = $this->getSpecificDataErrors($lang, $show_val, $step, $attribute, $stop_on_first_error, $start_step, $end_step);
            $this->arr_erros[$step] = array_merge(
                $common_e_arr,
                $specific_e_arr
            );
        }

        $err_arr = $this->arr_erros[$step];

        foreach ($ignore_fields_arr as $ignore_field) {
            unset($err_arr[$ignore_field]);
        }

        // die(var_export($this->arr_erros,true));

        return $err_arr;
    }

    public function getDataErrorForAttribute($attribute)
    {
        return $this->arr_erros['all'][$attribute];
    }

    public function setDataErrorForAttribute($attribute, $error)
    {
        $this->arr_erros['all'][$attribute] = $error;
    }

    protected function paggableAttribute($attribute)
    {
        // can be overridden in subclasses
        return [true, ""];
    }

    public function attributeIsToPag($attribute)
    {
        list($paggable, $reason) = $this->paggableAttribute($attribute);
        if(!$paggable)
        {
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

    // هنا نتكلم عن العمود ككل وليس البيانات في العمود بحسب السجل
    final public function keyIsToDisplayForUser($key, $auser, $mode = 'DISPLAY')
    {
        $mode = strtoupper($mode);
        $mode_code = $mode;
        if ($mode == 'DISPLAY') {
            $mode_code = 'SHOW';
        }
        $structure = AfwStructureHelper::getStructureOf($this, $key);

        if ($structure['MINIBOX']) {
            $structure['SHOW'] = true;
        }

        global $display_in_edit_mode;
        if ($display_in_edit_mode['*'] and $structure['SHOW']) {
            if (
                !$structure['EDIT'] and
                $structure['CATEGORY'] != 'FORMULA' and
                $structure['TYPE'] != 'PK'
            ) {
                $structure['EDIT'] = true;
                $structure['READONLY'] = true;
            }
        }
        $user_can_see_attribute =
            ((!$structure["$mode-BFS"] or
                $auser and
                $auser->i_have_one_of_bfs($structure["$mode-BFS"])) and
                (!$structure["$mode-ROLES"] or
                    $auser and
                    $auser->i_have_one_of_roles($structure["$mode-ROLES"])));

        return ($user_can_see_attribute and
            ($structure["$mode-BFS"] or
                $structure["$mode_code-BFS"] or
                $structure["$mode-ROLES"] or
                $structure["$mode_code-ROLES"] or
                $structure[$mode] or
                $structure[$mode_code] or
                $auser and
                $auser->isAdmin() and
                $structure["$mode_code-ADMIN"] or
                $this->arr_erros['all'][$key] and
                $structure["$mode_code-ERROR"]))
            ? $structure
            : false;
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

    // هنا نتكلم  عن البيانات في العمود بحسب السجل
    final public function dataAttributeCanBeDisplayedForUser(
        $attribute,
        $auser,
        $mode = 'DISPLAY',
        $structure
    ) {
        $mode = strtoupper($mode);

        if (!$structure) {
            $structure = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $structure = AfwStructureHelper::repareMyStructure($this, $structure, $attribute);
        }

        $ugroups = $structure["$mode-UGROUPS"];

        if ($auser and $ugroups) {
            $auser_belong_to_ugroups = $auser->i_belong_to_one_of_ugroups(
                $ugroups,
                $this
            );
        }

        // if(($attribute=="idn") and ($auser->getId()==621)) die("this=$this, mode=$mode, auser=$auser, ugroups = ".var_export($ugroups,true)." -> auser_belong_to_ugroups=$auser_belong_to_ugroups");
        ($canDisplay = !$ugroups) or $auser_belong_to_ugroups;

        return $canDisplay ? $structure : false;
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

        $sci = null;
        if ($this->getVal('sci_id') > 0) {
            $sci = $this->get('sci_id');
        }
        //die("Sim=$simulation Sci =".var_export($sci,true));
        if ($sci) {
            return $sci->getVal('step_num');
        } elseif ($simulation) {
            // die("table=static::$TABLE  editByStep=$this->editByStep ");
            return $this->getDoneSteps();
        } else {
            return 0;
        }
    }

    public function setLastEditedStep($currstep)
    {

        $myAtable_id = $this->myAtableId;
        if (!$myAtable_id) {
            list($myModule, $myAtable) = $this->getThisModuleAndAtable();
            if ($myAtable) {
                $myAtable_id = $myAtable->getId();
            }
        }
        if ($myAtable_id) {
            $sci = new ScenarioItem();
            $sci->select('atable_id', $myAtable_id);
            $sci->select('step_num', $currstep);
            if ($sci->load()) {
                $this->set('sci_id', $sci->getId());
            } else {
                $war = "Can't find scenario item for TBL ($myAtable_id) stepnum=$currstep, contact your ADMIN.";
                $objme = AfwSession::getUserConnected();

                if ($objme and $objme->isAdmin()) $war .= "<br>.Check to be sure that scenario steps are created <a href='main.php?Main_Page=afw_mode_display.php&cl=Atable&id=$myAtable_id&currmod=pag&currstep=6'>click here to check</a>";


                AfwSession::pushWarning($war);
            }
        } else {
            AfwSession::pushWarning("Can't find atable id for this class, check to be sure that is pagged");
        }
    }

    public function getDoneSteps($error_offset = 0)
    {
        global $lang;
        //die("getDoneSteps for static::$TABLE ");
        if ($this->editByStep) {
            for ($istep = 1; $istep <= $this->editNbSteps; $istep++) {
                // die("istep=$istep before getStepErrors ");
                $err_arr = $this->getStepErrors(
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

    public function getStepErrors(
        $kstep,
        $lang = 'ar',
        $show_val = true,
        $recheck = false,
        $ignore_fields_arr = null, 
        $attribute = null
    ) {
        return $this->getDataErrors($lang, $show_val, $recheck, $kstep, $ignore_fields_arr, $attribute);
    }

    public function getAttributeError($attribute)
    {
        global $lang;
        $struct = AfwStructureHelper::getStructureOf($this, $attribute);
        $step = $struct['STEP'];
        if (!$step) {
            $step = 1;
        }

        $stepErrors_arr = $this->getStepErrors($step,$lang, true, false, [], $attribute);

        return $stepErrors_arr[$attribute];
    }

    public function getNbErrors($step = 'all', $force = false, $ignore_fields_arr = null)
    {
        if (!isset($this->arr_erros) or $force) {
            $this->getDataErrors('ar', true, $force, $step, $ignore_fields_arr);
        }

        return count($this->arr_erros[$step]);
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
        return ['delete', 'afw_mode_delete.php'];
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

    protected function simpleImportRecord(
        $item_field,
        $item_val,
        $dataRecord,
        $overwrite_data,
        $options,
        $check_data_ok,
        $lang
    ) {
        $errors = [
            'simpleImportRecord for this class is not already implemented',
        ];

        return [$this, $errors, [], []];
    }

    // the implementation of a new import process is just to override in the afw sub class
    // these 3 methods
    // 1. importRecord
    // 2. namingImportRecord
    // 3. getRelatedClassesForImport()
    // and write the [sub_class_name]_import_config.php see example : employee_import_config.php

    protected function importRecord(
        $dataRecord,
        $orgunit_id,
        $overwrite_data,
        $options,
        $lang,
        $dont_check_error
    ) {
        $errors = ['importRecord for this class is not already implemented'];

        return [$this, $errors, [], []];
    }

    protected function namingImportRecord($dataRecord, $lang)
    {
        throw new AfwRuntimeException('not implemented namingImportRecord method');
    }

    protected function getRelatedClassesForImport($options = null)
    {
        throw new AfwRuntimeException('not implemented getRelatedClassesForImport method');
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

    public static function getLightDownloadUrl($file_path, $extension)
    {
        return "<a target='_download' href='$file_path' class='download-icon download-$extension fright' data-toggle='tooltip' data-placement='top' title='[title]'>&nbsp;</a>";
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
        return $this->getFinishButtonLabelDefault(
            $lang,
            $nextStep,
            $form_readonly
        );
    }

    public function canFinishOnCurrentStep()
    {
        return !$this->finishOnlyLastStep or
            $this->currentStep == $this->editNbSteps;
    }

    // if class is displayed in edit mode so we can finish wizard by saving and remaining in same current step
    public function canFinishAsSaveAndRemainInCurrentStep()
    {
        $className = $this->getMyClass();
        return self::classIsDisplayedInEditMode($className);
    }

    public static function classIsDisplayedInEditMode($className)
    {
        global $display_in_edit_mode, $display_in_display_mode;
        return $display_in_edit_mode[$className] or
            $display_in_edit_mode['*'] and
            !$display_in_display_mode[$className];
    }

    protected function getReadOnlyFormFinishButtonLabel()
    {
        return '';
    }

    final public function getFinishButtonLabelDefault(
        $lang,
        $nextStep,
        $form_readonly = 'RO'
    ) {
        $className = $this->getMyClass();
        if (self::classIsDisplayedInEditMode($className)) {
            if ($form_readonly != 'RO') {
                return $this->translate('SAVE', $lang, true);
            } else {
                $ret = $this->getReadOnlyFormFinishButtonLabel();
                if ($ret) return $this->translate($ret, $lang, true);
                return '';
            }
        }

        if ($this->editByStep and $nextStep > 0 and $this->isDraft()) {
            return $this->translate(
                'COMPLETE_LATER' . $form_readonly,
                $lang,
                true
            );
        }
        //$this->editNbSteps

        return $this->translate('FINISH' . $form_readonly, $lang, true);
    }

    public function getNextStepAfterFinish($current_step)
    {
        // shoulbe keep same current step if we have no display mode (only edit mode)
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
        return $this->getFieldGroupDefaultInfos($fgroup);
    }

    final public function getFieldGroupDefaultInfos($fgroup)
    {
        $css_fg = 'none';
        if (se_termine_par($fgroup, 'List')) {
            $css_fg = 'pct_100';
        }

        if (
            se_termine_par($fgroup, 'Group') or
            se_termine_par($fgroup, 'Group50')
        ) {
            $css_fg = 'pct_50';
        }

        if (se_termine_par($fgroup, 'Group66')) {
            $css_fg = 'pct_66';
        }

        if (se_termine_par($fgroup, 'Group33')) {
            $css_fg = 'pct_33';
        }

        if (se_termine_par($fgroup, 'Group25')) {
            $css_fg = 'pct_25';
        }

        return ['name' => $fgroup, 'css' => $css_fg];
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

    public function isMultipleObjectsAttribute($attribute, $desc = '')
    {
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }

        $attr_sup_categ = $desc['SUPER_CATEGORY'];
        $attr_categ = $desc['CATEGORY'];
        $attr_scateg = $desc['SUB-CATEGORY'];

        if ($attr_categ == 'ITEMS') {
            return true;
        }
        if ($attr_scateg == 'ITEMS') {
            return true;
        }
        if ($attr_sup_categ == 'ITEMS') {
            return true;
        }
        if ($desc['TYPE'] == 'MFK') {
            return true;
        }

        return false;
    }

    public function previewAttribute($attribute, $desc = '', $max_length = 56)
    {
        global $lang;
        if (!$desc) {
            $desc = AfwStructureHelper::getStructureOf($this, $attribute);
        } else {
            $desc = AfwStructureHelper::repareMyStructure($this, $desc, $attribute);
        }
        if (!$this->isMultipleObjectsAttribute($attribute, $desc)) {
            $return = $this->showAttribute($attribute);
        } else {
            $objects = $this->get($attribute);
            $array = [];
            foreach ($objects as $object) {
                $array[] = $object->getDisplay($lang);
            }
            $mfk_show_sep = $desc['LIST_SEPARATOR'];
            if (!$mfk_show_sep) {
                $mfk_show_sep = $desc['MFK-SHOW-SEPARATOR'];
            }
            if (!$mfk_show_sep) {
                $mfk_show_sep = '، ';
            }
            $return = implode($mfk_show_sep, $array);
            $etc = '.. (' . count($array) . ' item(s))';
            $return = truncateArabicJomla($return, $max_length, $etc);
        }

        return $return;
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



    protected function enabledIcon($attribute, $icon, $structure = null)
    {
        $thisWizwrd = new AfwWizardHelper($this);
        return $thisWizwrd->standardEnabledIcon($attribute, $icon, $structure);
    }

    public function getMyPicture()
    {
        return '';
    }

    public function getAttributeTooltip($attribute, $lang = 'ar')
    {
        $col_tooltip = $attribute . '.tooltip';
        $val_tooltip = $this->tm($col_tooltip, $lang);
        if ($val_tooltip != $col_tooltip) {
            return $val_tooltip;
        } else {
            $col_tooltip = $attribute . '_tooltip';
            $val_tooltip = $this->tm($col_tooltip, $lang);
            if ($val_tooltip != $col_tooltip) {
                return $val_tooltip;
            } else {
                $col_tooltip = $attribute . '.tooltip';
                $val_tooltip = $this->tf($col_tooltip, $lang);
                if ($val_tooltip != $col_tooltip) {
                    return $val_tooltip;
                } else {
                    $col_tooltip = $attribute . '_tooltip';
                    $val_tooltip = $this->tf($col_tooltip, $lang);
                    if ($val_tooltip != $col_tooltip) {
                        return $val_tooltip;
                    }
                }
            }
        }

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
        return '';
    }

    public function getCurrentFrontStep()
    {
        return 1;
    }

    public function forceMode()
    {
        $this->force_mode = true;
    }

    public function isImportantField($fieldname, $desc)
    {
        if (($desc['IMPORTANT'] == "HIGH") or ($desc['IMPORTANT'] == "NORMAL")) return true;
        if ((!$desc['IMPORTANT']) or ($desc['IMPORTANT'] == "IN") or ($desc['IMPORTANT'] == "MEDIUM")) {
            $uk_arr = $this->UNIQUE_KEY ? $this->UNIQUE_KEY : [];
            return ($desc['TYPE'] == 'PK' or $desc['PILLAR'] or $desc['POLE'] or in_array($fieldname, $uk_arr));
        }
        return false;
    }

    public function importanceCss($fieldname, $desc)
    {
        $importance = strtolower($desc["IMPORTANT"]);
        if (!$importance) $importance = "in";
        $uk_arr = $this->UNIQUE_KEY ? $this->UNIQUE_KEY : [];
        if (($importance == "in") and in_array($fieldname, $uk_arr)) $importance = "high";
        elseif (($importance == "in") and ($desc['TYPE'] == 'PK' or $desc['PILLAR'] or $desc['POLE'])) $importance = "normal";
        elseif ($importance == "in") $importance = "small";
        if (($fieldname == "عرض") or ($fieldname == "view") or ($fieldname == "display")) $importance = "small";
        if (($fieldname == "تعديل") or ($fieldname == "edit") or ($fieldname == "update")) $importance = "high";

        return $importance;
    }

    public function getAfwImportantFields()
    {
        $this_db_structure = static::getDbStructure(
            $return_type = 'structure',
            $attribute = 'all'
        );

        $data = [];

        foreach ($this_db_structure as $fieldname => $struct_item) {
            if ($this->isImportantField($fieldname, $struct_item)) {
                $data[] = $fieldname;
            }
        }

        return $data;
    }

    // returns array key value containing list of important fields of
    // this object
    public function importants()
    {
        $ifields = $this->getAfwImportantFields();
        $result = [];
        foreach ($ifields as $ifield) {
            $result[$ifield] = $this->getVal($ifield);
        }

        return $result;
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
        if (!$filter) return true; else return false;        
    }

    /*************************     private methods       ************************/
    

    /****************************************************************************/


    

    public function getCategorizedAttribute($attribute, $attribute_category, $attribute_type, $structure, $what, $format, $integrity, $max_items, $lang, $call_method="")
    {
        /*
        if (!$structure['NO-CACHE'] and isset($this->gotItemCache[$attribute][$what])) {

            $return = $this->gotItemCache[$attribute][$what];
            $log_getter = 'return from gotItemCache = ' . var_export($return, true);
            if ($attribute == 'requestList0000') {
                die($log_getter);
            } else $afw_getter_log[] = "$log_getter";
        }*/
        $return = null;
        if (!$return) {
            $this_TABLE = static::$TABLE;
            $this_id = $this->getId();

            /* obsolete in v3.0
            if($not_ allowed_get[$this_TABLE][$attribute])
            {
                    throw new AfwRuntimeException("FOR TABLE $this_TABLE (record id = $this_id) ->get($attribute,$what) is not allowed here");
            }
            */

            
            $b_abstract = false;
            //if($this->MY_DEBUG) AFWDebugg::log("category of $attribute = ".$structure["CATEGORY"]);

            switch ($attribute_category) {
                case 'ITEMS':
                    $array = [];
                    /*
                    if((!$structure["NO-CACHE"]) and $this->gotItems Cache[$attribute])   
                    {
                        $return = $this->gotItems Cache[$attribute];
                        if($attribute=="requestList") die("return from gotItems Cache = " . var_export($return,true));
                    }
                    */
                    if (!$return) {
                        list($ansTab, $ansMod,) = static::answerTableAndModuleFor($attribute);
                        if ($ansTab) {
                            $className = AfwStringHelper::tableToClass($ansTab);
                            $object = new $className();
                            // $object->setMyDebugg($this->MY_DEBUG);
                            if ($structure['ITEM']) {
                                $item_oper = $structure['ITEM_OPER'];
                                $item_name = $structure['ITEM'];
                                $this_id = $this->getAfieldValue(
                                    $this->getPKField()
                                );

                                if ($item_oper) {
                                    $object->where("me.$item_name $item_oper '$this_id' ");
                                } else {
                                    $object->where("me.$item_name = '$this_id' ");
                                }
                            }
                            if ($structure['WHERE']) {
                                $sql_where = $this->decodeText($structure['WHERE']);
                                $object->where($sql_where);
                            }
                            /* obsolete since v3.0
                            format can not be used for SQL where
                            if($format and ($format!="IMPLODE")) {
                                $object->where($format);
                            }
                            */

                            if (!$structure['LOGICAL_DELETED_ITEMS_ALSO']) {
                                $object->select($object->fld_ACTIVE(),'Y');
                            }
                            $object->debugg_tech_notes = "before loadMany for Items of attribute : $attribute";
                            if ($max_items) {
                                $limit_loadMany = $max_items;
                            } else {
                                $limit_loadMany = '';
                            }

                            $return = $object->loadMany($limit_loadMany,$structure['ORDER_BY']);
                            // if($attribute=="requestList") die("sql_for_loadmany of $attribute = ".$this->debugg_sql_for_loadmany." returned list => ".var_export($return,true));

                            // if(!$structure["NO-CACHE"]) $this->gotIte msCache[$attribute] = $return;
                        } else {
                            throw new AfwRuntimeException(
                                'Check if ANSWER property is defined for attribute ' .
                                    $attribute .
                                    ' having type ITEMS in DB_STRUCTURE of table ' .
                                    static::$TABLE,
                                $call_method
                            );
                        }
                    }
                    break;

                case 'FORMULA':
                    global $lang;
                    if (!$lang) $lang = 'ar';
                    $return = AfwFormulaHelper::executeFormulaAttribute($this, $attribute, NULL, $lang, $what);
                    $return_isset = isset($return);
                    $this_debugg_formula_log = "AfwFormulaHelper::executeFormulaAttribute(this, $attribute, NULL, $lang, $what) = [return=$return/isset=$return_isset]";                    
                    $attribute_value = $return;

                    break;
                case 'VIRTUAL':
                    $b_abstract = true;
                    if (AfwLoadHelper::cacheManagement($this)) {
                        $this->OBJECTS_CACHE[$attribute] = $this->getVirtual($attribute, $what, $format);
                    }

                    break;
                case 'SHORTCUT':
                    //if($attribute=="skill_type_id") throw new AfwRuntimeException("$attribute is SHORTCUT");
                    //if($this->MY_DEBUG) AFWDebugg::log("Case SHORTCUT");
                    $report_arr = [];
                    $forced_value = $this->getAfieldValue($attribute);
                    $report_arr[] = "forced_value=$forced_value";
                    $default_value = $structure['DEFAULT'];
                    if (!$default_value) {
                        $default_value = '';
                    }
                    if (
                        isset($structure['SHORTCUT']) &&
                        $structure['SHORTCUT']
                    ) {
                        $attribute_shortcut = $structure['SHORTCUT'];
                    }
                    //die("shortcut 2 = ".$attribute_shortcut);

                    // if($attribute_shortcut=="skill_type_id") throw new AfwRuntimeException("$attribute forced_value = $forced_value");
                    if (strpos($attribute_shortcut, '.') !== false) {
                        //if($this->MY_DEBUG) AFWDebugg::log("Object $attribute exist");
                        $fields = explode('.', $attribute_shortcut);
                        $sc_cat = $structure['SHORTCUT-CATEGORY'];
                        $sc_cat_arr = explode('.', $sc_cat);
                        $count = count($fields);
                        if ($count > 1) {
                            //die("shortcut 3 = ".var_export($fields,true));
                            //if($this->MY_DEBUG) AFWDebugg::log("count field = $count");
                            if ($sc_cat_arr[0] == "FORMULA")
                                $object = $this->calc($fields[0], true, "object");
                            else
                                $object = $this->het($fields[0], '', $optim_lookup=false); // optim=false mandatory because in shortcut we need to load object to get next attribute of shortcut 
                                                                             // (just a decode is not enough)
                            if ($object) {
                                if (!is_object($object)) {
                                    throw new AfwRuntimeException("$object returned by the shortcut[$attribute_shortcut] the shortcut item [" . $fields[0] . "] is not an object");
                                }
                                $report_arr[] =
                                    'fields[0]=' .
                                    $object->getDisplay('ar');
                                // if($attribute_shortcut=="goal.system_id") die("shortcut($attribute_shortcut) object 0 = ".var_export($object,true));
                                for ($i = 1; $i < $count - 1; $i++) {
                                    if ($object === null) {
                                        if ($integrity) {
                                            throw new AfwRuntimeException(
                                                'Impossible to get [' .
                                                    $fields[$i] .
                                                    "] à cause d'une valeur NULL of object " .
                                                    $fields[$i - 1] .
                                                    ", veuillez vérifier attribute " .
                                                    $attribute .
                                                    ' de type SHORTCUT.'
                                            );
                                        } else {
                                            break;
                                        }
                                    } else {
                                        if ($this->MY_DEBUG and false) {
                                            AFWDebugg::log(
                                                'object[' .
                                                    ($i - 1) .
                                                    ']'
                                            );
                                        }
                                        if ($this->MY_DEBUG and false) {
                                            AFWDebugg::log(
                                                $object,
                                                true
                                            );
                                        }
                                        if ($this->MY_DEBUG and false) {
                                            AFWDebugg::log(
                                                "befor get fields[$i]=" .
                                                    $fields[$i]
                                            );
                                        }

                                        if ($sc_cat_arr[$i] == "FORMULA")
                                            $object = $object->calc($fields[$i], true, "object");
                                        else
                                            $object = $object->het($fields[$i]);

                                        if ($object) {
                                            $report_arr[] =
                                                "fields[$i]=" .
                                                $object->getDisplay(
                                                    'ar'
                                                );
                                        }
                                    }
                                }
                                //die("short cut analyse for attribute $attribute = ".var_export($object,true));
                                if ($object === null) {
                                    if ($this->MY_DEBUG and false) {
                                        AFWDebugg::log(
                                            'Object is NULL'
                                        );
                                    }
                                    if ($integrity) {
                                        throw new AfwRuntimeException(
                                            'Impossible to get [' .
                                                $fields[$count - 1] .
                                                "] à cause d'une valeur NULL of object " .
                                                $fields[$count - 2] .
                                                ", veuillez vérifier attribute " .
                                                $attribute .
                                                ' de type SHORTCUT.',
                                            $call_method
                                        );
                                    } else {
                                        switch (strtolower($what)) {
                                            case 'object':
                                                $return = null;
                                                break;
                                            case 'value':
                                            case 'decodeme':
                                                $return = $forced_value
                                                    ? $forced_value
                                                    : $default_value;

                                            case 'report':
                                                $return = implode(
                                                    "\n<br>",
                                                    $report_arr
                                                );
                                                break;
                                                break;
                                        }
                                    }
                                } else {
                                    if ($this->MY_DEBUG and false) {
                                        AFWDebugg::log('Object exist');
                                    }

                                    if ($what == 'report') {
                                        $return = $object->get(
                                            $fields[$count - 1],
                                            'value',
                                            $format,
                                            $integrity
                                        );
                                        $report_arr[] = "last : fields[$count-1]=" .
                                            $fields[$count - 1] .
                                            ' => ' .
                                            $return;
                                        $return = implode("\n<br>",$report_arr);
                                    } else {
                                        $report_arr[] =
                                            "get(fields[$count-1]=" .
                                            $fields[$count - 1] .
                                            " ,$what) = " .
                                            $return;
                                        $return = $object->get(
                                            $fields[$count - 1],
                                            $what,
                                            $format,
                                            $integrity
                                        );
                                    }

                                    // if(($fields[0]=="course_session") and ($fields[1]=="attendanceList"))
                                    // if(($fields[0]=="cher_id") and ($fields[1]!="emp_num") and ($fields[1]!="orgunit_name") and ($fields[1]!="orgunit_id") and ($fields[1]!="orgunit_id")) 
                                    // throw new AfwRuntimeException("fields=".implode("|\n<br>|",$fields)."\n<br> report_arr=".implode("\n<br>",$report_arr)."\n<br> >>> rafik debugg :: get(".$fields[$count-1].", $what, $format) = $return");
                                    if ($this->MY_DEBUG and false) {
                                        AFWDebugg::log($return, true);
                                    }
                                }
                            } else {
                                if ($integrity) {
                                    throw new AfwRuntimeException(
                                        'Impossible to get [' .
                                            $fields[1] .
                                            "] à cause d'une valeur NULL of object " .
                                            $fields[0] .
                                            ", veuillez vérifier attribute " .
                                            $attribute .
                                            ' de type SHORTCUT. '.
                                        $call_method
                                    );
                                } else {
                                    $return = $forced_value
                                        ? $forced_value
                                        : $default_value;
                                    //if($default_value and ($default_value==$return)) die("rafik test 0013");
                                    break;
                                }
                            }
                        } else {
                            throw new AfwRuntimeException(
                                "Property SHORTCUT of attribute " .
                                    $attribute .
                                    ' de la table ' .
                                    static::$TABLE .
                                    " doit avoir plus d'un element.",
                                $call_method
                            );
                        }
                    } else {
                        throw new AfwRuntimeException(
                            "Property SHORTCUT non définie of attribute " .
                                $attribute .
                                ' dans DB_STRUCTURE de la table ' .
                                static::$TABLE .
                                '.',
                            $call_method
                        );
                    }
                    break;
            }
            if ((!$structure['NO-CACHE']) and $return) {
                // $this->gotItemCache[$attribute][$what] = $return;
                // die("attribute=$attribute, attribute_category=$attribute_category set in gotItemCache = " . var_export($this->gotItemCache,true));
            }
        }

        $attr_sup_categ = $structure['SUPER_CATEGORY'];
        $attr_categ = $structure['CATEGORY'];
        $attr_scateg = $structure['SUB-CATEGORY'];
        
        if (strtolower($what) == 'value') {
            if ($return and $return instanceof AFWObject) {
                $return = $return->getId();
            }

            if (
                $attr_categ == 'ITEMS' or
                $attr_scateg == 'ITEMS' or
                $attr_sup_categ == 'ITEMS'
            ) {
                $return_arr = $return;
                $return = '';
                foreach ($return_arr as $return_item) {
                    $return .= ',' . $return_item->getId();
                }
                if ($return) {
                    $return .= ',';
                }
            }
        } elseif (strtolower($what) == 'decodeme') {
            if (
                $attr_categ == 'ITEMS' or
                $attr_scateg == 'ITEMS' or
                $attr_sup_categ == 'ITEMS'
            ) {
                $format = strtolower($format);
                
                $arr_items_decoded = [];
                foreach ($return as $return_item) {
                    $arr_items_decoded[] = $return_item->getDisplay($lang);
                }
                

                if ($format == 'implode') {
                    $return = implode(',', $arr_items_decoded);
                } else {
                    $return = $arr_items_decoded;
                }
            } elseif ($return and $return instanceof AFWObject) {
                $return = $return->getDisplay($lang);
            } else {

                if (!isset($return)) {
                    //if(($attribute=="homework")) die("what=$what, rafik entered in non implemented zone of decode of attribute $attribute formula log = $this_debugg_formula_log  returned : [return=$return, formatted=$formatted, return_formatted=$return_formatted] ");
                    if (($attribute == "xxxx")) throw new AfwRuntimeException("attribute-action to be not implemented this_debugg_formula_log=$this_debugg_formula_log attr_categ=$attr_categ attribut=$attribute, attribute_value=$attribute_value, format=$format, what=$what, gettype=" . $attribute_type);
                }
            }

            $unit = $structure['UNIT'];
            $hide_unit = $structure['DISPLAY_HIDE_UNIT'];
            if ($unit and $return and !$hide_unit) {
                $return .= ' ' . $unit;
            }
        } else {
            if ($integrity and !isset($return)) {
                $suggest = "";
                if ($attr_categ == "FORMULA") $suggest = "often this happen when you dont call to return \AfwFormulaHelper::calculateFormulaResult($this,\$attribute, \$what) on your getFormuleResult method";
                throw new AfwRuntimeException(
                    "Erreur : no-return defined for get : what=$what,attribut=$attribute, format=$format, attr_categ=$attr_categ ($suggest), gettype=" .
                        $attribute_type .
                        ' STRUCTURE = ' .
                        var_export($structure, true)
                );
            }
        }
        $link_url = $structure['LINK-URL'];
        $link_css_class = $structure['LINK-CSS'];
        if (!$link_css_class) {
            $link_css_class = 'nice_link';
        }

        $target = '';
        $popup_t = '';

        $link_url = $this->decodeText($link_url, '', false);

        if ($link_url and $return != '' and $format != 'NO-URL') {
            $return = "<a class='$link_css_class' $target href='$link_url&popup=$popup_t'>$return</a>";
        }

        return $return;
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
                    static::$TABLE.
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

    public function func($function,
                            $group_by = '',
                            $throw_error = true,
                            $throw_analysis_crash = true)
    {
        return AfwSqlHelper::aggregFunction($this, $function, $group_by, $throw_error, $throw_analysis_crash);
    }


    /*********************************XXXXXXXXXXXXXXXXXXXXXXXX**************************** */

    

    

    

    /*
    private function getTheResultRowId($result_row=null)
    {
    }

    private function getTheResultRowIndex($result_row=null)
    {
        $loadByIndex = null; 
        if (is_array($this->UNIQUE_KEY) and count($this->UNIQUE_KEY) > 0) {
            $uk_val_arr = [];
            $isLoadByIndex = true;
            foreach ($this->UNIQUE_KEY as $key_item) {
                if (!isset($result_row[$key_item])) {
                    $isLoadByIndex = false;
                } else {
                    $uk_val_arr[] = $result_row[$key_item];
                }
            }

            if ($isLoadByIndex) {
                $loadByIndex = implode('-/-', $uk_val_arr);
            }
            // if(($className=="TravelHotel") and (!$value)) throw new AfwRuntimeException("loadByIndex=$loadByIndex this->SEARCH_TAB = ".var_export($this->SEARCH_TAB,true));
        }

        return $loadByIndex;
    }*/
}
