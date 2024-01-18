<?php
      $file_hzm_dir_name = dirname(__FILE__); 
      
      //require_once("$file_hzm_dir_name/../../xlsapp/Classes/PHPExcel.php");
      die("PHPExcel.php is no more supported and not compatible with PHP 8.0 to be migrated to PhpSpreadsheet class see https://phpspreadsheet.readthedocs.io/en/latest/");
      
      Class HzmExcel{
      
          private $filePath;
          private $excelReader;
          private $excelObj;
          private $worksheet;
          private $header;
          private $colIndex;
          private $rowIndex;
          private $tableau;
          private $pkey_name;
          private $header_rows;
          private $map_with_row;
          
          public $myRange;
      
          public function __construct($file_path, $pkey_name="", $header_rows=1,$map_with_row=1, $header=null){
		$this->filePath = $file_path;
                $this->header_rows = $header_rows;
                $this->map_with_row = $map_with_row;
                $this->excelReader = PHPExcel_IOFactory::createReaderForFile($this->filePath);
                $this->excelObj = $this->excelReader->load($this->filePath);
                unset($this->excelReader);
                $this->worksheet = $this->excelObj->getActiveSheet();
                unset($this->excelObj);
                if($this->header_rows and $this->map_with_row)
                {
                      $this->header = array();
                      $maxCol = $this->worksheet->getHighestColumn();
                      $maxRow = $this->worksheet->getHighestRow();
 		      $map_row_data = $this->worksheet->rangeToArray('A'.$this->map_with_row.':'.$maxCol.$this->map_with_row,	$nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef=false);
                      $this->header = $map_row_data[0];
                      // die("this->header : ".var_export($this->header,true)); 
                      $data_start_row = $this->getDataStartRow();
                      $this->tableau = $this->worksheet->rangeToArray('A'.$data_start_row.':'.$maxCol.$maxRow,	$nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef=false);                      
                }
                elseif($header)
                {
                      $this->header = $header;
                      $this->tableau = $this->worksheet->toArray($nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false);
                }
                else
                {
                      // throw exception;
                }
                
                unset($this->worksheet);
                
                $this->colIndex = [];
                
                foreach($this->header as $index => $col_name)
                {
                      $col_name = trim($col_name);
                      $this->header[$index] = $col_name;
                      $this->colIndex[$col_name] = $index; 
                }
                
                $this->pkey_name = $pkey_name;
                if($this->pkey_name)
                {
                        $this->rowIndex = [];
                
                        foreach($this->tableau as $index => $record)
                        {
                              $this->rowIndex[$record[$this->pkey_name]] = $index; 
                        }
                }
                else $this->rowIndex = null;
                
                 
                
                
	  }
          
          public function getDataStartRow()
          {
               return $this->header_rows+1;
          }
          
          public function getHeader()
          {
                return $this->header;
          }
          
          public function getValueFromTableauByRowNum($col_name, $row_num)
          {
                return $this->tableau[$row_num][$this->colIndex[$col_name]];
          }

          public function getValueFromTableauByRowId($col_name, $row_id)
          {
                if($this->rowIndex)
                {
                     $row_num = $this->rowIndex[$row_id];
                     return $this->tableau[$row_num][$this->colIndex[$col_name]];
                }
                else
                {
                     // throw exception;
                     return null;
                }
          }
          
          public function translateMessage($message, $lang = "ar") 
          {
                $file_hzm_dir_name = dirname(__FILE__); 
                
                include "$file_hzm_dir_name/../../../pag/messages_$lang.php";
	
		if($messages[$message]) return $messages[$message];
                else return $message; 
          }
          
          
          public function meetsRequirement($requirementMatrix, $lang="ar")
          {
                 $ok = true;
                 
                 $errors = [];
                 $warnings = [];
                 $infos = [];
                 
                 foreach($requirementMatrix as $key => $keyRequirement)
                 {
                         
                        if (($keyRequirement["mandatory"]) and (!in_array($key, $this->header))) 
                        {
                            // die("this->header : ".var_export($this->header,true)." keyRequirement of $key ==> ".var_export($keyRequirement,true));
                            $key_trad = $keyRequirement["trad_$lang"]; 
                            $ok = false;
                            $errors[] = $this->translateMessage("mandatory column missed",$lang). " : $key ($key_trad)";
                            /*
                            if($keyRequirement["mandatory"])
                            {
                                    die("[$key] is not found in this->header : ".var_export($this->header,true)." keyRequirement of $key ==> ".var_export($keyRequirement,true));
                            } */
                        }
                        
                 }
                 
                 $cols_ignored = [];
                 foreach($this->header  as $index => $col_name)
                 {
                        if(!$requirementMatrix[$col_name])   $cols_ignored[] =  $col_name;
                 }
                 if(count($cols_ignored)>0)
                 {
                      $warnings[] = $this->translateMessage("These found columns will be ignored",$lang)." : <br>\n".implode(",<br>\n",$cols_ignored); // ." debug req=".var_export($requirementMatrix,true)
                 }
                 
                 
                 return array($ok,$errors,$warnings,$infos);
          }
          
          public function getHeaderTrad($trad="")
          {
                 $headerTrad = [];
                 
                 foreach($this->header  as $index => $col_name)
                 {
                        $trad_col_name = $trad[$col_name];
                        $headerTrad[$col_name] = $trad_col_name ? $trad_col_name : $col_name;
                 }
                 
                 return $headerTrad;
          }
          
          public function rowCount()
          {
             return count($this->tableau);
          }
          
          public function getData($row_num_start=-1, $row_num_end=-1, $caller)
          {
               global $callers_arr, $nb_get_xls_data, $nb_data, $objme;
                 
                 $callers_arr[date("Y-m-d H:i:s")] = $caller."($row_num_start, $row_num_end)";
                 
                 if(!$nb_get_xls_data) $nb_get_xls_data = 0;
                 $nb_get_xls_data++;
                 
                 //if($nb_get_xls_data>1) throw new AfwRuntimeException("HzmExcel::getData called twice".var_export($callers_arr,true));
                 
                 
                 if($row_num_end<0) 
                 {
                    $error = "HzmExcel::getData row_num_end = $row_num_end not allowed for the moment";
                    if($objme) throw new AfwRuntimeException($error);
                    else throw_error($error);
                 }
                 
                 
                 $data = [];
                 
                 $nb_data = 0;
                 
                 foreach($this->tableau as $row_num => $record)
                 {
                    if(($row_num>=$row_num_start) and (($row_num<=$row_num_end) or ($row_num_end==-1)))
                    {    
                       if($this->pkey_name)
                             $id = $record[$this->pkey_name];
                       else
                             $id = $row_num; 


                       foreach($record as $col_num => $value)
                       {
                            $col_name = $this->header[$col_num];
                            
                            /*if(strlen($value)>500) 
                            {
                                if($objme) throw new AfwRuntimeException("value too big in ($col_num[$col_name],row:$row_num) ");
                            }*/
                            $data[$id][$col_name] = $value;
                            
                            unset($value);
                       }
                       
                       $nb_data++;
                       if($nb_data>30005)
                       {
                           if($objme) throw new AfwRuntimeException("big excel data size(tab) = ".count($this->tableau)." size(record) = ".count($record). " size(data) = ".count($data)." : data[$id][$col_name] = $value, show rows ($row_num_start, $row_num_end)");
                       }
                       
                       
                    }
                    unset($record);   
                 }
                 
                 return $data;
          }
      
      
      }
?>