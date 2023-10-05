<?php
function findUser($look_user_name)
{      
      global $ldap_search_server, $ldap_search_username, $ldap_search_password, $ldap_search_base_dn, $ldap_search_username_var;
      
        $ldap_connection = ldap_connect($ldap_search_server);
        
        if (FALSE === $ldap_connection){
            // Uh-oh, something is wrong...
        	echo 'Unable to connect to the ldap server';
        }
        
        // We have to set this option for the version of Active Directory we are using.
        ldap_set_option($ldap_connection, LDAP_OPT_PROTOCOL_VERSION, 3) or die('Unable to set LDAP protocol version');
        ldap_set_option($ldap_connection, LDAP_OPT_REFERRALS, 0); // We need this for doing an LDAP search.
        
        if (TRUE === ldap_bind($ldap_connection, $ldap_search_username, $ldap_search_password))
        {
        
            	
            $search_filter = "($ldap_search_username_var=$look_user_name)";
        	
            //Connect to LDAP
            $result = ldap_search($ldap_connection, $ldap_search_base_dn, $search_filter);
        	
            if (FALSE !== $result)
            {
        		$entries = ldap_get_entries($ldap_connection, $result);
        		
        		// Uncomment the below if you want to write all entries to debug somethingthing 
        		//var_dump($entries);
                        // or die("entries = ".var_export($entries,true));
                        
                        $ldap_users = array();
                        
        		//For each account returned by the search
        		for ($x=0; $x<$entries['count']; $x++)
                        {
        			//
        			//Retrieve values from Active Directory
        			//
        			
        			//Windows Usernaame
        			$ldap_users[$x]["username"] = "";
        			
        			if (!empty($entries[$x]['samaccountname'][0])) 
                                {
        				$ldap_users[$x]["username"] = $entries[$x]['samaccountname'][0];
        				if ($ldap_users[$x]["username"] == "NULL")
                                        {
        					$ldap_users[$x]["username"] = "";
        				}
        			} 
                                else 
                                {
        				//#There is no samaccountname s0 assume this is an AD contact record so generate a unique username
        				
        				$LDAP_uSNCreated = $entries[$x]['usncreated'][0];
        				$ldap_users[$x]["username"] = "CONTACT_" . $LDAP_uSNCreated;
        			}
        			
        			//Last Name
        			$LDAP_LastName = "";
        			
        			if (!empty($entries[$x]['sn'][0])) {
        				$LDAP_LastName = $entries[$x]['sn'][0];
        				if ($LDAP_LastName == "NULL"){
        					$LDAP_LastName = "";
        				}
        			}
                                
                                $ldap_users[$x]["last_name"] = $LDAP_LastName;
        			
        			//First Name
        			$LDAP_FirstName = "";
        			
        			if (!empty($entries[$x]['givenname'][0])) {
        				$LDAP_FirstName = $entries[$x]['givenname'][0];
        				if ($LDAP_FirstName == "NULL"){
        					$LDAP_FirstName = "";
        				}
        			}
                                
                                $ldap_users[$x]["first_name"] = $LDAP_FirstName;
        			
        			//Company
        			$LDAP_CompanyName = "";
        			
        			if (!empty($entries[$x]['company'][0])) {
        				$LDAP_CompanyName = $entries[$x]['company'][0];
        				if ($LDAP_CompanyName == "NULL"){
        					$LDAP_CompanyName = "";
        				}
        			}
                                
                                $ldap_users[$x]["company"] = $LDAP_CompanyName;
        			
        			//Department
        			$LDAP_Department = "";
        			
        			if (!empty($entries[$x]['department'][0])) {
        				$LDAP_Department = $entries[$x]['department'][0];
        				if ($LDAP_Department == "NULL"){
        					$LDAP_Department = "";
        				}
        			}
                                
                                $ldap_users[$x]["department"] = $LDAP_Department;
        			
        			//Job Title
        			$LDAP_JobTitle = "";
        			
        			if (!empty($entries[$x]['title'][0])) {
        				$LDAP_JobTitle = $entries[$x]['title'][0];
        				if ($LDAP_JobTitle == "NULL"){
        					$LDAP_JobTitle = "";
        				}
        			}
                                
                                $ldap_users[$x]["job_title"] = $LDAP_JobTitle;
        			
        			//IPPhone
        			$LDAP_OfficePhone = "";
        			
        			if (!empty($entries[$x]['ipphone'][0])) {
        				$LDAP_OfficePhone = $entries[$x]['ipphone'][0];
        				if ($LDAP_OfficePhone == "NULL"){
        					$LDAP_OfficePhone = "";
        				}
        			}
                                $ldap_users[$x]["office_phone"] = $LDAP_OfficePhone;
        			
        			//FAX Number
        			$LDAP_OfficeFax = "";
        			
        			if (!empty($entries[$x]['facsimiletelephonenumber'][0])) {
        				$LDAP_OfficeFax = $entries[$x]['facsimiletelephonenumber'][0];
        				if ($LDAP_OfficeFax == "NULL"){
        					$LDAP_OfficeFax = "";
        				}
        			}
                                $ldap_users[$x]["office_fax"] = $LDAP_OfficeFax;
        			
        			//Mobile Number
        			$LDAP_CellPhone = "";
        			
        			if (!empty($entries[$x]['mobile'][0])) {
        				$LDAP_CellPhone = $entries[$x]['mobile'][0];
        				if ($LDAP_CellPhone == "NULL"){
        					$LDAP_CellPhone = "";
        				}
        			}
                                $ldap_users[$x]["mobile"] = $LDAP_CellPhone;
        			
        			//Telephone Number
        			$LDAP_DDI = "";
        			
        			if (!empty($entries[$x]['telephonenumber'][0])) {
        				$LDAP_DDI = $entries[$x]['telephonenumber'][0];
        				if ($LDAP_DDI == "NULL"){
        					$LDAP_DDI = "";
        				}
        			}
                                $ldap_users[$x]["telephone_number"] = $LDAP_DDI;
        			
        			//Email address
        			$LDAP_InternetAddress = "";
        			
        			if (!empty($entries[$x]['mail'][0])) {
        				$LDAP_InternetAddress = $entries[$x]['mail'][0];	
        				if ($LDAP_InternetAddress == "NULL"){
        					$LDAP_InternetAddress = "";
        				}
        			}
                                $ldap_users[$x]["email"] = $LDAP_InternetAddress;
        			
        			//Home phone
        			$LDAP_HomePhone = "";
        			
        			if (!empty($entries[$x]['homephone'][0])) {
        				$LDAP_HomePhone = $entries[$x]['homephone'][0];
        				if ($LDAP_HomePhone == "NULL"){
        					$LDAP_HomePhone = "";
        				}
        			}
                                $ldap_users[$x]["home_phone"] = $LDAP_HomePhone;

        		} //END for loop
        	} //END FALSE !== $result
        	else
                {
                     return array(false,"ldap_search failed");
                }
        	ldap_unbind($ldap_connection); // Clean up after ourselves.
        
        
        } //END ldap_bind
        else
        {
              return array(false,"ldap_bind failed");
        }
        
        return array(true, $ldap_users);
}


?>