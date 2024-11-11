<?php
$SHOW_ARGS = true;
$SHOW_ALL_ARGS = false;

if(!isset($relative_path)) $relative_path = "./";

if((!function_exists("myAfwErrorHandler")) and (!function_exists("myAfwExceptionHandler")))
{
        function myAfwErrorHandler($errno, $errstr, $errfile, $errline) 
        {
                
                echo "<b>Custom error:</b> [$errno] $errstr<br>";
                echo " Error on line $errline in $errfile<br>";                
        }

        function myAfwExceptionHandler($ex)
        {
                /*
                if($ex instanceof TypeError)
                {
                        $ex2 = new RuntimeException($ex->getMessage());
                }

                if($ex instanceof Exception)
                {
                        
                }*/
                
                ob_start();
                dump_exception( $ex );
                $dump = ob_get_clean();
                echo $dump;
                exit;
                
        }

        function cleanLineCode($line)
        {
                $line = str_replace("\n", "", $line);
                $line = strip_tags($line);

                return $line;
        }

        function showCodeLines($file, $line, $lines=null, $LINES_BEFORE=6, $LINES_AFTER=6)
        {
            if(!$lines) 
            {
                if (file_exists($file))
                {
                        $lines = file($file);
                }
            }
            if ($lines)
            {
                echo "<code>$file</code>
                <ul class=\"code\">";
                for($i = $line - $LINES_BEFORE; $i < $line + $LINES_AFTER; $i ++ ) 
                {
                        if ($i > 0 && $i < count($lines))
                        {
                            $line_num = $i+1;
                            if ( $i == $line-1 ) $class_line_error = "line";
                            else $class_line_error = "";
                            $line_of_code = cleanLineCode($lines[$i]);
                            echo "<li class='$class_line_error'><div class='linenum'>$line_num</div>$line_of_code</li>";
                                
                        }
                }
                echo "</ul>";
            }
        }

        function dump_exception($ex)
        {
            global $relative_path, $SHOW_ARGS, $SHOW_ALL_ARGS;
                $file = $ex->getFile();
                $line = $ex->getLine();

                if ( file_exists( $file ) )
                {
                        $lines = file( $file );
                }
                $crst = md5("crst" . date("YmdHis"));
    
?></div>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/jquery-ui-1.11.4.css">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/font-awesome.min-4.3.css">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/font-awesome.min.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tajawal%3A400%2C700&ver=5.5.1">

<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/front-application.css">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/hzm-v001.css">

<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/front_screen_pc.css?crst=<?php echo $crst ?>">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/front_tablet.css?crst=<?php echo $crst ?>">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/front_mobile.css?crst=<?php echo $crst ?>">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/front_mobile_thin.css?crst=<?php echo $crst ?>">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/material-design-iconic-font.min.css">
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/bootstrap/bootstrap-v3.min.css">
<link href="<?php echo $relative_path;?>../lib/attention/attention.css" rel="stylesheet">




<script src="<?php echo $relative_path;?>../lib/js/jquery-1.12.0.min.js"></script>
<script src="<?php echo $relative_path;?>../lib/bootstrap/bootstrap-v3.min.js"></script>

<?php
  $my_font = "front";
  $my_theme = "simple";
  $lang = "ar";
?>
<script src="<?php echo $relative_path;?>../lib/js/jquery-ui-1.11.4.js"></script>
<script src="<?php echo $relative_path;?>../lib/attention/attention.js"></script>
<script src="<?php echo $relative_path;?>../lib/attention/attention_functions.js"></script>
<link rel="stylesheet" href="../lib/hijra/jquery.calendars.picker.css"/>
<script src="<?php echo $relative_path;?>../lib/hijra/jquery.calendars.js"></script>
<script src="<?php echo $relative_path;?>../lib/hijra/jquery.calendars.plus.js"></script>
<script src="<?php echo $relative_path;?>../lib/hijra/jquery.calendars.picker.js"></script>
<script src="<?php echo $relative_path;?>../lib/hijra/jquery.calendars.ummalqura.js"></script>

<!-- <msdropdown> -->
<link rel="stylesheet" type="text/css" href="../lib/msdropdown/css/msdropdown/dd.css" />
<script src="<?php echo $relative_path;?>../lib/msdropdown/js/msdropdown/jquery.dd.js"></script>
<!-- </msdropdown> -->

<script src="<?php echo $relative_path;?>js/schedule-viewmodel.js"></script>
<script src="<?php echo $relative_path;?>js/module.js"></script>
        
<link href="<?php echo $relative_path;?>../lib/css/autocomplete.css" rel="stylesheet" type="text/css">
<link href="<?php echo $relative_path;?>../lib/css/responsive.css" rel="stylesheet" type="text/css">
<meta http-equiv="X-UA-Compatible" content="IE=9; IE=8; IE=EDGE">

<link href="pic/logo.png" rel="shortcut icon">

<title>Momken Library</title>
<link href="<?php echo $relative_path;?>../lib/css/def_<?=$lang?>_<?=$my_font?>.css" rel="stylesheet" type="text/css">
<link href="<?php echo $relative_path;?>../lib/css/<?=$my_theme?>/style_common.css" rel="stylesheet" type="text/css">
<link href="<?php echo $relative_path;?>../lib/css/<?=$my_theme?>/style_<?=$lang?>.css" rel="stylesheet" type="text/css">
</head>
<body>
<div class='afwerr'>
        <h1>Uncaught <?= get_class( $ex ); ?></h1>
        <h3><?= $ex->getMessage(); ?></h3>
        <p>
            An uncaught <?= get_class( $ex ); ?> was thrown on line <?= $line; ?> of file <?= basename( $file ); ?> that prevented further execution of this request.
        </p>
        <h2>Where it happened:</h2>
        
        <?php showCodeLines($file, $line, $lines); ?>
        <? if ( is_array( $ex->getTrace() ) ) : ?>
        <h2>Stack trace:</h2>
            <table class="trace">
                <thead>
                    <tr>
                        <td>File</td>
                        <td>Line</td>
                        <td>Class</td>
                        <td>Function</td>
                        <td>Arguments</td>
                    </tr>
                </thead>
                <tbody>
                <? foreach ( $ex->getTrace() as $i => $trace ) : ?>
                    <tr class="<?= $i % 2 == 0 ? 'even' : 'odd'; ?>">
                        <td><?= isset($trace[ 'file' ]) ? basename($trace[ 'file' ]) : ''; ?></td>
                        <td><?= isset($trace[ 'line' ]) ? $trace[ 'line' ] : ''; ?></td>
                        <td><?= isset($trace[ 'class' ]) ? $trace[ 'class' ] : ''; ?></td>
                        <td><?= isset($trace[ 'function' ]) ? $trace[ 'function' ] : ''; ?></td>
                        <td>
                            <? if(isset($SHOW_ARGS) and $SHOW_ARGS) : ?>
                                <? if(isset($trace['args']) and $trace['args'] ) : ?>
                                    <? foreach ( $trace['args'] as $i => $arg ) : ?>
                                        <? if(!is_object($arg) and !is_array($arg)) : ?> 
                                            <span title=""><?= gettype($arg); ?> : <?= var_export( $arg, true ); ?></span>
                                            <?= $i < count( $trace['args'] ) -1 ? ',' : ''; ?> 
                                        <? else : ?>
                                            Array or Object
                                        <? endif; ?>
                                    <? endforeach; ?>
                                <? else : ?>
                                    NULL
                                <? endif; ?>
                            <? else : ?>    
                                DISABLED-0
                            <? endif; ?>
                        </td>
                    </tr>                    
                    <? if(isset($SHOW_ALL_ARGS) and $SHOW_ALL_ARGS and $trace['args']) { ?>
                    <tr class="<?= $i % 2 == 0 ? 'even' : 'odd'; ?>">
                        <td class="args" colspan="5">
                            <?php echo var_export($trace['args'],true) ?>
                        </td>
                    </tr>                        
                    <? } ?>    
                    <tr class="<?= $i % 2 == 0 ? 'even' : 'odd'; ?>">
                        <td colspan="5">
                           <div class='php code zone'> <?php showCodeLines($trace['file'], $trace['line']);   ?></div>
                        </td>
                    </tr>
                <? endforeach;?>
                </tbody>
            </table>
        <? else : ?>
            <pre><?= $ex->getTraceAsString(); ?></pre>
        <? endif; ?>
    <?php
        if(class_exists("AfwSession")) echo AfwSession::getLog();
        /*
        
        
        if($_POST) 
        {
                echo "<table dir='ltr' class=\"display dataTable\">\n";
                $odd = "odd";
                foreach($_POST as $att => $att_val)
                {
                        echo "<tr calss='$odd'><td>posted <b>$att : </b></td><td>$att_val</td></tr>\n"; 
                        if($odd=="even") $odd = "odd";
                        else $odd = "even";
                }
                echo "</table>\n<hr>\n";
        }*/
    ?>        
        </body>
</html>
<?php
}
}
//set_error_handler("myAfwErrorHandler",E_ERROR | E_PARSE | E_RECOVERABLE_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR);
/*
    protected function afwError($error_title, $call_method = '')
    {
        global $_POST, $out_scr, $lang, $the_last_sql;
        die($error_title);
        $file_dir_name = dirname(__FILE__);
        // il faut un header special qui ne plante jamais ان شاء الله
        include "$file_dir_name/../lib/hzm/web/hzm_min_header.php";

        $message .= 'object error :';
        $message .=
            '<br> <b>TableClass :</b> ' . AfwStringHelper::tableToClass(static::$TABLE);
        $message .= '<br> <b>ID :</b> ' . $this->getId();
        $message .=
            '<br> <b>LAST_ATTRIBUTE :</b> ' . $this->debugg_last_attribute;

        // -- rafik : danger : no call to any overrideble method here
        // -- to avoid infinite loop of this error method call
        //$message .= "<br> <b>OBJ :</b> " . $this->getDisplay($lang);
        $message .= '<br> <b>LAST SQL QUERY :</b> ' . $the_last_sql;

        // -- rafik : danger : no call to any overrideble method here
        // -- to avoid infinite loop of this error method call
        //$message .= "<br> <b>PROPS :</b> " . $this->showMyProps();

        $message .= "<br> <b>Method :</b> $call_method";

        $message .= '<hr>';
        if ($_POST) {
            $message .= "<table dir='ltr'>";
            foreach ($_POST as $att => $att_val) {
                $message .= "<tr><td>posted <b>$att : </b></td><td>$att_val</td></tr>";
            }
            $message .= '</table><hr>';
        }
        if (class_exists('AfwAutoLoader') or class_exists('AfwSession')) {
            $objme = AfwSession::getUserConnected();
            if ($objme and $objme->isSuperAdmin()) {
                $message .= "<div id='analysis_log'>";
                $message .= AfwSession::getLog();
                $message .= '</div>';
            }
        }

        AFWDebugg::log($message);
        $message .= $out_scr;

        AfwRunHelper::safeDie($error_title, $message, true, null, false, true);

        return false;
    }
*/

set_exception_handler("myAfwExceptionHandler");