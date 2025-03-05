<?php
class AfwHtmlIncluderHelper
{
  
    public static function outputHeader(
    $lang, 
    $page_charset,
    $my_afw_theme,
    $options = [],
    $custom_scripts = [],
    $my_font = "front")
    {
      if($options["front-application"])
          $site_name = AfwSession::getCurrentFrontSiteName($options["front-application"],$lang);
      else
          $site_name = AfwSession::getCurrentSiteName($lang);
      $main_module = $cmodule = AfwUrlManager::currentURIModule();
      $xmodule = AfwSession::getCurrentlyExecutedModule();
      $xtemplate = AfwSession::getCurrentModuleTemplate();
      $pagecode = AfwUrlManager::currentPageCode();
      $company = AfwSession::currentCompany();
      $menu_template = AfwSession::currentMenuTemplate();
    
      // mandatory scripts-options can not be disabled
        $options["bootstrap"] = true;
        $options["front_header"] = true;
        $options["front_application"] = true;
        $options["jquery"] = true;
        $options["sweetalert"] = true;
        $options["dataTables"] = true;
        $options["calendars"] = true;
        // $options["dropdowntree"] = true;
        // $options["bootstrap-select"] = true;

        if($options["edit"])
        {
          $options["mobiscroll"] = true;
          $options["clock-timepicker"] = true;
          $options["sweetalert"] = true;
          $options["autocomplete"] = true;
          $options["calendars"] = true;
        }
        else
        {
          // @todo
          // below to remove when options["edit"] = true is managed
          $options["mobiscroll"] = true;
        }

        if($options["qedit"])
        {
          $options["autocomplete"] = true;
          $options["calendars"] = true;
        }

        $jquery_version = AfwSession::config('jquery-version', '3.6.0');
        $jquery_ui_version = AfwSession::config('jquery-ui-version', '1.14.0');
        $bootstrap_version = AfwSession::config('bootstrap-version', '5.3.3');
        
        $header = "<head>
          <script src='../lib/js/jquery-$jquery_version.min.js'></script>
          <script src='../lib/js/jquery.validate.js'></script>
          <link rel='stylesheet' href='../lib/css/jquery-ui-$jquery_ui_version.css'>
          <script src='../lib/js/jquery-ui-$jquery_ui_version.js'></script>
          <!-- script src='../lib/tree/tree.jquery.js'></script> -->
          
          ";

          
          
        // <link rel='stylesheet' href='../lib/css/line.css'>

        $header .= "
        <meta http-equiv='Content-Type' content='text/html; charset=$page_charset'>
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1, minimum-scale=1\" />
        <link rel='stylesheet' href='../lib/css/font-awesome.min-4.3.css'>
        <link rel='stylesheet' href='../lib/css/font-awesome.min.css'>
        
        <link rel='stylesheet' href='../lib/css/menu_$lang.css'>
        <script src='../lib/js/hzm.js'></script>
        ";

        $crst = md5("crst" . date("YmdHis"));
        

        if ($options["front_header"]) {
          $header .= "
          <link rel='stylesheet' href='../lib/css/front-application.css'>
          <link rel='stylesheet' href='../lib/css/hzm-v001.css'>";
        }

        if ($options["dashboard-stats"]) {
          $header .= "<link rel='stylesheet' href='../lib/css/dashboard-stats.css'>";
        }

        

        if ($options["chart-js"]) {
          $header .= "<script src='https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js'></script>";
        }


        if($options["otp"]) $header .= "
          <link rel='stylesheet' href='../lib/css/otp.css'>";

        if($options["front_application"]) $header .= "
          <link rel='stylesheet' href='../lib/css/front_screen_pc.css?crst=$crst'>
          <link rel='stylesheet' href='../lib/css/front_tablet.css?crst=$crst'>
          <link rel='stylesheet' href='../lib/css/front_mobile.css?crst=$crst'>
          <link rel='stylesheet' href='../lib/css/front_mobile_thin.css?crst=$crst'>
          <link rel='stylesheet' href='../lib/css/material-design-iconic-font.min.css'>
          ";

        
        


        if($options["bootstrap"]) 
        {
          // This is to resolve problem of : TypeError: i.createPopper is not a function
          // found when we use bootstrap-v5.3.3 resolved in bootstrap.bundle version until will be fixed 
          // in next bootstrap versions
            if(AfwSession::config("bootstrap.bundle", false))
            {
              $bootstrap_script = "<script src='../lib/bootstrap/bootstrap.bundle.min.js'></script>";
            }
            else
            {
              $bootstrap_script = "<script src='../lib/bootstrap/bootstrap-v$bootstrap_version.min.js'></script>";
            }

            $header .= "$bootstrap_script
          <link rel='stylesheet' href='../lib/bootstrap/bootstrap-v$bootstrap_version.min.css'>
          ";
        }
        

        if($options["bootstrap-select"]) $header .= "
          <link rel='stylesheet' href='../lib/bsel/css/bootstrap-select.css'>
          <script src='../lib/bsel/js/bootstrap-select.js'></script>
          ";


        if($options["jstree_activate"]) $header .= "
        <link rel='stylesheet' type='text/css' href='../lib/css/jstree/default/style.min.css' />
        <script src='../lib/js/jstree.min.js'></script>
          ";

        if($options["ivviewer_activate"]) $header .= "
        <link rel='stylesheet' type='text/css' media='screen' href='../lib/viewer/viewer.css' />
          ";

          if($options["ivviewer_activate"]) $header .= "
          <link rel='stylesheet' type='text/css' media='screen' href='../lib/viewer/viewer.css' />
          <script type=\"text/javascript\" src=\"../lib/iv-viewer/dist/iv-viewer.js\"></script>
          ";  

          if($options["dropdowntree"]) $header .= "
          <link rel='stylesheet' href='../lib/css/dropdowntree.css' />
          <script src='../lib/js/dropdowntree.js'></script>
          ";

          

          if($options["qedit"]) $header .= "
          <script src='../lib/js/qedit.js'></script>
          ";

          if($options["bootstrap-multiselect"]) $header .= "
          <link type='text/css' rel='stylesheet' href='../lib/bmulti/css/bootstrap-multiselect.css' />
          <script src='../lib/bmulti/js/bootstrap-multiselect.js'></script>
          ";

          if($options["mobiscroll"]) $header .= "
          <link type='text/css' rel='stylesheet' href='../lib/css/mobiscroll.jquery.min.css' />
          <script src='../lib/js/mobiscroll.jquery.min.js'></script>
          ";  

          

          if($options["clock-timepicker"]) $header .= "
          <script src='../lib/js/jquery-clock-timepicker.min.js'></script>        
          ";  

          if($options["sweetalert"]) $header .= "
          <script src='../lib/js/sweetalert.min.js'></script>
          ";

          if ($options["other-js-arr"]) {
            foreach($options["other-js-arr"] as $js_file_path)
            {
              $header .= "<script src='$js_file_path'></script>";
            }          
          }

          if($options["edit"]) $header .= "
          <link href='../lib/skins/square/green.css' rel='stylesheet' type='text/css'>
          <link href='../lib/skins/square/red.css' rel='stylesheet' type='text/css'>
          <script src='../lib/js/icheck.js'></script>

          <script>
            $(document).ready(function() {
              $('input.echeckbox').iCheck({
                checkboxClass: 'icheckbox_square-green',
                radioClass: 'iradio_square-green',
                increaseArea: '20%' // optional
              });

              $('input.rcheckbox').iCheck({
                checkboxClass: 'icheckbox_square-red',
                radioClass: 'iradio_square-red',
                increaseArea: '20%' // optional
              });

            });
          </script>
          ";        
        
          
          if ($lang != "en") $header .= "
          <script src='../lib/js/localization/messages_$lang.js'></script>
          ";

          if($options["dataTables"]) $header .= "
          <script src='../lib/js/jquery.dataTables_$lang.min.js'></script>
          ";


          if($options["autocomplete"]) $header .= "
          <link href='../lib/css/autocomplete.css' rel='stylesheet' type='text/css'>
          <script src='../lib/js/jquery.ui.autocomplete.html.js'></script>
          ";

          if($options["calendars"]) $header .= "
          <link rel='stylesheet' href='../lib/hijra/jquery.calendars.picker.css' />
          <script src='../lib/hijra/jquery.calendars.js'></script>
          <script src='../lib/hijra/jquery.calendars.plus.js'></script>
          <script src='../lib/hijra/jquery.calendars.picker.js'></script>
          <script src='../lib/hijra/jquery.calendars.ummalqura.js'></script>          
          ";

          $header .= "          
          <meta http-equiv='X-UA-Compatible' content='IE=9; IE=8; IE=EDGE'>
          <link href='../lib/css/responsive.css' rel='stylesheet' type='text/css'>
          <script src='./js/module.js'></script>
          
          <link href=\"../lib/css/def_".$lang."_".$my_font.".css\" rel=\"stylesheet\" type=\"text/css\">
          <link href=\"../lib/css/$my_afw_theme/style_common.css?crst=$crst\" rel=\"stylesheet\" type=\"text/css\">
          <link href=\"../lib/css/$my_afw_theme/style_$lang.css?crst=$crst\" rel=\"stylesheet\" type=\"text/css\">
          <link href=\"../lib/css/$menu_template"."_menu.css?crst=$crst\" rel=\"stylesheet\" type=\"text/css\">
          <link href=\"../lib/css/$menu_template"."_menu_$lang.css?crst=$crst\" rel=\"stylesheet\" type=\"text/css\">
          
          
          <link href='../$main_module/css/module.css?crst=$crst' rel='stylesheet' type='text/css'>
          <link href='../client-$company/css/common-$company.css?crst=$crst' rel='stylesheet' type='text/css'>

          <script src='../lib/js/$menu_template"."_menu.js'></script>
          <script src='../lib/js/$menu_template"."_menu_$lang.js'></script>

          
          ";

          if($xmodule != $main_module) {
              /* $header .= "  
              <link href='../$xmodule/css/module.css?crst=$crst' rel='stylesheet' type='text/css'>
              "; */ 
          }


          $header .= "
          <title>$site_name</title>
          <link href='favicon.ico' rel='shortcut icon'>";


          if($options["page_css_file"]) {
            $page_css_file = $options["page_css_file"];
            $header .= "
            <link href='./css/$page_css_file.css?crst=$crst' rel='stylesheet' type='text/css'>
            ";
          }

          $xtemplate_css_file = "template_$xtemplate.css";
          $file_dir_name = dirname(__FILE__);
          $xtemplate_css_file_full_path = $file_dir_name ."/../../../lib/css/".$xtemplate_css_file;
          // die("xtemplate_css_file=".$xtemplate_css_file." should be in $cmodule/css");
          if(file_exists($xtemplate_css_file_full_path))
          {
            $header .= "
            <link hint='rafik' href=\"../lib/css/$xtemplate_css_file?crst=$crst\" rel=\"stylesheet\" type=\"text/css\">
            ";
          }
          else 
          {
            throw new AfwRuntimeException("xtemplate_css_file=".$xtemplate_css_file." not found in lib css");
          }

          foreach ($custom_scripts as $custom_script) {
            if ($custom_script["type"] == "css") {
              $header .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $custom_script["path"] . "\" />";
            } elseif ($custom_script["type"] == "js") {
              $header .= "<script  type=\"text/javascript\" src=\"" . $custom_script["path"] . "\" ></script>";
            } else throw new AfwRuntimeException($custom_script["path"] . " has unknown type in custom_script parameter");
          }

          if($options["fancybox_activate"]) $header .= "
          <link rel='stylesheet' type='text/css' media='screen' href='../lib/fancy-box/jquery.fancybox-1.3.4.css' />
          ";

          if($options["table_obj_style"]) $header .= "
          <link rel='stylesheet' type='text/css' href='../lib/css/table_obj_style.css' />
          ";

          if ($options["other-css-arr"]) {
            foreach($options["other-css-arr"] as $css_file_path)
            {
              $header .= "<link rel='stylesheet' type='text/css' href='$css_file_path' />";
            }          
          }

          $pagecode_js_file = "$pagecode.js";
          $file_dir_name = dirname(__FILE__);
          $pagecode_js_file_full_path = $file_dir_name ."/../../../$cmodule/js/$pagecode_js_file";
          if(file_exists($pagecode_js_file_full_path))
          {
            $header .= "
          <script src='./js/$pagecode_js_file'></script>  
          ";  
          }
          else
          {
            $header .= "
          <!-- auto-js file '[module]/js/$pagecode_js_file' not found so skipped --> 
          "; 
          }


          $header .= "</head>";

          return $header;
              
    }

}