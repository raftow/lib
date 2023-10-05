<?
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
          ['<?=$data_pie_col_key?>', '<?=$data_pie_col_val?>'],
<?
     foreach($data_pie as $data_pie_key => $data_pie_val)
     {
?>
          ['<?=$data_pie_key?>',     <?=$data_pie_val?>],
<?
     }
?>          
        ]);

        var options = {
          fontName: 'title',
          title: '<?=$data_pie_title?>',
          width: <?=$widthChart?>,
          height: <?=$heightChart?>,
          is3D: true,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>
<?
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
          ['<?=$data_bar_col_key?>', '<?=$data_bar_col_val?>', { role: "style" }, { role: 'annotation' }],
<?
     foreach($data_bar as $data_bar_key => $data_bar_val)
     {
?>
          ['<?=$data_bar_key?>',     <?=$data_bar_val?>, "<?=$statsClass::styleBar($data_bar_code,$data_bar_val)?>",'<?=$data_bar_key." ($data_bar_val)"?>'],
<?
     }
?>          
        ]);

        var options = {
          fontName: 'title',
          fontSize: 14,
          title: '<?=$data_bar_title?>',
          width: <?=$widthChart?>,
          height: <?=$heightChart?>,
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
<?
     }
?>