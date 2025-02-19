<?php
const _SHOW_ARGS = true;
const _SHOW_ALL_ARGS = false;
const _PROD = false;
const _UPGRADING = false;

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
                if(_PROD) prod_dump_exception($ex);
                else dump_exception($ex);
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
            if($ex instanceof AfwBusinessException) return dump_business_exception($ex);
            global $relative_path;
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

<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/jquery-ui-1.14.0.css">
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
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/bootstrap/bootstrap-v5.3.3.min.css">
<link href="<?php echo $relative_path;?>../lib/attention/attention.css" rel="stylesheet">




<script src="<?php echo $relative_path;?>../lib/js/jquery-3.6.0.min.js"></script>
<script src="<?php echo $relative_path;?>../lib/bootstrap/bootstrap-v5.3.3.min.js"></script>

<?php
  $my_font = "front";
  $my_theme = "simple";
  $lang = "en";
?>
<script src="<?php echo $relative_path;?>../lib/js/jquery-ui-1.14.0.js"></script>
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
                            <? if(_SHOW_ARGS) : ?>
                                <? if(isset($trace['args']) and $trace['args'] ) : ?>
                                    <? foreach ( $trace['args'] as $i => $arg ) : ?>
                                        <? if(!is_object($arg) and !is_array($arg)) : ?> 
                                            <span><?= gettype($arg); ?> : <?= var_export( $arg, true ); ?></span>
                                        <? else : ?>
                                            <span>Array or Object</span>
                                        <? endif; ?>
                                        <?= $i < count( $trace['args'] ) -1 ? ',' : ''; ?> 
                                    <? endforeach; ?>
                                <? else : ?>
                                    NULL
                                <? endif; ?>
                            <? else : ?>    
                                DISABLED-0
                            <? endif; ?>
                        </td>
                    </tr>                    
                    <? if(_SHOW_ALL_ARGS and $trace['args']) { ?>
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

function dump_business_exception($ex)
{
    ?>
    </div>
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
    <link rel="stylesheet" type="text/css" href="../lib/css/error-handler.css">
    <link rel="stylesheet" type="text/css" href="../lib/css/def_ar_front.css">
    <body>
    <div class='business-exception'>    
    <?php 
        $main_title = $ex->getMessage();
        $return_message = $ex->return_message; 
        $return_page = $ex->return_page; 
    ?>    
        <img class='bex' src='../lib/images/<?php echo $ex->picture ?>' width='128' height='128'/>
        <h1><?php echo $main_title ?></h1>
        <a class='btn btn-blue' href='<?php echo $return_page ?>'><?php echo $return_message ?></a>
    </div>
    </body>
    </html>
    <?php       
}

function prod_dump_exception($ex)
{
    if($ex instanceof AfwBusinessException) return dump_business_exception($ex);
            global $relative_path;
                $file = $ex->getFile();
                $line = $ex->getLine();

                if ( file_exists( $file ) )
                {
                        $lines = file( $file );
                }
                $crst = md5("crst" . date("YmdHis"));
    
?>
</div>
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
<link rel="stylesheet" href="<?php echo $relative_path;?>../lib/css/error-handler.css">
<body>
<div class='component eh'>
<?php
    $context = AfwUrlManager::getUriContext();
    if(class_exists("AfwSession")) $crm_site = AfwSession::config("crm_site", "");
    else $crm_site = "";
    if(class_exists("AfwVirtualErrorHelper"))
    {
        list($component, $version, $relc) = AfwVirtualErrorHelper::getDetails($ex);        
    }
    else
    {
        $component = "AFW-V-E-H";
        $version = "3.0";        
        $relc = "Nothing";
    }

    if(_UPGRADING) $main_title = "We are upgrading the system for you ..., please wait";
    else $main_title = "The following components need upgrade, please contact administrator";
    
?>    
        <h1><?php echo $main_title ?></h1>
        <h1>Component :<?php echo $component ; ?></h1>
        <h2>Version : <?php echo $version ; ?></h2>
        <h2>Context : <?php echo $context ; ?></h2>
        <!-- <?= $ex->getMessage(); ?> -->        
        <h3>Please contact your administrator by sending copy-past of the text of this screen in a new ticket in customer service plateform : <?php echo $crm_site ?></h3>
        <h1>Other Related Librairies : </h1>
        <h2><?php echo $relc; ?></h2>
</div>
</body>
</html>
<?php
}

}


set_exception_handler("myAfwExceptionHandler");