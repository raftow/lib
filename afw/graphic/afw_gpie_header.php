<?php
/**
 * @var array $data
 */
    foreach($data as $vard => $vald) $$vard = $vald;
      if(!$widthChart) $widthChart = 800; // '100%';//
     if(!$heightChart) $heightChart = 800; // '100%';//
     
     if(is_array($data_pie) and (count($data_pie)>0))
     {
?>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['xx<?php echo $data_pie_col_key?>', 'yy<?php echo $data_pie_col_val?>'],
<?php
     // echo "data_pie=".var_export($data_pie,true); 
     foreach($data_pie as $data_pie_key => $data_pie_val)
     {
?>
          ['<?php echo $data_pie_key?>',     <?php echo $data_pie_val?>],
<?php
     }
?>          
        ]);

        var options = {
          fontName: 'title',
          title: '<?php echo $data_pie_title?>',
          width: <?php echo $widthChart?>,
          height: <?php echo $heightChart?>,
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
<?php
     }
     
     if(is_array($data_bar) and (count($data_bar)>0))
     {
?>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
<?php
     if($data_bar_col_key)
     {
?>
          ['<?php echo $data_bar_col_key?>', '<?php echo $data_bar_col_val?>', { role: "style" }, { role: 'annotation' }],
<?php
     }
     foreach($data_bar as $data_bar_key => $data_bar_val)
     {
?>
          ['<?php echo $data_bar_key?>',     <?php echo $data_bar_val?>, "<?php echo $statsClass::styleBar($data_bar_code,$data_bar_val)?>",'<?php echo $data_bar_key." ($data_bar_val)"?>'],
<?php
     }
?>          
        ]);

        var options = {
          fontName: 'title',
          fontSize: 14,
          title: '<?php echo $data_bar_title?>',
          width: <?php echo $widthChart?>,
          height: <?php echo $heightChart?>,
          bar: {groupWidth: "70%"},
          legend: { position: "none" },
          annotations: {
            textStyle: {
              fontName: 'title',
              fontSize: 14,
              bold: false,
              italic: false,
              textColor: 'black'
            }
          }
        };

        var chart = new google.visualization.BarChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
<?php
     }
?>