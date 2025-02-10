<?php
// the file generated can be used in JDL Studio : https://jhipster.github.io/jdl-studio/
// now become https://start.jhipster.tech/jdl-studio/


function attributeSyntaxed($fld,$special_lang,$slang_syntax_fld_type_arr,$slang_syntax_fld_type_method_arr)
{
   if($special_lang == "jdl")
   {
       $fld->token_is_arr = array();
       $fld->token_not_is_arr = array();
       $fld->token_is_arr["mandatory"] = "";//"required";  hassen+idriss request nothing required initially 12/3/2017
       $fld->token_not_is_arr["mandatory"] = "";
       $res =  "\t   // " . $fld.",\n";
       $res .= "\t   ";
       $res .= $fld->myJavaName();
       if($slang_syntax_fld_type_method_arr[$special_lang][$fld->getVal("afield_type_id")])
       {
            $fld_type_method = $slang_syntax_fld_type_method_arr[$special_lang][$fld->getVal("afield_type_id")];
            $syn_fld = $fld->$fld_type_method();
            $res .= "   " . $syn_fld .",\n";
            if(AfwStringHelper::stringContain($syn_fld,"لا"))
            {
               $res .= "   Y::fld=" . var_export($fld,true)."->method=".$fld_type_method."(),\n";
               die($res);
            } 
            
       }
       elseif($slang_syntax_fld_type_arr[$special_lang][$fld->getVal("afield_type_id")])
       {
            $fld_type = $slang_syntax_fld_type_arr[$special_lang][$fld->getVal("afield_type_id")];
            $syn_fld = $fld->decodeTpl($fld_type);
            $res .= "   " . $syn_fld .",\n";
            if(AfwStringHelper::stringContain($syn_fld,"لا"))
            {
               $res .= "   X::fld=" . var_export($fld,true)."->method=decodeTpl(".$fld_type."),\n";
               die($res);
            }
       }
       
       
       return $res; 
   }
   else return "attribute syntaxe for language $special_lang not defined";

}

function tableShouldBeIgnored($special_lang,$special_lang_ext,$atb_obj,$slang_syntax_extentions)
{
     $related_module = $atb_obj->related_module;
     if($special_lang == "jdl")
     {
                switch ($special_lang_ext) 
        	{
        		case "jdb"     : 
                                if($atb_obj->_isEnum()) return " this table is enum table";
                                return false;
        		case "enm"  :
                                if (!$atb_obj->_isEnum()) return " this table is not enum table"; 
                                if (!$related_module->getRelationWithTable($atb_obj->getId())) return " this table has no relation with module $related_module";
                                return false;
        		default       : 
                                return false;
        	}
     }
     
     return false;
}

function fieldShouldBeIgnored($special_lang,$special_lang_ext,$afld_obj, $slang_syntax_extentions)
{
   
     if($special_lang == "jdl")
     {
         if($special_lang_ext == "jdh") return true;
         if($special_lang_ext == "enm") return true;
         
         if(($afld_obj->getVal("afield_type_id")==6) or ($afld_obj->getVal("afield_type_id")==15))
         {
              return true;
         }
         
         if(($afld_obj->getVal("afield_type_id")==5) or ($afld_obj->getVal("afield_type_id")==12))
         {
              $good_field = false;
              if($special_lang_ext=="rom") $good_field = $afld_obj->getERType()->isOneToMany();
              if($special_lang_ext=="rmo") $good_field = $afld_obj->getERType()->isManyToOne();
              if($special_lang_ext=="oob") $good_field = $afld_obj->getERType()->isOneToOneBidirectional();
              if($special_lang_ext=="oou") $good_field = $afld_obj->getERType()->isOneToOneUnidirectional();
              if($special_lang_ext=="jdb") $good_field = true;
              
              if($good_field)
              {
                      if($afld_obj->getAnsTable()->_isEnum()) 
                      {
                                return ($slang_syntax_extentions[$special_lang][$special_lang_ext]["relation"]);
                      }          
                      else
                      {
                                if($afld_obj->isInternalRelation())
                                {
                                      return ($slang_syntax_extentions[$special_lang][$special_lang_ext]["entity"]);
                                }
                                else return false; 
                                
                      }
              }
              else return true;
               
         }
     }
     
     return false;
}




$special_lang = "jdl";

$slang_syntax_files[$special_lang] = array("enm","jdb","rom","rmo","oob","oou"); // not needed "jdh",

$slang_syntax_extentions[$special_lang]["jdh"]["entity"] = true;
$slang_syntax_extentions[$special_lang]["jdb"]["entity"] = true;
$slang_syntax_extentions[$special_lang]["enm"]["entity"] = true;
$slang_syntax_extentions[$special_lang]["rom"]["relation"] = true;
$slang_syntax_extentions[$special_lang]["rmo"]["relation"] = true;
$slang_syntax_extentions[$special_lang]["oob"]["relation"] = true;


//////////////////////////////////////////////////////////////////////////
//                 ENTITY  BLOCS
//////////////////////////////////////////////////////////////////////////

/*
$special_lang_ext = "jdh";
$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "// Micro Service description : [module_code] - [id] - [titre_short] - [titre]\n// This code is auto-generated by Hazm framework v2.0\n// Author : Rafik BOUBAKER \n\n\n";
$slang_syntax[$special_lang][$special_lang_ext]["table_header"]  = "// Entity description : [atable_name] - [id] - [titre_short] \n//** entity [class_name]\n";
$slang_syntax[$special_lang][$special_lang_ext]["field_header"]= "";
$slang_syntax[$special_lang][$special_lang_ext]["field_body"]= false;
$slang_syntax[$special_lang][$special_lang_ext]["field_footer"]= "";
$slang_syntax[$special_lang][$special_lang_ext]["table_footer"]= "\n";
$slang_syntax[$special_lang][$special_lang_ext]["module_footer"]= "\n#fill: #ffffff\n#stroke: #000000\n//End of module\nmicroservice * with [module_code]\n#title: [module_code]-[id]";
*/
$special_lang_ext = "jdb";
$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "\n#fill: #ffffff\n#stroke: #000000\n//End of module\nmicroservice * with [module_code]\n#title: [module_code]-[id]\n";
$slang_syntax[$special_lang][$special_lang_ext]["table_header"]  = "entity [class_name] {\n"; //\t   id Long,\n
$slang_syntax[$special_lang][$special_lang_ext]["field_body"]= true;
$slang_syntax[$special_lang][$special_lang_ext]["trim_before_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["table_footer"]= "\n}\n\n\n";  // \t   creationUserId Long,\n\t   creationDate ZonedDateTime,\n\t   updateUserId Long,\n\t   updateDate ZonedDateTime,\n\t   version Integer,\n\t   active Boolean\n

$special_lang_ext = "enm";
$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "\n";
$slang_syntax[$special_lang][$special_lang_ext]["table_header"]  = "enum [class_name] {\n        [titre_en]\n"; //\t   id Long,\n
$slang_syntax[$special_lang][$special_lang_ext]["data_body"]= true;
$slang_syntax[$special_lang][$special_lang_ext]["trim_before_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["table_footer"]= "\n}\n\n\n";  // \t   creationUserId Long,\n\t   creationDate ZonedDateTime,\n\t   updateUserId Long,\n\t   updateDate ZonedDateTime,\n\t   version Integer,\n\t   active Boolean\n


//////////////////////////////////////////////////////////////////////////
//                 RELATION BLOCS
//////////////////////////////////////////////////////////////////////////


/*
1.1.relation_field_before_YTable_header
1.2.relation_YTable_header
1.3.relation_field_after_YTable_header

2.1.relation_field_before_XTable_header
2.2.relation_XTable_header
2.3.relation_field_after_XTable_header

3.1.relation_XTable_body
3.2.relation_YTable_body

4.1.relation_field_before_YTable_footer
4.2.relation_YTable_footer
4.3.relation_field_after_YTable_footer

5.1.relation_field_before_XTable_footer
5.2.relation_XTable_footer
5.3.relation_field_after_XTable_footer
*/

$special_lang_ext = "rom";

$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "// start of OneToMany relations\n";
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_header"]  = "relationship OneToMany {\n";

$slang_syntax[$special_lang][$special_lang_ext]["2.2.relation_XTable_header"]= "     [class_name]{";
$slang_syntax[$special_lang][$special_lang_ext]["2.3.relation_field_after_XTable_header"]= "list";   //[java_name]List not good finalement
$slang_syntax[$special_lang][$special_lang_ext]["4.2.relation_YTable_footer"]  = "[class_name]} to [class_name]";
$slang_syntax[$special_lang][$special_lang_ext]["5.1.relation_field_before_XTable_footer"]= "{[java_name]},\n";

$slang_syntax[$special_lang][$special_lang_ext]["trim_before_relation_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_footer"]  = "\n}\n\n";
$slang_syntax[$special_lang][$special_lang_ext]["module_footer"]= "\n\n//End of OneToMany relations \n\n";


$special_lang_ext = "rmo";

$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "// start of ManyToOne relations\n";
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_header"]  = "relationship ManyToOne {\n";

$slang_syntax[$special_lang][$special_lang_ext]["1.2.relation_YTable_header"]  = "     [class_name]";
$slang_syntax[$special_lang][$special_lang_ext]["1.3.relation_field_after_YTable_header"]= "{[java_name]} to ";
$slang_syntax[$special_lang][$special_lang_ext]["2.2.relation_XTable_header"]= "[class_name],\n";

$slang_syntax[$special_lang][$special_lang_ext]["trim_before_relation_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_footer"]  = "\n}\n\n";
$slang_syntax[$special_lang][$special_lang_ext]["module_footer"]= "\n\n//End of ManyToOne relations \n\n";


$special_lang_ext = "oob";

$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "// start of OneToOne Bidirection relations\n";
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_header"]  = "relationship OneToOne {\n";

$slang_syntax[$special_lang][$special_lang_ext]["1.2.relation_YTable_header"]  = "     [class_name]";
$slang_syntax[$special_lang][$special_lang_ext]["1.3.relation_field_after_YTable_header"]= "{[java_name]} to ";
$slang_syntax[$special_lang][$special_lang_ext]["2.2.relation_XTable_header"]= "[class_name]";
$slang_syntax[$special_lang][$special_lang_ext]["3.2.relation_YTable_body"]  = "{[fcl:class_name]},\n";

$slang_syntax[$special_lang][$special_lang_ext]["trim_before_relation_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_footer"]  = "\n}\n\n";
$slang_syntax[$special_lang][$special_lang_ext]["module_footer"]= "\n\n//End of OneToOne Bidirection relations \n\n";

$special_lang_ext = "oou";

$slang_syntax[$special_lang][$special_lang_ext]["module_header"] = "// start of OneToOne Unidirection relations\n";
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_header"]  = "relationship OneToOne {\n";

$slang_syntax[$special_lang][$special_lang_ext]["1.2.relation_YTable_header"]  = "     [class_name]";
$slang_syntax[$special_lang][$special_lang_ext]["1.3.relation_field_after_YTable_header"]= "{[java_name]} to ";
$slang_syntax[$special_lang][$special_lang_ext]["2.2.relation_XTable_header"]= "[class_name],\n";

$slang_syntax[$special_lang][$special_lang_ext]["trim_before_relation_table_footer"] = array("\n",",");
$slang_syntax[$special_lang][$special_lang_ext]["relation_table_footer"]  = "\n}\n\n";
$slang_syntax[$special_lang][$special_lang_ext]["module_footer"]= "\n\n//End of OneToOne Unidirection relations \n\n";




// 	6	اختيار من قائمة
$slang_syntax_fld_type_method_arr[$special_lang][6]="mfkNotImplemented";
          
// 	5	اختيار من قائمة
$slang_syntax_fld_type_method_arr[$special_lang][5]="getAnswerClass";
    
// 	2	تاريخ
$slang_syntax_fld_type_arr[$special_lang][2]        = "String [is-en:mandatory] maxlength(8)";
    
// 	9	تاريخ ميلادي
$slang_syntax_fld_type_arr[$special_lang][9]        = "ZonedDateTime";
    
// 	12	إختيار من قائمة قصيرة
$slang_syntax_fld_type_method_arr[$special_lang][12]       = "getAnswerClass";
  
// 	1	قيمة عددية متوسطة
$slang_syntax_fld_type_arr[$special_lang][1]        = "Integer";
      
// 	13	قيمة عددية صغيرة
$slang_syntax_fld_type_arr[$special_lang][13]       = "Integer max(100)";
      
// 	14	قيمة عددية كبيرة
$slang_syntax_fld_type_arr[$special_lang][14]       = "Long";
   
// 	3	مبلغ من المال
$slang_syntax_fld_type_arr[$special_lang][3]        = "Float";
      
// 	7	نسبة مائوية
$slang_syntax_fld_type_arr[$special_lang][7]        = "Float max(100)";
      
// 	11	نص طويل
$slang_syntax_fld_type_arr[$special_lang][11]       = "String [is-en:mandatory]";
     
// 	10	نص قصير
$slang_syntax_fld_type_arr[$special_lang][10]       = "String [is-en:mandatory] maxlength([field_size])";
     
// 	8	نعم/لا
$slang_syntax_fld_type_arr[$special_lang][8]        = "Boolean";
        
// 	4	وقت
$slang_syntax_fld_type_arr[$special_lang][4]        = "String [is-en:mandatory] maxlength(8)";
      
// 	15	إختيار متعدد من قائمة قصيرة
$slang_syntax_fld_type_method_arr[$special_lang][15]= "enumMfkNotImplemented";
    
// 	16	قيمة عددية كسرية
$slang_syntax_fld_type_arr[$special_lang][16]       = "Float";
      

?>


