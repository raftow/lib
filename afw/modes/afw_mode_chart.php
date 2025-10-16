<?php
    $MODULE  = $_REQUEST["m"];
    $file_dir_name = dirname(__FILE__)."/../..";
    include(dirname(__FILE__)."/../afw_start.php");
    $stc  = $_REQUEST["stc"];
    $class  = $_REQUEST["cl"];
    $lang  = $_REQUEST["lang"];
    $subcase  = $_REQUEST["subcase"];
    $case  = $_REQUEST["case"];
    $subcase  = $_REQUEST["subcase"];
    
    if(!$lang) $lang = "ar";
    $myObj = new $class();
    $stats_title = $myObj->translate('stats.' . $stc, $lang);
    $stats_title = $myObj->decodeText($stats_title, $prefix = '', $add_cotes = false, $sepBefore = '[', $sepAfter = ']');


    $colFilter  = $_REQUEST["f"];
    $colFilterValue  = $_REQUEST["v"];
    $stats_config = $class::$STATS_CONFIG[$stc];
    $stats_data_from    = $stats_config['STATS_DATA_FROM'];
    
    $stat_trad              = [];
    if($stats_data_from)
    {
        $params_list = $stats_config["PARAMS"];
        if(!$params_list) $params_list = [];
        $params_arr = [];
        foreach($params_list as $param_name)
        {
            if(isset($_REQUEST[$param_name])) $params_arr[$param_name] = $_REQUEST[$param_name];
        }

        $dataFromClass = $stats_data_from['class'];
        $dataFromMethod = $stats_data_from['method'];

        list($stats_data_arr, $stat_trad, $sub_title_arr) = $dataFromClass::$dataFromMethod($params_arr);

    }
    
    $data_pie_arr = [];

    foreach ($stat_trad as $col => $info) 
    {
        foreach ($stats_data_arr as $stats_curr_row => $stats_data_item) {
            if(($stats_data_item[$colFilter]==$colFilterValue) or (!$colFilterValue) or ($colFilterValue=="all"))
            {
                if(is_numeric($stats_data_item[$col])) $data_pie_arr[$stats_data_item[$colFilter]][$info] = $stats_data_item[$col];
            }
        }
    }    
    if($subcase)
    {
        $subcases_arr = [];
        $subcases_arr[$subcase] = $sub_title_arr[$subcase];
    }
    else
    {
        $subcases_arr = $sub_title_arr;
    }
    /*
    echo "<br> sub_title_arr = ".var_export($sub_title_arr, true);
    echo "<br> stat_trad = ".var_export($stat_trad, true);
    echo "<br> colFilter = $colFilter";
    echo "<br> colFilterValue = $colFilterValue";
    die("<br> data_pie_arr = ".var_export($data_pie_arr, true));*/
?>
<!DOCTYPE html>
<html>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<link href="../lib/css/def_ar_front.css?crst=<?php echo md5(date("His"))?>" rel="stylesheet" type="text/css">
<link href='../crm/css/module.css?crst=<?php echo md5(date("His"))?>' rel='stylesheet' type='text/css'>
<body>
        
        <h1 class='stats-title'><?php $stats_title ?></h1>
        <?php
            foreach($subcases_arr as $subcase => $sub_title)
            {
?>
            <div id="myChart<?php echo $subcase ?>" style="width:40%;margin-right:5%;margin-left:5%;max-width:600px; height:500px;float: right;"></div>
<?php
            }
            
        ?>
        
        <script>
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            
<?php 
            // Draw
            foreach($subcases_arr as $subcase => $sub_title)
            {
?>  
            // Set Data
            const data_arr<?php echo $subcase ?> = google.visualization.arrayToDataTable([
                ['LabelName', 'ValueName'],
<?php     
                    $values = "";
                    foreach($data_pie_arr[$subcase] as $info => $vali)           
                    {
                        $values .= "['$info',$vali],\n";
                    }

                    $values = trim($values);
                    $values = trim($values, ",");

                    echo $values;
                
?>                
            ]);
<?php 
            }

            // die("<br> subcases_arr = ".var_export($subcases_arr, true));
?> 
            // Set Options
            var options = {
                title:'xxx',
                fontName:'title',
                fontSize:18,
                is3D:true,
                slices: {0: {color: '#008800'}, 1: {color: '#53a5e1'}, 2: {color: 'rgb(155, 153, 19)'}, 3: {color: '#eb740e'}, 4: {color: 'rgb(230, 72, 9)'}, 5: {color: '#000000'}}
            };

            var chart = null;
            
<?php 
            // Draw
            foreach($subcases_arr as $subcase => $sub_title)
            {
?>                    
            options.title = '<?php echo $sub_title ?>';
            chart = new google.visualization.PieChart(document.getElementById('myChart<?php echo $subcase ?>'));
            chart.draw(data_arr<?php echo $subcase ?>, options);
<?php 
            }
?>                    

        }
</script>
</body>
</html>