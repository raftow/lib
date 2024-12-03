<?php
class AfwChartHelper
{
    

    private static function dataToJsXYArray($data, $elarge = true)
    {
        $minX = 99999999;
        $minY = 99999999;
        $maxX = 0;
        $maxY = 0;

        $js_code = "";
        foreach ($data as $x => $y) {
            if ($minX > $x) $minX = $x;
            if ($minY > $y) $minY = $y;

            if ($maxX < $x) $maxX = $x;
            if ($maxY < $y) $maxY = $y;
            $js_code .= "{x:$x, y:$y},";
        }
        $js_code = trim($js_code, ",");
        $js_code = "[$js_code]";

        if ($elarge) {
            $nbIntervals = count($data) - 1;
            $tauxInterval = 1.0 / $nbIntervals;
            $dX = abs($maxX - $minX);
            $ddX = round($dX * $tauxInterval);
            $dY = abs($maxY - $minY);
            $ddY = round($dY * $tauxInterval);

            $maxX = $maxX + $ddX;
            $maxY = $maxY + $ddY;

            $minX = $minX - $ddX;
            $minY = $minY - $ddY;
        }

        return [$js_code, $minX, $minY, $maxX, $maxY];
    }

    private static function dataToJsXValuesYValues($data, $elarge = true)
    {
        $minX = 99999999;
        $minY = 99999999;
        $maxX = 0;
        $maxY = 0;

        $x_js_code = "";
        $y_js_code = "";
        foreach ($data as $x => $y) {
            if ($minX > $x) $minX = $x;
            if ($minY > $y) $minY = $y;

            if ($maxX < $x) $maxX = $x;
            if ($maxY < $y) $maxY = $y;
            $x_js_code .= "$x,";
            $y_js_code .= "$y,";
        }
        $x_js_code = trim($x_js_code, ",");
        $y_js_code = trim($y_js_code, ",");
        $x_js_code = "[$x_js_code]";
        $y_js_code = "[$y_js_code]";

        if ($elarge) {
            $nbIntervals = count($data) - 1;
            $tauxInterval = 1.0 / $nbIntervals;
            $dX = abs($maxX - $minX);
            $ddX = round($dX * $tauxInterval);
            $dY = abs($maxY - $minY);
            $ddY = round($dY * $tauxInterval);

            $maxX = $maxX + $ddX;
            $maxY = $maxY + $ddY;

            $minX = $minX - $ddX;
            $minY = $minY - $ddY;
        }

        return [$x_js_code, $y_js_code, $minX, $minY, $maxX, $maxY];
    }


    public static function scatterChartScript($data, $idCanvas)
    {
        list($xy_arr_js, $minX, $minY, $maxX, $maxY) = self::dataToJsXYArray($data);



        $return = "<script>
    var xyValues = $xy_arr_js;

    new Chart(\"$idCanvas\", {
    type: \"scatter\",
    data: {
    datasets: [{
    pointRadius: 4,
    pointBackgroundColor: \"rgb(0,0,255)\",
    data: xyValues
    }]
    },
    options: {
    legend: {display: false},
    scales: {
    xAxes: [{ticks: {min: $minX, max:$maxX}}],
    yAxes: [{ticks: {min: $minY, max:$maxY}}],
    }
    }
    });
    </script>";

        return $return;
    }

    public static function lineChartScript($data, $idCanvas)
    {
        list($x_js_code, $y_js_code, $minX, $minY, $maxX, $maxY) = self::dataToJsXValuesYValues($data);



        $return =
            "<script>
    const xValues = $x_js_code;
    const yValues = $y_js_code;

    new Chart(\"$idCanvas\", {
    type: \"line\",
    data: {
        labels: xValues,
        datasets: [{
        fill: false,
        lineTension: 0,
        backgroundColor: \"rgba(0,0,255,1.0)\",
        borderColor: \"rgba(0,0,255,0.05)\",
        data: yValues
        }]
    },
    options: {
        legend: {display: false},
        scales: {
        yAxes: [{ticks: {min: $minY, max:$maxY}}],
        }
    }
    });
    </script>";

        return $return;
    }
}
