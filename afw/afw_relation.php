<?php

// old require of afw_root 

class AFWRelation extends AFWRoot {

     private $myParentObject = null;
     private $myObject = null;
     private $items = null;

     private $answer_module = "";
     private $answer_table = "";
     private $answer_item = "";
     private $id_item = 0;
     private $answer_where = "";
     private $answer_limit = "";
     private $answer_orderby = "";
     
     public function __construct($module, $table, $item, $id_item, $where="",$myParentObject=null) 
     {
             $this->answer_module = $module;
             $this->answer_table = $table;
             $this->answer_item = $item;
             $this->answer_where = $where;
             $this->id_item = $id_item;
             $this->myParentObject = $myParentObject;
     }
     
     
     public function getObject()
     {
         if(!$this->myObject) 
         {
                 $className = self::tableToClass($this->answer_table);
                 $this->myObject = new $className();
         }
     
         return $this->myObject;
     
     }
     
     public function resetWhere($where="")
     {
         $this->answer_where = $where;
         
         return $this;
     }
     
     public function orderBy($orderby="")
     {
         $this->answer_orderby = $orderby;
         
         return $this;
     }
     
     public function limit($limit="")
     {
         $this->answer_limit = $limit;
         
         return $this;
     }
     
     public function prepare()
     {
          $this->getObject();
          if($this->answer_item) $this->myObject->select($this->answer_item, $this->id_item);
          if($this->answer_where) 
          {
               if($this->myParentObject) $tokenized_answer_where = $this->myParentObject->decodeText($this->answer_where);
               $this->myObject->where($tokenized_answer_where);
          }
          /*
          if(AfwStringHelper::stringContain($this->answer_where,"class_name = _utf8§class_name§")) 
          {
               die("ans-where = " . $this->answer_where . " tokenized-ans-where=$tokenized_answer_where this->myParentObject=".var_export($this->myParentObject,true));
          }*/
     }
     
     
     public function count()
     {
          $this->prepare();
          
          
          return $this->myObject->count();
     }

     
     
     public function getList($sql_only=false)
     {
          $this->prepare();
          
          
          if($sql_only) return $this->myObject->getSQLMany($this->myObject->getPKField(), $this->answer_limit, $this->answer_orderby, $optim=true);
          else return $this->myObject->loadMany($this->answer_limit, $this->answer_orderby);
     }

     public function getSQLMany()
     {
          return $this->myObject->getSQLMany('',$this->answer_limit, $this->answer_orderby);
     }

     public function getSQLFirst()
     {
          return $this->myObject->getSQLMany('',1, $this->answer_orderby);
     }
     
     public function getFirst()
     {
          $this->prepare();
          
          $sql = $this->myObject->getSQLMany('',1, $this->answer_orderby);
          $list = $this->myObject->loadMany(1, $this->answer_orderby); // $this->answer_limit
          if(count($list)==0) return array(null, $sql);
          reset($list);
          $return = current($list);

          return array($return, $sql);
     }


     public function recupData($attributes_arr, $distinct=false)
     {
          $this->prepare();
          return AfwLoadHelper::loadData($this->myObject, $attributes_arr, $limit = '', $order_by = '', $distinct);
     }

     public function getArray($attribute, $distinct=false)
     {
          $this->prepare();
          
          $array_result = array();
          $array_done = array();

          $listCols = $this->myObject->loadCol($attribute, $distinct);
          foreach($listCols as $colValue)
          {
               if((!$distinct) or (!$array_done[$colValue]))
               {
                    $array_result[] = $colValue;
                    $array_done[$colValue] = true;   
               }

          }
          /* This code is not optimized, optimization done above
          $listObjects = $this->myObject->loadMany($this->answer_limit, $this->answer_orderby);
          foreach($listObjects as $itemObject)
          {
               $new_val = $itemObject->getVal($attribute);
               if((!$distinct) or (!$array_done[$new_val]))
               {
                    $array_result[] = $new_val;
                    $array_done[$new_val] = true;   
               }

          }*/

          return $array_result;
     }
     
     public function func($function, $group_by = "")
     {
          $this->prepare();
          
          
          return $this->myObject->func($function, $group_by);
     }

     public function getIds()
     {
           return $this->getArray("id");
     }
     
     
     

}

?>