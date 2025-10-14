<?php
    $MODULE  = $_REQUEST["m"];
    $file_dir_name = dirname(__FILE__)."/../..";
    include(dirname(__FILE__)."/../afw_start.php");
    $stc  = $_REQUEST["stc"];
    $class  = $_REQUEST["cl"];
    $lang  = $_REQUEST["lang"];
    $subcase  = $_REQUEST["subcase"];
    $case  = $_REQUEST["case"];
    
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
    
    foreach ($stat_trad as $col => $info) 
    {
        foreach ($stats_data_arr as $stats_curr_row => $stats_data_item) {
            if($stats_data_item[$colFilter]==$colFilterValue)
            {
                if(is_numeric($stats_data_item[$col])) $data_pie[$info] = $stats_data_item[$col];
            }
        }
    }    
?>
<!DOCTYPE html>
<html>
<script src="https://www.gstatic.com/charts/loader.js"></script>
<link href="../lib/css/def_ar_front.css?crst=<?php echo md5(date("His"))?>" rel="stylesheet" type="text/css">
<link href='../crm/css/module.css?crst=<?php echo md5(date("His"))?>' rel='stylesheet' type='text/css'>
<body>
        <?php
            // echo var_export($sub_title_arr, true);
            subcase
        ?>
        <div id="myChart" style="width:100%; max-width:600px; height:500px;"></div>
        <script>
        google.charts.load('current', {'packages':['corechart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {

            // Set Data
            const data = google.visualization.arrayToDataTable([
                ['Contry', 'Mhl'],
<?php     
        $values = "";
        foreach($data_pie as $info => $vali)           
        {
            $values .= "['$info',$vali],\n";
        }

        $values = trim($values);
        $values = trim($values, ",");

        echo $values;
                
?>                
            ]);

            // Set Options
            const options = {
                title:'<?php echo $stats_title ?>',
                fontName:'title',
                fontSize:18,
                is3D:true,
                slices: {0: {color: '#008800'}, 1: {color: '#53a5e1'}, 2: {color: 'rgb(155, 153, 19)'}, 3: {color: 'rgb(184, 123, 11)'}, 4: {color: 'rgb(230, 72, 9)'}, 5: {color: '#000000'}}
            };

            // Draw
            const chart = new google.visualization.PieChart(document.getElementById('myChart'));
            chart.draw(data, options);

        }
</script>
</body>
</html>