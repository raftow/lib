<?php

$file_dir_name = dirname(__FILE__); 
// old include of afw.php

class AfwCacheSystem
{
    private static $cacheSystemSingleton = null;
    
    private $cacheObjects = array();
    private $indexTable = array();
    private $analysisTable = array();
    private $statisticsTable = array();
    private $triggerTable = array();
    
    // object specific audit
    private $module_to_audit = "btb";
    private $table_to_audit = "bus_file";
    private $object_id_to_audit = 2;
    private $repeat_of_load_of_audited_object = 0;
    private $trigger_max = 500;
    private $trigger_count = 0;
    private $context_to_audit = "";

    


    public static $DATABASE		= ""; 
    public static $MODULE		    = ""; 
    public static $TABLE			= ""; 
    public static $DB_STRUCTURE = array(
                                        'id' => array("TYPE" => "PK", "SHOW" => true),
                                    );
    
    /**
     * @return AfwCacheSystem
     * 
     */
    
    public static function getSingleton()
    {
        if(!self::$cacheSystemSingleton)
        {
            self::$cacheSystemSingleton = new AfwCacheSystem();
        } 
        
        
        return self::$cacheSystemSingleton;
    }

    function getStructureFromCache($class_name)
    {
        return $this->getFromCache("hzm", $class_name, "structure", "structure_cache");
    }

    function setStructureIntoCache($class_name, $db_structure)
    {
        return $this->putIntoCache("hzm", $class_name, $db_structure, $indexValue="structure", $context="structure_cache");        
    }
    /**
     * 
     * @return AFWObject
     */
    
    function getFromCache($module_code, $table_name, $id, $context="")
    {
            /* for debugg    
            if(($this->context_to_audit) and ($this->context_to_audit==$context))
            {
                throw new AfwRuntimeException("for audit table $module_code -> $table_name [$id] get from cache audit triggered for context $context ");
            }*/
            
            if(($id != "empty") and ($context != "structure_cache") and (!is_numeric($id)))  $id = $this->indexTable[$module_code][$table_name][$id];
            if(!$id) return null;
            if((AfwSession::config("MODE_MEMORY_OPTIMIZE", false)) and (!AfwSession::config("CACHE_FORCE_USE", true)))
            {
                die("MODE_MEMORY_OPTIMIZE : no cache to get from getFromCache"); 
                //return null; 
            }
            
            $try = $this->analysisTable[$module_code][$table_name][$id]["try"];
            if(!$try) $try = 1;
            else $try++;
            $this->analysisTable[$module_code][$table_name][$id]["try"] = $try;
            
            $found = $this->analysisTable[$module_code][$table_name][$id]["found"];   
            if(!$found) $found = 0;
            
            $not_found = $this->analysisTable[$module_code][$table_name][$id]["not-found"];   
            if(!$not_found) $not_found = 0;
            
            
            
            $return = $this->cacheObjects[$module_code][$table_name][$id];
            
            if($return)
            {
                   $found++;   
            }
            else
            {
                    $not_found++;
                    if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name) and ($this->object_id_to_audit==$id))
                    {
                        $this->repeat_of_load_of_audited_object++;
                        if($this->repeat_of_load_of_audited_object > 2)
                        {
                             throw new AfwRuntimeException("once = $this->repeat_of_load_of_audited_object that $table_name [$id] loaded and not found in _page_cache_objects = ".var_export($this->cacheObjects,true)."\n _cache_analysis=".var_export($this->analysisTable,true));
                        }
                    }
            }
            
            $this->analysisTable[$module_code][$table_name][$id]["found"] = $found;
            $this->analysisTable[$module_code][$table_name][$id]["not-found"] = $not_found;   
            
            return $return;    
    }
    
    public function hasCacheFor($module_code, $table_name)
    {
          return (count($this->cacheObjects[$module_code][$table_name])>0);
    }
    
    public function hasCacheIndexedFor($module_code, $table_name)
    {
          return (count($this->indexTable[$module_code][$table_name])>0);
    }
    
    function removeTableFromCache($module_code, $table_name)
    {
            if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name))
            {
                     throw new AfwRuntimeException("cache is removed for table to audit : [$module_code - $table_name]");
            }
            unset($this->cacheObjects[$module_code][$table_name]);
    }
    
    
    function removeObjectFromCache($module_code, $table_name, $id)
    {
            if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name) and ($this->object_id_to_audit==$id))
            {
                     throw new AfwRuntimeException("object to audit is removed from cache [$module_code, $table_name, $id]");
            }
            unset($this->cacheObjects[$module_code][$table_name][$id]);
    } 
    
            
    function putIntoCache($module_code, $table_name, $object, $indexValue="", $context="", $id=null, $obj_cl=null)
    {
            if((!AfwSession::config("MODE_MEMORY_OPTIMIZE", false)) or (AfwSession::config("CACHE_FORCE_USE", true)))
            {
                    if($context != "structure_cache")
                    {
                        if(is_object($object))
                        {
                            $id = $object->getId();
                            if(!$id) $id = "empty";
                            $obj_cl = get_class($object);
                        }
                        elseif(is_string($object))
                        {
                            if((!$id) or (!$obj_cl)) throw new AfwRuntimeException("error/warning string is to store in cache system without specify class and id of object related");                            
                        }
                        else throw new AfwRuntimeException("strange not object nor string to store in cache system : ".var_export($object, true));
                    }
                    else
                    {
                        $id = "structure";
                        $obj_cl = $table_name;
                    }
                    
                    
                    if(!$this->cacheObjects[$module_code][$table_name][$id])
                    {
                            
                            
                            if(!$this->statisticsTable[$obj_cl]) $this->statisticsTable[$obj_cl] = 1;
                            else $this->statisticsTable[$obj_cl]++;
                        
                            if(!$this->statisticsTable["total"]) $this->statisticsTable["total"] = 1;
                            $this->statisticsTable["total"]++;
                    }
                    else
                    {
                        unset($this->cacheObjects[$module_code][$table_name][$id]);
                    }
                    
                    $this->cacheObjects[$module_code][$table_name][$id] = $object;
                    
                    /* for debugg
                    if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name) and ($this->object_id_to_audit==$id))
                    {
                        throw new AfwRuntimeException("$module_code -> $table_name [$id] putted into intelligent cache : ".var_export($_page_cache_objects,true)."\n _cache_analysis=".var_export($_cache_analysis,true));
                    }
                    */

                    
                    if($id and $indexValue and ($indexValue != $id))
                    {
                         $this->indexTable[$module_code][$table_name][$indexValue] = $id;
                    }

                    /* for debugg
                    if(($this->context_to_audit) and ($this->context_to_audit==$context))
                    {
                        throw new AfwRuntimeException("for audit table $module_code -> $table_name [$id] put into cache audit triggered for context $context ");
                    }*/
                    
                    return true;
                       
            }
            else
            {
                    /* for debugg
                    if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name) and ($this->object_id_to_audit==$id)) 
                    {
                        throw new AfwRuntimeException("for audit table $module_code -> $table_name [$id] put into intelligent cache skipped because memory optimize ");
                    }    
                    */
            }
            
            return false; 
    }
    
    function skipPutIntoCache($module_code, $table_name, $id, $reason)
    {
            if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name) and ($this->object_id_to_audit==$id)) 
            {
                throw new AfwRuntimeException("for audit table $module_code -> $table_name [$id] put into intelligent cache skipped because : $reason ");
            }
    }

    
    function cache_analysis_to_html($light=false, $lang="ar")
    {
        $message = "";
        
        foreach($this->cacheObjects as $module_code => $module_cache_objects)
        {
             $message .= "<hr>cache system for $module_code : <hr><br>";
             foreach($module_cache_objects as $table_name => $table_cache_objects)
             {
                    $message .= "<br><b>cache system from table $table_name. </b><br>";
                    $message .= "<table dir='ltr' class='grid'>";
                    $message .= "<tr><th><b>ID</b></th><th><b>Object</b></th><th><b>Tried</b></th><th><b>Found</b></th><th><b>Not found</b></th></tr>";
                    $table_cache_objects_count = count($table_cache_objects);
                    foreach($table_cache_objects as $id => $cache_object)
                    {
                          $found = $this->analysisTable[$module_code][$table_name][$id]["found"];
                          $not_found = $this->analysisTable[$module_code][$table_name][$id]["not-found"];
                          $try = $this->analysisTable[$module_code][$table_name][$id]["try"];
                          if($light) $cache_object_display = "mode light";
                          else $cache_object_display = $cache_object->getDisplay($lang); 
                          $message .= "<tr><td>$id</td><td>$cache_object_display</td><td>$try</td><td>$found</td><td>$not_found</td></tr>"; 
                    }
                    $message .= "</table><br> count : $table_cache_objects_count<br><hr>";
             }
        
        }
        
        foreach($this->triggerTable as $caller => $call_nb)
        {
                   $message .= "$caller ===> [$call_nb]<br>";
        }
        
        
        return $message;
    }
    
    function triggerCreation($module_code, $table_name, $caller="php")
    {
         if(($this->module_to_audit==$module_code) and ($this->table_to_audit==$table_name))
         {             
             if(!$this->triggerTable[$caller]) $this->triggerTable[$caller] = 1;
             else $this->triggerTable[$caller]++;
             
             $this->trigger_count++;
             
             
             if($this->trigger_count>$this->trigger_max)
             {
                  echo $this->cache_analysis_to_html();
                  die();
             }
         }
    
    }


    
    
}

?>