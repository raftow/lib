<?php
class AfwOperatorEnTranslator{

    public static function initData()
    {
        $trad = [];
        $trad["OPERATOR"]["OR"]					        = "or";
        $trad["OPERATOR"]["IS_EMPTY"]					= "is empty";
        $trad["OPERATOR"]["IS_NOT_EMPTY"]				= "is not empty";
        $trad["OPERATOR"]["EQUAL"]					= "equal to";
        $trad["OPERATOR"]["LESS_THAN"]			                = "less than";
        $trad["OPERATOR"]["GREATER_THAN"]   		                = "more than";
        $trad["OPERATOR"]["GREATER_OR_EQUAL_THAN"]                      = "more or equal to";
        $trad["OPERATOR"]["LESS_OR_EQUAL_THAN"]                         = "less or equal to";
        $trad["OPERATOR"]["NOT_EQUAL"]                         		= "different than";
        $trad["OPERATOR"]["CONTAIN"]                         		= "contains";
        $trad["OPERATOR"]["NOT_CONTAIN"]                       		= "does not contain";
        $trad["OPERATOR"]["BEGINS_WITH"]                         	= "starts with";
        $trad["OPERATOR"]["ENDS_WITH"]	                         	= "ends with";
        $trad["OPERATOR"]["IN"]	                         		= "from this list";
        $trad["OPERATOR"]["NOT_IN"]	                         	= "not from this list";
        $trad["OPERATOR"]["BETWEEN"]	                         	= "from-to";
        $trad["OPERATOR"]["FILE"]	                         	= "attributes of";
        $trad["OPERATOR"]["THE-FILE"]	                         	= "attributes screen";

        $trad["OPERATOR"]["SEARCH"]	                         	= "search in";
        $trad["OPERATOR"]["QSEARCH"]	                         	= "Quick search of";

        $trad["OPERATOR"]["CLICK-TO-EDIT-SEARCH"]	                = "Click to edit search criterea";
        $trad["OPERATOR"]["SEARCH CRITERIA"]	                       	= "search criterea";
        $trad["OPERATOR"]["RETRIEVE-COLS"]	                        = "retreive columns";
        $trad["OPERATOR"]["RETRIEVE-RESULT-ACTIONS"]	                = "actions on search result";
        $trad["OPERATOR"]["EDIT"]	                         	= "edit";
        $trad["OPERATOR"]["INSERT"]	                        	= "add";

        $trad["OPERATOR"]["_SEARCH"]	                         	= "search of";
        $trad["OPERATOR"]["_EDIT"]	                         	= "edit";
        $trad["OPERATOR"]["_INSERT"]	                         	= "add";
        $trad["OPERATOR"]["_DISPLAY"]	                         	= "description of";
        $trad["OPERATOR"]["_VIEW"]	                         	= "view";
        $trad["OPERATOR"]["_DELETE"]	                         	= "delete";
        $trad["OPERATOR"]["_CONSULT_"]	                         	= "view";
        $trad["OPERATOR"]["_STAT_"]	                         	= "stats about";
        $trad["OPERATOR"]["_REPORT_"]	                         	= "report about";
        $trad["OPERATOR"]["_WEB_SERV_LKUP_"]	                        = "Lookup web service";


        $trad["OPERATOR"]["NEW"]	                        	= "new record";
        $trad["OPERATOR"]["EDIT_FILE"]	                         	= "edit";
        $trad["OPERATOR"]["SEARCH_RESULT"]	                        = "search results in";
        $trad["OPERATOR"]["LOADING_PROBLEM"]	                        = "File upload failed";
        $trad["OPERATOR"]["SUBMIT"]	                        	= "submit";
        $trad["OPERATOR"]["SUBMIT-SEARCH"]	                        = "search";
        $trad["OPERATOR"]["SUBMIT-SEARCH-ADVANCED"]                     = "advanced";
        $trad["OPERATOR"]["RESET_FORM"]	                        	= "reset criterea";
        $trad["OPERATOR"]["NO-RECORD"]	                                = "no record found";
        $trad["OPERATOR"]["EXCEL-EXPORT"]	                       	= "export to excel file";
        $trad["OPERATOR"]["Y"]	                	        	= "Yes";
        $trad["OPERATOR"]["N"]	        	                	= "No";
        $trad["OPERATOR"]["W"]		                        	= "I don't know";
        $trad["OPERATOR"]["YES"]                	        	= "Yes";
        $trad["OPERATOR"]["NO"]	        	                	= "No";
        $trad["OPERATOR"]["EUH"]	                        	= "Not at yet";

        $trad["OPERATOR"]["UPDATE"]	                        	= "save changes";
        $trad["OPERATOR"]["UPDATE_AND_RETURN"]                        	= "save changes and return";
        $trad["OPERATOR"]["STEP"]	                         	= "step";
        $trad["OPERATOR"]["NEXT"]	                         	= "next >";
        $trad["OPERATOR"]["NEXT_TAB"]	                         	= "next step >";
        $trad["OPERATOR"]["PREVIOUS"]	                         	= "< previous";
        $trad["OPERATOR"]["FINISH"]	                         	= "finish";

        $trad["OPERATOR"]["FIELD VALUE"]	                        = "field value";

        $trad["OPERATOR"]["FIELD MANDATORY"]	        	        = "Data missed for mandatory field";
        $trad["OPERATOR"]["DELETED OR WRONG MANDATORY OBJECT"]	        = "Mandatory object is wrong or has been deleted";
        $trad["OPERATOR"]["WRONG FORMAT FOR FIELD"]        	        = "Field bad formatted";
        $trad["OPERATOR"]["WRONG DATA FOR FIELD"]                       = "Wrong data for this field";
        $trad["OPERATOR"]["PILLAR OBJECT"]	        	        = "Important object";
        $trad["OPERATOR"]["ERRORS"]	        	                = "erros";
                                                    
        $trad["OPERATOR"]["TYPE-ENUM"]	        	                = "Choice invalid from enumurated list"; 
        $trad["OPERATOR"]["TYPE-YN"]	        	                = "Value should be Yes or No?�";
        $trad["OPERATOR"]["TYPE-PCTG-VALUE"]	        	                = "Wrong percentage value "; 
        $trad["OPERATOR"]["TYPE-PCTG-FORMAT"]	        	                = "Percentage value bad formatted"; 
        $trad["OPERATOR"]["FORMAT-TIME"]	        	                = "Bad time value"; 
        $trad["OPERATOR"]["FORMAT-DATE"]	        	                = "Bad date value"; 
        $trad["OPERATOR"]["FORMAT-GDAT"]	        	                = "Bad Greg. date value";

        $trad["OPERATOR"]["FORMAT-ARABIC-TEXT"]	        	                = "Not arabic text"; 
        $trad["OPERATOR"]["FORMAT-HTTP"]	        	                = "Bad link"; 
        $trad["OPERATOR"]["FORMAT-SA-MOBILE"]	        	                = "Mobile number bad formatted"; 
        $trad["OPERATOR"]["FORMAT-EMAIL"]	        	                = "Email adress bad formatted";

        $trad["OPERATOR"]["LOGIN"]	        	                = "Login";
        $trad["OPERATOR"]["LOGOUT"]	        	                = "Logout";
        $trad["OPERATOR"]["DATA-ADMIN"]	        	                = "Administration";
        $trad["OPERATOR"]["HOME"]	        	                = "Home";
        $trad["OPERATOR"]["ANALYST"]	        	                = "Analyst";
        $trad["OPERATOR"]["MYACCOUNT"]	        	                = "My account";
        $trad["OPERATOR"]["CONTROL"]	        	                = "Control";
        $trad["OPERATOR"]["SEARCH_HERE"]	        	        = "Search here";
        $trad["OPERATOR"]["CONTACT_US"]	        	                = "Contact-us";
        $trad["OPERATOR"]["SIGN-UP"]	         	                = "Register";
        $trad["OPERATOR"]["LANGUE"]	         	                = "Language";
        $trad["OPERATOR"]["OPTIONS"]	         	                = "Options";
        $trad["OPERATOR"]["NULL"]	         	                = "Not specified";

        $trad["OPERATOR"]["page"]	         	                = "page";
        $trad["OPERATOR"]["record"]	         	                = "record";
        $trad["OPERATOR"]["new_instance"]	         	        = "new record"; 
        $trad["OPERATOR"]["qedit_new"]	         	                = "add many records";              
        $trad["OPERATOR"]["qedit_update"]	         	        = "quick update of search results";   
        $trad["OPERATOR"]["other_search"]	         	        = "back to"; 
        $trad["OPERATOR"]["back_to_last_form"]        	                = "back to previous screen";
        $trad["OPERATOR"]["new_search"]	         	                = "new search";
        $trad["OPERATOR"]["show"]	         	                = "show";
        $trad["OPERATOR"]["records_updated"]	         	        = "records updated : ";
        $trad["OPERATOR"]["record(s)"]	                	        = "record(s)";
        $trad["OPERATOR"]["qedit_some_records"]                   	= "quick edit on ";
        $trad["OPERATOR"]["save_with_sucess"]         	                = "Changes have been saved with success";
        $trad["OPERATOR"]["changes"]         	                        = "";
        $trad["OPERATOR"]["--changes"]         	                        = " ";
        $trad["OPERATOR"]["no_update_found"]	         	        = "No modification found";

        $trad["OPERATOR"]["MY-FILES"]         	                = "my files";
        $trad["OPERATOR"]["ETC"]         	                = "etc";

        return $trad;
    }
}