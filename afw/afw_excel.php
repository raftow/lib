<?php
$file_dir_name = dirname(__FILE__); 
require "$file_dir_name/../xlsapp/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
class AfwExcel
{
	private $filePath;
	private $excelReader;
	/**
	 * @var Spreadsheet $excelObj
	 */
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


	public static function getExcelFileData($file_path, $row_num_start=-1, $row_num_end=-1, $caller="not-defined")
    {
            $excel = new AfwExcel($file_path);
            $my_data = $excel->getData($row_num_start, $row_num_end, $caller);
            $my_head = $excel->getHeaderTrad();
            
            return [$excel, $my_head, $my_data];
    }

	public function __construct($file_path, $pkey_name = "", $header_rows = 1, $map_with_row = 1, $header = null)
    {
            $this->filePath = $file_path;
            $this->header_rows = $header_rows;
            $this->map_with_row = $map_with_row;
            $this->excelObj = IOFactory::load($this->filePath);
            $this->worksheet = $this->excelObj->getActiveSheet();
            unset($this->excelObj);
            if ($this->header_rows and $this->map_with_row) {
                  $this->header = array();
                  $maxCol = $this->worksheet->getHighestColumn();
                  $maxRow = $this->worksheet->getHighestRow();
                  $map_row_data = $this->worksheet->rangeToArray('A' . $this->map_with_row . ':' . $maxCol . $this->map_with_row,      $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false);
                  $this->header = $map_row_data[0];
                  // die("this->header : ".var_export($this->header,true)); 
                  $data_start_row = $this->getDataStartRow();
                  $this->tableau = $this->worksheet->rangeToArray('A' . $data_start_row . ':' . $maxCol . $maxRow,      $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false);
            } elseif ($header) {
                  $this->header = $header;
                  $this->tableau = $this->worksheet->toArray($nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false);
            } else {
                  // throw exception;
            }

            unset($this->worksheet);

            $this->colIndex = [];

            foreach ($this->header as $index => $col_name) {
                  $col_name = trim($col_name);
                  $this->header[$index] = $col_name;
                  $this->colIndex[$col_name] = $index;
            }

            $this->pkey_name = $pkey_name;
            if ($this->pkey_name) {
                  $this->rowIndex = [];

                  foreach ($this->tableau as $index => $record) {
                        $this->rowIndex[$record[$this->pkey_name]] = $index;
                  }
            } else $this->rowIndex = null;
      }

	public static function genereExcel($header_excel, $data_excel, $xls_page_title = 'نتائج البحث', 
									   $genereFileName="", $addBigHeader=true, $returnLinkOrDownload="link",
	                                   $headerStyle=null, $bigHeaderStyle=null, $dataStyle=null,
									   $data_align="right", $altern_color="EEEEEE", $big_header_color = "002299" 
									   )
	{
		$objme = AfwSession::getUserConnected();

		$_alphabet = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'); 
		/** Error reporting */
		//error_reporting(E_ALL);
		


		// Create new PHPExcel object
		//echo date('H:i:s') , " Create new PHPExcel object" , EOL;
		$objSpreadsheet = new Spreadsheet();

		if($objme) $creator_name = $objme->getDisplay();
		else $creator_name = "visitor";

		// Set document properties
		//echo date('H:i:s') , " Set document properties" , EOL;
		$objSpreadsheet->getProperties()->setCreator($creator_name)
									->setLastModifiedBy($creator_name)
									->setTitle($xls_page_title)
									->setSubject($xls_page_title)
									->setDescription($xls_page_title)
									->setKeywords($xls_page_title)
									->setCategory($xls_page_title);


		// Create a first sheet, representing sales data
		//echo date('H:i:s') , " Add some data" , EOL;

		// no need it is by default :
		// $objSpreadsheet->setActiveSheetIndex(0);
		$sheet = $objSpreadsheet->getActiveSheet();
		$cnt_h = count($header_excel);
		if($cnt_h<=26)
		{
			$last_letter = $_alphabet[$cnt_h];
		}
		elseif($cnt_h<=52)
		{
			$cnt_h = $cnt_h - 26;
			$last_letter = 'A'.$_alphabet[$cnt_h];
		}
		elseif($cnt_h<=78)
		{
			$cnt_h = $cnt_h - 52;
			$last_letter = 'B'.$_alphabet[$cnt_h];
		}
		else
		{
			throw new AfwRuntimeException('too much cols in header_excel='.var_export($header_excel));
		}

		$all_header_rows = 'A1:'.$last_letter.'3';
		if(!$headerStyle) $headerStyle = [
			// (C1) FONT
			"font" => [
			  "bold" => true,
			  "italic" => false,
			  "underline" => false,
			  "strikethrough" => false,
			  "color" => ["rgb" => "FFFFFF"], // argb possible
			  "name" => "Calibri",
			  "size" => 18
			],
		  
			// (C2) ALIGNMENT
			"alignment" => [
			  "horizontal" => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
			  // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
			  // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			  "vertical" => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
			  // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
			  // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
			],
		  
			// (C3) BORDER
			/*
			"borders" => [
			  "top" => [
				"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				"color" => ["argb" => "FFFF0000"]
			  ],
			  "bottom" => [
				"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				"color" => ["argb" => "FF00FF00"]
			  ],
			  "left" => [
				"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
				"color" => ["argb" => "FF0000FF"]
			  ],
			  "right" => [
				"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				"color" => ["argb" => "FF0000FF"]
			  ]
			   ALTERNATIVELY, THIS WILL SET ALL
			  "outline" => [
				"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
				"color" => ["argb" => "FFFF0000"]
			  ]
			],*/
		  
			// (C4) FILL
			"fill" => [
			  // SOLID FILL
			  "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
			  "color" => ["rgb" => "000000"], // argb possible
		  
			  /*  GRADIENT FILL
			  "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			  "rotation" => 90,
			  "startColor" => [
				"argb" => "FF000000",
			  ],
			  "endColor" => [
				"argb" => "FFFFFFFF",
			  ]*/
			]
		];

		if($data_align == "left") $dalign = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT;
		elseif($data_align == "center") $dalign = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER;
		else $dalign = \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT;

		if(!$dataStyle) $dataStyle = [
			// (C1) FONT
			"font" => [
			  "bold" => false,
			  "italic" => false,
			  "underline" => false,
			  "strikethrough" => false,
			  "color" => ["rgb" => "000000"], // argb possible
			  "name" => "Calibri",
			  "size" => 18
			],
		  
			// (C2) ALIGNMENT
			"alignment" => [
			  "horizontal" => $dalign,
			  "vertical" => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
			],
		  
		  
			// (C4) FILL
			"fill" => [
			  // SOLID FILL
			  "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
			  "color" => ["rgb" => "FFFFFF"], // argb possible
		  
			  /*  GRADIENT FILL
			  "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
			  "rotation" => 90,
			  "startColor" => [
				"argb" => "FF000000",
			  ],
			  "endColor" => [
				"argb" => "FFFFFFFF",
			  ]*/
			]
		];

		$dataStyleAltern = $dataStyle;
		$dataStyleAltern["fill"]["color"]["rgb"] = $altern_color;

		$startRow = 1;
		if($addBigHeader)
		{
			$style = $sheet->getStyle($all_header_rows);
			$style->applyFromArray($headerStyle);

			if(!$bigHeaderStyle)
			{
				$bigHeaderStyle = $headerStyle;
				$bigHeaderStyle["fill"]["color"]["rgb"] = $big_header_color;
				$bigHeaderStyle["font"]["size"] = 22;
				
			}

			$styleB1 = $sheet->getStyle('B1');
			$styleB1->applyFromArray($bigHeaderStyle);

			$styleD1 = $sheet->getStyle('D1');
			$styleD1->applyFromArray($bigHeaderStyle);

			$styleE1 = $sheet->getStyle('E1');
			$styleE1->applyFromArray($bigHeaderStyle);


			$sheet->setCellValue('B1', $xls_page_title);
			$sheet->setCellValue('D1', date("Y-m-d"));
			$sheet->setCellValue('E1',AfwDateHelper::currentHijriDate("hdate_long"));
			$startRow = 3;
		}

		$icol = 0;
		foreach($header_excel as $nom_col => $title)
		{
			$col_letter = $_alphabet[count($header_excel)-$icol];    
			$cellpos= $col_letter.''.$startRow;
			$sheet->setCellValue($cellpos, $title);
			$sheet->getColumnDimension($col_letter)->setAutoSize(true);    
			$icol++;
		}

		$irow = $startRow+1;
		$altern = false;
		foreach($data_excel as $id => $row)
		{
				$icol = 0;
				foreach($header_excel as $nom_col => $desc)
				{
					$cellpos= $_alphabet[count($header_excel)-$icol].$irow;
					//$sheet->getStyle($cellpos)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$sheet->setCellValue($cellpos, $row[$nom_col]);
					$icol++;
				}
				
				$curr_row = 'A'.$irow.':'.$last_letter.$irow;
				$rowStyle = $sheet->getStyle($curr_row);
		
				if($altern)
				{
					$rowStyle->applyFromArray($dataStyleAltern);						
				}
				else
				{
					$rowStyle->applyFromArray($dataStyle);						
				}
				$irow++;
				$altern = (!$altern);
		}

		$lastrow = $startRow + count($data_excel); 
		$startDataRow = $startRow + 1;
		$all_data_grid = 'A'.$startDataRow.':'.$last_letter.$lastrow;

		$styleThinBlackBorderOutline = array(
			'borders' => array(
				"outline" => [
					"borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
					"color" => ["argb" => "FF000000"]
				],
				
			),
		);
		$sheet->getStyle($all_data_grid)->applyFromArray($styleThinBlackBorderOutline);

		$grid_header_row = 'A'.$startRow.':'.$last_letter.$startRow;

		$sheet->getStyle($grid_header_row)->applyFromArray(
				array(
					'font'    => array(
						'bold'      => true
					),
					'alignment' => array(
						'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
					),
					'borders' => array(
						'top'     => array(
							'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						)
					),
					'fill' => array(
						'type'       => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
						'rotation'   => 90,
						'startcolor' => array(
							'argb' => 'FFA0A0A0'
						),
						'endcolor'   => array(
							'argb' => 'FFFFFFFF'
						)
					)
				)
		);

		$sheet->getStyle('A3')->applyFromArray(
				array(
					'alignment' => array(
						'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
					),
					'borders' => array(
						'left'     => array(
							'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						)
					)
				)
		);

		$sheet->getStyle('B3')->applyFromArray(
				array(
					'alignment' => array(
						'horizontal' => PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
					)
				)
		);

		$sheet->getStyle('E3')->applyFromArray(
				array(
					'borders' => array(
						'right'     => array(
							'style' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
						)
					)
				)
		);

		// Unprotect a cell
		//$sheet->getStyle('B1')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
		/*
		// Add a drawing to the worksheet
		echo date('H:i:s') , " Add a drawing to the worksheet" , EOL;
		$objDrawing = new PHPExcel_Worksheet_Drawing();
		$objDrawing->setName('PHPExcel logo');
		$objDrawing->setDescription('PHPExcel logo');
		$objDrawing->setPath('./images/phpexcel_logo.gif');
		$objDrawing->setHeight(36);
		$objDrawing->setCoordinates('D24');
		$objDrawing->setOffsetX(10);
		$objDrawing->setWorksheet($sheet);
		*/

		// Set page orientation and size
		// $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
		// $sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

		// Rename first worksheet
		$sheet->setTitle($xls_page_title);

		$writer = new Xlsx($objSpreadsheet);

		

		if($genereFileName) 
		{
			$upld_path = AfwSession::config("uploads_http_path","");
			$uploads_root_path = AfwSession::config("uploads_root_path","");
			$exports_file_name = $uploads_root_path."exports/$genereFileName";
			$writer->save($exports_file_name.'.xlsx');

			if($returnLinkOrDownload == "link") $link = "<center><div class='card'><br>تم تصدير ملف اكسل يحتوي على نتائج البحث  <br>
			<a href='$upld_path/exports/$genereFileName.xlsx' class='btn btn-large btn-primary downloadlink'>تحميل الملف</a>		
			</div></center><br><br>";

			
		}
		
		
		if($returnLinkOrDownload == "download")
		{
			header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
			header("Content-Disposition: attachment;filename=\"2-download.xlsx\"");
			header("Cache-Control: max-age=0");
			header("Expires: Fri, 11 Nov 2011 11:11:11 GMT");
			header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
			header("Cache-Control: cache, must-revalidate");
			header("Pragma: public");
			$writer->save("php://output");
			return;
		}
		elseif($returnLinkOrDownload == "link") return $link;
		
	}

	public static function readExcel($file_path, $row_num_start=-1, $row_num_end=-1, $caller="not-defined")
    {
			$spreadsheet = 
            $excel = new HzmExcel($file_path);
            $my_data = $excel->getData($row_num_start, $row_num_end, $caller);
            $my_head = $excel->getHeaderTrad();
            
            return [$excel, $my_head, $my_data];
    }


	public function getDataStartRow()
      {
            return $this->header_rows + 1;
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
            if ($this->rowIndex) {
                  $row_num = $this->rowIndex[$row_id];
                  return $this->tableau[$row_num][$this->colIndex[$col_name]];
            } else {
                  // throw exception;
                  return null;
            }
      }

      public function translateMessage($message, $lang = "ar")
      {
            $file_hzm_dir_name = dirname(__FILE__);

            include "$file_hzm_dir_name/../../../lib/messages_$lang.php";

            if ($messages[$message]) return $messages[$message];
            else return $message;
      }


      public function meetsRequirement($requirementMatrix, $lang = "ar")
      {
            $ok = true;

            $errors = [];
            $warnings = [];
            $infos = [];

            foreach ($requirementMatrix as $key => $keyRequirement) {

                  if (($keyRequirement["mandatory"]) and (!in_array($key, $this->header))) {
                        // die("this->header : ".var_export($this->header,true)." keyRequirement of $key ==> ".var_export($keyRequirement,true));
                        $key_trad = $keyRequirement["trad_$lang"];
                        $ok = false;
                        $errors[] = $this->translateMessage("mandatory column missed", $lang) . " : $key ($key_trad)";
                        /*
                            if($keyRequirement["mandatory"])
                            {
                                    die("[$key] is not found in this->header : ".var_export($this->header,true)." keyRequirement of $key ==> ".var_export($keyRequirement,true));
                            } */
                  }
            }

            $cols_ignored = [];
            foreach ($this->header  as $index => $col_name) {
                  if (!$requirementMatrix[$col_name])   $cols_ignored[] =  $col_name;
            }
            if (count($cols_ignored) > 0) {
                  $warnings[] = $this->translateMessage("These found columns will be ignored", $lang) . " : <br>\n" . implode(",<br>\n", $cols_ignored); // ." debug req=".var_export($requirementMatrix,true)
            }


            return array($ok, $errors, $warnings, $infos);
      }

      public function getHeaderTrad($trad = [])
      {
            $headerTrad = [];

            foreach ($this->header  as $index => $col_name) {
                  $trad_col_name = $trad[$col_name];
                  $headerTrad[$col_name] = $trad_col_name ? $trad_col_name : $col_name;
            }

            return $headerTrad;
      }

      public function rowCount()
      {
            return count($this->tableau);
      }

      public function getData($row_num_start = -1, $row_num_end = -1, $caller)
      {
            global $callers_arr, $nb_get_xls_data, $nb_data, $objme;

            $callers_arr[date("Y-m-d H:i:s")] = $caller . "($row_num_start, $row_num_end)";

            if (!$nb_get_xls_data) $nb_get_xls_data = 0;
            $nb_get_xls_data++;

            //if($nb_get_xls_data>1) throw new AfwRuntimeException("HzmExcel::getData called twice".var_export($callers_arr,true));


            if ($row_num_end < 0) {
                  $error = "HzmExcel::getData row_num_end = $row_num_end not allowed for the moment";
                  if ($objme) throw new AfwRuntimeException($error);
                  else throw new AfwRuntimeException($error);
            }


            $data = [];

            $nb_data = 0;

            foreach ($this->tableau as $row_num => $record) {
                  if (($row_num >= $row_num_start) and (($row_num <= $row_num_end) or ($row_num_end == -1))) {
                        if ($this->pkey_name)
                              $id = $record[$this->pkey_name];
                        else
                              $id = $row_num;


                        foreach ($record as $col_num => $value) {
                              $col_name = $this->header[$col_num];

                              /*if(strlen($value)>500) 
                            {
                                if($objme) throw new AfwRuntimeException("value too big in ($col_num[$col_name],row:$row_num) ");
                            }*/
                              $data[$id][$col_name] = $value;

                              unset($value);
                        }

                        $nb_data++;
                        if ($nb_data > 30005) {
                              if ($objme) throw new AfwRuntimeException("big excel data size(tab) = " . count($this->tableau) . " size(record) = " . count($record) . " size(data) = " . count($data) . " : show rows ($row_num_start, $row_num_end)");
                        }
                  }
                  unset($record);
            }

            return $data;
      }

	
}
//$callEndTime = microtime(true);
//$callTime = $callEndTime - $callStartTime;

// Add some data to the second sheet, resembling some different data types
/*
$objSpreadsheet->setActiveSheetIndex(1);
$sheet->setCellValue('A1', 'Terms and conditions');
$sheet->setCellValue('A3', $sLloremIpsum);
$sheet->setCellValue('A4', $sLloremIpsum);
$sheet->setCellValue('A5', $sLloremIpsum);
$sheet->setCellValue('A6', $sLloremIpsum);

// Set the worksheet tab color
$sheet->getTabColor()->setARGB('FF0094FF');;

// Set alignments
$sheet->getStyle('A3:A6')->getAlignment()->setWrapText(true);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(80);

// Set fonts
$sheet->getStyle('A1')->getFont()->setName('Candara');
$sheet->getStyle('A1')->getFont()->setSize(20);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getFont()->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);

$sheet->getStyle('A3:A6')->getFont()->setSize(8);

$objDrawing = new PHPExcel_Worksheet_Drawing();
$objDrawing->setName('Terms and conditions');
$objDrawing->setDescription('Terms and conditions');
$objDrawing->setPath('./images/termsconditions.jpg');
$objDrawing->setCoordinates('B14');
$objDrawing->setWorksheet($sheet);

// Set page orientation and size
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Rename second worksheet
$sheet->setTitle('Terms and conditions');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objSpreadsheet->setActiveSheetIndex(0);
*/
