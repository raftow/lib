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

    public static function dataToXYR($data)
    {
        $minX = 9999;
        $minY = 9999;
        $maxX = -9999;
        $maxY = -9999;
        $result = "";
        foreach($data as $x => $dataX)
        foreach($dataX as $y => $r)
        {
            if($x<$minX) $minX = $x;
            if($y<$minY) $minY = $y;
            if($x>$maxX) $maxX = $x;
            if($y>$maxY) $maxY = $y;
            $result .= "{
                x: $x,
                y: $y,
                r: $r
            },
            ";
        }

        $result = trim($result);
        $result = trim($result, ",");
        return ["[$result]", $minX, $minY, $maxX, $maxY];
    }

    public static function dataToJsXYRValues($data, $labels)
    {
        // die("dataToJsXYRValues : data = ".var_export($data, true));

        $data_js = "";

        $minX = 9999;
        $minY = 9999;
        $maxX = 0;
        $maxY = 0;

        foreach($data as $dataI => $dataItem)
        {
            list($dataItemXYR, $minX0, $minY0, $maxX0, $maxY0) = self::dataToXYR($dataItem);
            if($minX0<$minX) $minX = $minX0;
            if($minY0<$minY) $minY = $minY0;
            if($maxX0>$maxX) $maxX = $maxX0;
            if($maxY0>$maxY) $maxY = $maxY0;
            $label = $labels[$dataI];
            $data_js .= "{
            label: '$label',
            data: $dataItemXYR
          },
          ";
        }

        $data_js = trim($data_js);
        $data_js = trim($data_js, ",");
        return [$data_js, $minX, $minY, $maxX, $maxY];
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
    xValues = $x_js_code;
    yValues = $y_js_code;

    new Chart(\"$idCanvas\", {
    type: \"line\",
    data: {
        labels: xValues,
        datasets: [{
        fill: false,
        lineTension: 0,
        backgroundColor: \"rgba(0,0,255,1.0)\",
        borderColor: \"rgba(0,0,255,0.2)\",
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


    
    
    

    public static function bubbleChartScript($data, $idCanvas, $labels)
    {
        list($data_sets_js, $minX, $minY, $maxX, $maxY) = self::dataToJsXYRValues($data, $labels);

        $return =
            "<script>
        new Chart(
            \"$idCanvas\",
            {
              type: 'bubble',
              options: {
                aspectRatio: 1,
                scales: {
                  x: {
                    max: $maxX,
                  },
                  y: {
                    max: $maxY,                    
                  }
                }
              },

              datasets: $data_sets_js
            });";

        return $return;
    }

    // object number increase chart functions
            
    public static function objectNumberAt($className, $gdate, $dateColumn='', $dateSys='greg')
    {
        if(!$dateColumn) $dateColumn = "created_at";
        if($dateSys=='hijri') $idate = AfwDateHelper::to_hijri($gdate);
        else $idate = $gdate;

        $obj = new $className();
        $obj->where("$dateColumn <= '$idate'");
        return $obj->count();
    }



    // data to use for chart by default 1 year ago
    public static function oniData($className,
        $start = -360,
        $end = 0,
        $step = 30,
        $unit = 'd',
        $index = 'year',
        $valMode = '',
        $options = ['min' => 50, 'max' => 150],
        $dateColumn = '',
        $dateSys = 'greg'
    ) {
        if ($unit == 'd') $unit_value = 1;
        if ($unit == 'm') $unit_value = 30;
        if ($unit == 'y') $unit_value = 360;

        $step_value = $step * $unit_value;
        $start_value = $start * $unit_value;
        $end_value = $end * $unit_value;

        $data = [];

        $allzero = true;
        for ($i = $start; $i <= $end; $i += $step) {
            $gdate = AfwDateHelper::shiftGregDate("", $i * $unit_value);
            $c = static::objectNumberAt($className, $gdate, $dateColumn, $dateSys);
            if ($c) $allzero = false;
            if ($index == 'date') $indx = $gdate;
            elseif ($index == 'year') list($indx,) = explode("-", $gdate);
            elseif ($index == 'month') 
            {
                list($year,$month) = explode("-", $gdate);
                $indx = $year.$month;
            }
            else $indx = $i;
            $data[$indx] = $c;
        }

        if (!$valMode) $valMode = 'randomAlways';

        if (($allzero and ($valMode == 'randomIfAllzero')) or ($valMode == 'randomAlways')) {
            // throw new AfwRuntimeException("will be randomed data = ".var_export($data,true));
            foreach ($data as $dindx => $dc) {
                $data[$dindx] = round(rand($options['min'], $options['max']));
            }
            // throw new AfwRuntimeException("has been randomed data = ".var_export($data,true));
        }

        return $data;
    }

    public static function oniChartScript($className,
        $idCanvas,
        $type,
        $start = -360,
        $end = 0,
        $step = 30,
        $unit = 'd',
        $index = 'year',
        $valMode = '',
        $options = ['min' => 50, 'max' => 150],
        $dateColumn = '',
        $dateSys = 'greg'
    ) {
        $data = static::oniData($className, $start, $end, $step, $unit, $index, $valMode, $options, $dateColumn, $dateSys);
        if ($type == "scatter") return AfwChartHelper::scatterChartScript($data, $idCanvas);
        if ($type == "line") return AfwChartHelper::lineChartScript($data, $idCanvas);
    }
}
