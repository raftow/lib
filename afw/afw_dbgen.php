<?php

$popup = $_GET["popup"];        
$tbl_id = $_GET["tbl"];
$tblsubm = ($_GET["tblsubm"]==1);
$tbl_name = $_GET["tbn"];
$mod_id = $_GET["mod"];
$mod_code = $_GET["mdc"];
$sln = $_GET["sln"];
$nogsh = $_GET["nogsh"];

$MODE_SQL_PROCESS_LOURD = true;
$genere_sln = $sln;
//$real_create = $_GET["rcr"];  dangereux
if($real_create and (!$mod_code)) die("for real mode code generation, specify module code &mdc=xxxxx"); 
if(!$mod_id) die("specify module &mod=xxxxx");
$smod_id = $_GET["smod"];
$show_sql = $_GET["show_sql"];
$show_php = $_GET["show_php"];
$show_res = $_GET["show_res"];
$show_ignored = $_GET["ign"];
$dbstruct_outside = $_GET["struct_out"];

$genere_sql = $_GET["sql"];
$genere_php = $_GET["php"];
$nogen= $_GET["nogen"]; // This is if you want only to show text of php code, not to generate the files in hard disk of server
$genere_trad = $_GET["trad"];
$genere_lookup = $_GET["lookup"];
$genere_dbstruct_only = $_GET["struct"];

if((!$genere_sql) and (!$genere_php) and (!$genere_trad) and (!$genere_lookup) and (!$genere_sln) and (!$genere_dbstruct_only)) 
{
        $genere_sql = true;
        $genere_php = true;
        /*
        $genere_trad = false;
        $genere_lookup = false;
        $genere_dbstruct_only = false;*/
}        


$nb = 0;
$gen = new AfwGenerator();
// die(" new AfwGenerator = " . var_export($gen,true));
$allErrors = "";

if((!$tbl_name) and $tbl_id)  
{
      // require_once("atable.php");
      $tab = new Atable();
      $tab->load($tbl_id);
      $tbl_name =  $tab->getVal("atable_name");
}
require_once (dirname(__FILE__)."/../../external/config.php");
if($genere_sql)
{
        $sqldir = $START_GEN_TREE."sql";
        
        $lang = "ar";
        $allErrors .= "\n-- ->generateFromModule($sqldir,$tbl_id, $mod_id, $smod_id,$tbl_name,false,$tblsubm) -- \n";
        $allErrors .= $gen->generateFromModule($sqldir,$tbl_id, $mod_id, $smod_id,$tbl_name,false,$tblsubm);
        // die("here 7 : gen->generateFromModule($sqldir,$tbl_id, $mod_id, $smod_id,$tbl_name,true,$tblsubm) = [$allErrors]");
        $allErrors .= "\n-- end sql generation -- \n";
        $language = 'sql';
}        

if($genere_lookup)
{
        if(!$real_create) $phpdir = $START_GEN_TREE."php/lookup";
        else $phpdir = $START_TREE."php/lookup";
        if($nogen) $phpdir = "no-gen";
        $allErrors .= $gen->generateLookupPhpFiles($phpdir,$tbl_id, $mod_id, $smod_id);
        $allErrors .= "-- end lookup generation -- \n";        
        $allErrors .= $gen->generateLookupEnumFile($phpdir,$tbl_id, $mod_id, $smod_id);
        $allErrors .= "-- end enum generation -- \n";
        $language = 'php';
}



if($genere_php)
{
        if(!$real_create) $phpdir = $START_GEN_TREE."php";
        else $phpdir = $START_TREE."php";
        if($nogen) $phpdir = "no-gen";
        $allErrors .= $gen->generateModulePhpClasses($phpdir,$tbl_id, $mod_id, $smod_id,$tbl_name, $genere_dbstruct_only, $dbstruct_outside);
        $allErrors .= "-- end php classes generation -- \n";
        
        
        $traddir = $START_GEN_TREE."trad";
        if($nogen) $traddir = "no-gen";

        if($genere_trad)
        {
                $allErrors .= $gen->generateTrad($traddir, "ar", $mod_id, $smod_id, $tbl_name);
                $allErrors .= $gen->generateTrad($traddir, "en", $mod_id, $smod_id, $tbl_name);
                $allErrors .= "-- end php classes translations generation -- \n";
        }        
        $language = 'php';
}

if($genere_trad)
{
        if(!$lang) $lang = "ar";
        if(!$real_create) $traddir = $START_GEN_TREE."trad";
        else $traddir = $START_TREE."trad";
        if($nogen) $traddir = "no-gen";
        $allErrors .= $gen->generateTrad($traddir, "ar", $mod_id, $smod_id, $tbl_name);
        $allErrors .= $gen->generateTrad($traddir, "en", $mod_id, $smod_id, $tbl_name);
        
        $allErrors .= "-- end translations generation -- \n";
        $language = 'php';
}


if($genere_sln)
{
        $sln_dir = $START_GEN_TREE."sln";
        $allErrors .= $gen->generateModuleSpecialLanguage($sln_dir, $mod_id, $sln, $tbl_id,$tbl_name);
        $allErrors .= "// -- end $sln file(s) generation -- \n";
        $language = 'jdl';
}

$source = $allErrors; 

 
//
// Create a GeSHi object
//
if($withgsh)
{ 
   require_once(dirname(__FILE__)."/../lib/geshi11/class.geshi.php");
   $geshi = new GeSHi($source, $language);
   $ss = $geshi->parseCode();
   echo $ss;
}
else
{
   echo "<textarea cols='220' rows='40' dir='ltr' style='text-align:left'>$source</textarea>";
}
//die("je suis ali 3 : ".$source); 
//
// And echo the result!
//



//echo "<textarea dir='ltr' rows='45' cols='220' style='font-family: monospace;white-space: pre;overflow-wrap: normal;overflow-x: scroll;background-color: #fbfddc;color: #2A4C0F;'>$allErrors</textarea>";        

?>

<div class="hzm_panel_link_bar">
         <a class="fleft" href="main.php?Main_Page=afw_mode_display.php&amp;cl=Atable&amp;currmod=pag&amp;id=<?=$tbl_id?>&tbn=<?=$tbl_name?>">
            <span class="yellowbtn submit-btn fleft">atable <?=$tbl_name?> display </span>
         </a>
         <a class="fleft" href="dbgen.php?sql=1&amp;show_sql=1&amp;mod=<?=$mod_id?>&amp;tbl=<?=$tbl_id?>&tbn=<?=$tbl_name?>">
            <span class="greenbtn submit-btn fleft">SQL of <?=$tbl_name?></span>
         </a>
         <a class="fleft" href="dbgen.php?php=1&trad=1&amp;mod=<?=$mod_id?>&amp;tbl=<?=$tbl_id?>&tbn=<?=$tbl_name?>">
            <span class="bluebtn submit-btn fleft">PHP of <?=$tbl_name?></span>
         </a>
        
</div>

