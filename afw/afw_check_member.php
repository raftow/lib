<?php

        if(!$objme) $objme = AfwSession::getUserConnected();
        $debugg_start_check_member = false;
        $debugg_before_debugg_activation = false;
        
        
        if($debugg_start_check_member)
        {
           AfwSession::log("start_check_member : only_members=$only_members, session data (check user_avail and user_id filled) : ");
           AfwSession::logSessionData();
        }
        
        /*
        $_SES SION["lastpage"] = basename($_SERVER['PHP_SELF']);
        $_SES SION["lastget"] = $_GET;
        if($_GET["lang"]) $_SES SION["lang"] = $_GET["lang"];
        */
        
               
        if($only_members and (!AfwSession::getSessionVar("user_id")))
        {
                // $_SES SION["error"] = "الرجاء تسجيل الدخول أولا";
                // $_SES SION["error"] .= var_export($_SE SSION,true);
                if(!$config["default-logged-out-page"]) $config["default-logged-out-page"] = "login.php";
                
                 
        	header("Location: ".$config["default-logged-out-page"]);
        	exit();
        }
        
        
        if(!$MODULE)
        {
                $MODULE = AfwUrlManager::currentURIModule();       
        }
        
        if(!$MODULE) 
        {
                $message = "لم يتم تحديد الوحدة. خطأ فني  <br>";
                $backtrace = debug_backtrace();
		foreach($backtrace as $entry) 
                {
			$message .= "<br> <b>Function : </b>" . $entry['function']; 
                        $message .= " <b>File : </b>" . $entry['file']; 
                        $message .= " <b>Line : </b>" . $entry['line'] . " \n ";
		}

                AfwSession::pushError($message);
        
        }
        if(!$cl_dbg) $cl_dbg = date("YmdHis");
        /*if(!$cl) $cl_dbg = $sess_dbg;
        else $cl_dbg = $cl."_".date("Ymd");*/        
        $debug_name = $MODULE."_".$cl_dbg;
         
        $my_debug_file = "debugg_".AfwSession::getSessionVar("user_id")."_${debug_name}_".".txt";
        
        if($debugg_before_debugg_activation) AFWDebugg::log("debugg file name will be changed to : $DEBUGG_SQL_DIR,$my_debug_file");
        
        AFWDebugg::initialiser($DEBUGG_SQL_DIR,$my_debug_file);
        //AFWDebugg::initialiser("/www/log","debugg_$me.txt");
        if(!AfwSession::sessionStarted())
        {
                $message = "Error session not started, it should be started at this level";
                $backtrace = debug_backtrace();
        	foreach($backtrace as $entry) 
                {
        		$message .= "<br> <b>Function : </b>" . $entry['function']; 
                        $message .= " <b>File : </b>" . $entry['file']; 
                        $message .= " <b>Line : </b>" . $entry['line'] . " \n ";
        	}
                AFWDebugg::log($message);
                die($message);
        }
        
        $me = AfwSession::getSessionVar("user_id");
        if(!$me) $me = 0;
        if($me)
        {       if(!$lang) $lang = AfwSession::getSessionVar("current_lang"); 
                // AFWDebugg::log("user id connected : ".$me." lang $lang ");
                if(!$objme)
                {
                        $objme = new Auser();
                        if($me<=1000000)  // if > 1000000 then it is customer not employee (so no user in auser it is virtual user can't be loaded from DB)
                        {
                           $objme->load($me);
                        }
                        else
                        {
                                $objme->set("id",$me);
                                $objme->set("firstname",AfwSession::getSessionVar("user_firstname"));
                                /*
                                obsolete in v3.0 of momken
                                each application manage its default MAU
                                if($customer_default_roles[$THIS_MODULE_ID])
                                {
                                        $file_dir_name = dirname(__FILE__); 
                                        
                                        $customer _default_mau[$THIS_MODULE_ID] = ModuleAuser::virtualModuleAuser($THIS_MODULE_ID, 0, $customer_default_roles[$THIS_MODULE_ID]);
                                }
                                
                                if($customer _default_mau[$THIS_MODULE_ID])
                                {
                                        $customer _default_mau[$THIS_MODULE_ID]->set("id_auser",$me);
                                        
                                        $customer _default_mau_id = $customer _default_mau[$THIS_MODULE_ID]->getId();
                                        $mauList = array();
                                        $mauList[$customer _default_mau_id] = $customer _default_mau[$THIS_MODULE_ID];
                                        
                                        $objme->myModules[$MODULE] = array(-1, $THIS_MODULE_ID, $customer _default_mau[$THIS_MODULE_ID]);
                                        $objme->mau_list = $mauList;
                                }
                                else
                                {
                                        die("customer _default_mau[$THIS_MODULE_ID] not defined");
                                }*/
                        }
                        // die("objme->load($me) = ".var_export($objme,true));
                        $objme->loadOptions(); 
                        
                        if(!$lang)
                        {
                                $langobj = $objme->hetLang();
                                if($langobj)
                                {
                                        $lang = strtolower($langobj->getVal("lookup_code"));
                                        AfwSession::setSessionVarIfNotSet("current_lang", $lang);
                                }
                        }

                        if($only_admin and (!$objme->isAdmin()))
                        {
                                AfwSession::pushError("لا توجد عندك صلاحية ادارة");
                                if($info) AfwSession::pushInformation($info,"method-$pbMethodCode");
                                if($error) AfwSession::pushError($error);                                
                	        header("Location: index.php");
                	        exit();
                        }

                        

                        
                        
                }
                else
                {
                        $objme =& $objme;
                }   
                // si on vient d'un mail officiel alors marquer qu'il est ouvert
                /*
                $mail_sent_id = $_GET["mail_sent_id"];
                if($mail_sent_id)
                {
                     require_once("mail_sent.php");
                     $ms = new MailSent();
                     $ms->select("id_taleb",$me);
                     if($ms->load($mail_sent_id))
                     {
                             $ms->set("heure_ouvert",date("Y-m-d H:i:s"));
                             $ms->set("ouvert","Y");
                             $ms->update();           
                     }
                }*/
        }
        
        // die(" objme : ".var_export($objme,true));
        
        if($only_members and (!$objme))
        {
                AfwSession::pushError("الرجاء تسجيل الدخول أولا");
        	header("Location: login.php");
        	exit();
        }
        else if($haltme and $objme and $objme->isSuperAdmin())
        {
             echo "<h1>Admin halt</h1><br>";
             // show_cache_analysis();
             echo(var_export($_SERVER,true));
             
             if($hmm and $hmc) $halt_obj = $objme->userCanTable[$hmm][$hmc];
             elseif($hmm) $halt_obj = $objme->userCanTable[$hmm];
             else $halt_obj = $objme->userCanTable;
             
             die(var_export($halt_obj,true));
        }
        
        //$my_email = $objme->getVal("email");
        //if($objme->getId() and (!$my_email)) $objme->debuggObj($objme);
        
        if(($objme) and ($objme->popup))
        {
            $target = "target='popup'";
            $popup_t = "on";  
        }
        else
        {
            $target = "";
            $popup_t = ""; 
        }

        if($MODULE)
        {
                if($only_members)
                {
                        $objme_id = $objme->getId();
                        
                        list($accepted, $reason) = $objme->initWithModule($MODULE);
                        
                        if(!$accepted)
                        {
                                AfwSession::pushError("denied access to module $MODULE : $reason");
                        }
                }
        }