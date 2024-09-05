<?php

global $general_optimizer;
if(!$general_optimizer) $general_optimizer=0;
$general_optimizer++;
if($general_optimizer>200)
{
    throw new AfwRuntimeException("System has been entered in maintenance mode by auto-admin-module (contact rafiq)");
}

if(isset($trad) and (!is_array($trad)))
{
    // die("trad is ".var_export($trad,true));
    unset($trad);
}

$trad["OPERATOR"]["ALL"]					= "الجميع";

$trad["OPERATOR"]["INSTR-STD"]					= "أدخل";
$trad["OPERATOR"]["INSTR-TEXT"]					= "أدخل";
$trad["OPERATOR"]["INSTR-FK"]					= "إختر";
$trad["OPERATOR"]["INSTR-ENUM"]					= "إختر";

$trad["OPERATOR"]["CHOICE"]					= "إختيار";
$trad["OPERATOR"]["OR"]					        = "أو";
$trad["OPERATOR"]["IS_EMPTY"]					= "فارغ";
$trad["OPERATOR"]["IS_NOT_EMPTY"]				= "ليس بفارغ";
$trad["OPERATOR"]["EQUAL"]					= "يساوي";
$trad["OPERATOR"]["LESS_THAN"]			                = "أقل من";
$trad["OPERATOR"]["GREATER_THAN"]   		                = "أكثر من";
$trad["OPERATOR"]["GREATER_OR_EQUAL_THAN"]                      = "أكثر أو مساوي ل";
$trad["OPERATOR"]["LESS_OR_EQUAL_THAN"]                         = "أقل أو مساوي ل";
$trad["OPERATOR"]["NOT_EQUAL"]                         		= "مختلف عن";
$trad["OPERATOR"]["CONTAIN"]                         		= "يحتوي على";
$trad["OPERATOR"]["NOT_CONTAIN"]                       		= "لا يحتوي على";
$trad["OPERATOR"]["BEGINS_WITH"]                         	= "يبدأ ب";
$trad["OPERATOR"]["ENDS_WITH"]	                         	= "ينتهي ب";
$trad["OPERATOR"]["IN"]	                         		= "ضمن هذه الخيارات";
$trad["OPERATOR"]["NOT_IN"]	                         	= "ليس ضمن هذه الخيارات";
$trad["OPERATOR"]["BETWEEN"]	                         	= "الفترة";
$trad["OPERATOR"]["FILE"]	                         	= " ";
$trad["OPERATOR"]["THE-FILE"]	                         	= "شاشة الخصائص";

$trad["OPERATOR"]["SEARCH"]	                         	= "بحث متقدم في";
$trad["OPERATOR"]["QSEARCH"]	                         	= "الإستعلام عن";

$trad["OPERATOR"]["CLICK-TO-EDIT-SEARCH"]	                = "انقر هنا لتعديل معايير البحث";
$trad["OPERATOR"]["SEARCH CRITERIA"]	                       	= "معايير البحث في";
$trad["OPERATOR"]["RETRIEVE-COLS"]	                        = "الحقول التي تريد إظهارها في نتائج البحث";
$trad["OPERATOR"]["RETRIEVE-RESULT-ACTIONS"]	                = "اجراءات على نتائج البحث";
$trad["OPERATOR"]["EDIT"]	                         	= " ";
$trad["OPERATOR"]["INSERT"]	                        	= "إضافة";

$trad["OPERATOR"]["_SEARCH"]	                         	= "البحث عن";
$trad["OPERATOR"]["_EDIT"]	                         	= "تعديل";
$trad["OPERATOR"]["_INSERT"]	                         	= "إضافة";
$trad["OPERATOR"]["_DISPLAY"]	                         	= "عرض";
$trad["OPERATOR"]["_VIEW"]	                         	= "عرض ";
$trad["OPERATOR"]["_DELETE"]	                         	= "مسح";
$trad["OPERATOR"]["_CONSULT_"]	                         	= "الاستعلام عن";
$trad["OPERATOR"]["_STAT_"]	                         	= "إحصائيات حول";
$trad["OPERATOR"]["_REPORT_"]	                         	= "تقرير حول";
$trad["OPERATOR"]["_WEB_SERV_LKUP_"]	                        = "جلب قائمة ثابتة : ";


$trad["OPERATOR"]["NEW"]	                        	= "إضافة سجل جديد";
$trad["OPERATOR"]["EDIT_FILE"]	                         	= "تعديل البيانات";
$trad["OPERATOR"]["SEARCH_RESULT"]	                        = "نتائج الاستعلام في";
$trad["OPERATOR"]["LOADING_PROBLEM"]	                        = "Erreur lors du chargement du fichier";
$trad["OPERATOR"]["SUBMIT"]	                        	= "انطلق";
$trad["OPERATOR"]["SUBMIT-SEARCH"]	                        = "بحث";
$trad["OPERATOR"]["EXECUTE"]	                        = "تنفيذ";
$trad["OPERATOR"]["SUBMIT-SEARCH-ADVANCED"]                     = "بحث متقدم";
$trad["OPERATOR"]["RESET-CRITEREA"]	                        = "مسح المعايير";//"بحث جديد";
$trad["OPERATOR"]["RESET_FORM"]	                        	= "مسح معايير البحث";
$trad["OPERATOR"]["NO-RECORD"]	                                = "لا يوجد سجلات";
$trad["OPERATOR"]["EXCEL-EXPORT"]	                       	= "تصدير النتائج إلى ملف إكسيل";
$trad["OPERATOR"]["DDB-BTN"]	               	        	= "حذف المكررات";
$trad["OPERATOR"]["Y"]	                	        	= "نعم";
$trad["OPERATOR"]["N"]	        	                	= "لا";
$trad["OPERATOR"]["W"]		                        	= "غير محدد";
$trad["OPERATOR"]["YES"]                	        	= "نعم";
$trad["OPERATOR"]["NO"]	        	                	= "لا";
$trad["OPERATOR"]["EUH"]	                        	= "غير محدد";
$trad["OPERATOR"]["FOR"]	        	                = "لـ";

$trad["OPERATOR"]["save"]	                        	= "حفظ";
$trad["OPERATOR"]["SAVE"]	                        	= "حفظ فقط";
$trad["OPERATOR"]["UPDATE"]	                        	= "حفظ التعديلات";
$trad["OPERATOR"]["UPDATE_AND_RETURN"]                        	= "حفظ التعديلات ورجوع";
$trad["OPERATOR"]["RUN_DDB"]	                         	= "تنفيذ حذف المكررات";
$trad["OPERATOR"]["STEP"]	                         	= "الخطوة";
$trad["OPERATOR"]["NEXT"]	                         	= "حفظ ومتابعة >";
$trad["OPERATOR"]["NEXTRO"]	                         	= "متابعة >";
$trad["OPERATOR"]["NEXT_TAB"]	                         	= "الخطوة التالية >";
$trad["OPERATOR"]["GO_TO"]	                         	= "الإنتقال إلى";
$trad["OPERATOR"]["VIEW"]	                         	= "مشاهدة";
$trad["OPERATOR"]["PREVIOUS"]	                         	= "< حفظ ورجوع";
$trad["OPERATOR"]["PREVIOUSRO"]	                         	= "< رجوع";
$trad["OPERATOR"]["FINISH"]	                         	= "حفظ وإنهاء";
$trad["OPERATOR"]["FINISHRO"]	                         	= "إنهاء";
$trad["OPERATOR"]["COMPLETE_LATER"]	                        = "حفظ كمسودة وإكمال البيانات لاحقا";
$trad["OPERATOR"]["COMPLETE_LATERRO"]	                        = "إنهاء";


$trad["OPERATOR"]["FIELD"]	                                = "حقل";
$trad["OPERATOR"]["FIELD VALUE"]	                        = "بالنسبة للحقل";

$trad["OPERATOR"]["FIELD MANDATORY"]	        	        = "حقل إلزامي يجب إدخاله";
$trad["OPERATOR"]["DELETED OR WRONG MANDATORY OBJECT"]	        = "كيان إلزامي غير صحيح أو تم حذفه";
$trad["OPERATOR"]["WRONG FORMAT FOR FIELD"]        	        = "بيانات غير مناسبة للحقل";
$trad["OPERATOR"]["WRONG DATA FOR FIELD"]                       = "بيانات غير صحيحة للحقل";
$trad["OPERATOR"]["EMPTY LIST FOR REQUIRED FIELD"]              = "هذا الحقل الزامي يجب اختيار بعض الخيارات";
$trad["OPERATOR"]["PILLAR OBJECT"]	        	        = "البيانات : ";
$trad["OPERATOR"]["ERRORS"]	        	                = "من الأخطاء";
                                       
$trad["OPERATOR"]["TYPE-ENUM"]	        	                = "اختيار غير موجود في القائمة"; 
$trad["OPERATOR"]["TYPE-YN"]	        	                = "قيمة خطأ لحقل نعم/لا";
$trad["OPERATOR"]["TYPE-PCTG-VALUE"]	        	                = "تجاوز في قيمة النسبة المائوية"; 
$trad["OPERATOR"]["TYPE-PCTG-FORMAT"]	        	                = "خطأ في قيمة النسبة المائوية"; 
$trad["OPERATOR"]["FORMAT-TIME"]	        	                = "قيمة وقتية غير صحيحة"; 
$trad["OPERATOR"]["FORMAT-DATE"]	        	                = "تاريخ هجري غير صحيح"; 
$trad["OPERATOR"]["FORMAT-GDAT"]	        	                = "تاريخ نصراني غير صحيح";
$trad["OPERATOR"]["TEXT-MAX-LENGTH"]	        	                = "جملة أو فقرة تجاوز طولها المسموح به";
$trad["OPERATOR"]["TEXT-MIN-LENGTH"]	        	                = "هذا النص لم يحقق الطول الأدنى المطلوب";

$trad["OPERATOR"]["FORMAT-ARABIC-TEXT"]	        	                = "لا بد أن يكون غالب النص كلاما باللغة العربية"; 
$trad["OPERATOR"]["FORMAT-HTTP"]	        	                = "رابط غير صحيح"; 
$trad["OPERATOR"]["FORMAT-SA-MOBILE"]	        	                = "رقم جوال غير صحيح";
$trad["OPERATOR"]["FORMAT-SA-TRADENUM"]	        	                = "رقم سجل تجاري غير صحيح";
$trad["OPERATOR"]["FORMAT-SA-NATIONAL-UNIFIED-NUMBER"]	        	                = "رقم غير صحيح تأكد منه في السجل التجاري أو وثيقة الموارد البشرية";
$trad["OPERATOR"]["FORMAT-SA-IDN"]	        	                = "رقم هوية غير صحيح حسب مواصفات المملكة العربية السعودية";
$trad["OPERATOR"]["FORMAT-EMAIL"]	        	                = "بريد الكتروني غير صحيح"; 

$trad["OPERATOR"]["LOGIN"]	        	                = "تسجيل الدخول";
$trad["OPERATOR"]["LOGOUT"]	        	                = "تسجيل الخروج";
$trad["OPERATOR"]["DATA-ADMIN"]	        	                = "إدارة البيانات";
$trad["OPERATOR"]["HOME"]	        	                = "الرئيسية";
$trad["OPERATOR"]["ANALYST"]	        	                = "لوحة المحلل";
$trad["OPERATOR"]["MYACCOUNT"]	        	                = "حسابي";
$trad["OPERATOR"]["CONTROL"]	        	                = "تحكم";
$trad["OPERATOR"]["SEARCH_HERE"]        	                = "ابحث هنا";
$trad["OPERATOR"]["CONTACT_US"]	         	                = "اتصل بنا";
$trad["OPERATOR"]["SIGN-UP"]	         	                = "إنشاء حساب";
$trad["OPERATOR"]["LANGUE"]	         	                = "اللغة";
$trad["OPERATOR"]["OPTIONS"]	         	                = "خيارات";
$trad["OPERATOR"]["NULL"]	         	                = "غير محدد";
$trad["OPERATOR"]["NOT YET"]	         	                = "ليس بعد";


$trad["OPERATOR"]["page"]	         	                = "صفحة";
$trad["OPERATOR"]["record"]	         	                = "سجل";
$trad["OPERATOR"]["new_instance"]	         	        = "إضافة"; 
$trad["OPERATOR"]["qedit_new"]	         	                = "إنشاء قائمة من";              
$trad["OPERATOR"]["qedit_update"]	         	        = "تعديل سريع على سجلات نتائج البحث";   
$trad["OPERATOR"]["other_search"]	         	        = "الرجوع إلى شاشة"; 
$trad["OPERATOR"]["back_to_last_form"]        	                = "رجوع إلى الشاشة السابقة";
$trad["OPERATOR"]["new_search"]	         	                = "عملية بحث جديدة";
$trad["OPERATOR"]["show"]	         	                = "الإطلاع";
$trad["OPERATOR"]["records_updated"]	         	        = "تم تعديل";
$trad["OPERATOR"]["record(s)"]	                 	        = "من السجلات";
$trad["OPERATOR"]["qedit_some_records"]	                 	        = "إدارة البيانات المرجعية لـ";
$trad["OPERATOR"]["save_with_sucess"]         	                = "تم بنجاح حفظ";
$trad["OPERATOR"]["changes"]         	                = "التعديلات";
$trad["OPERATOR"]["--changes"]         	                = "التعديلات";
$trad["OPERATOR"]["no_update_found"]	         	                = "لا يوجد تعديلات";
$trad["OPERATOR"]["other_functions"]	         	                = "وظائف أخرى";

$trad["OPERATOR"]["MY-FILES"]         	                = "إضافة مرفقات جديدة";
$trad["OPERATOR"]["EDIT-MY-FILES"]         	        = "إدارة مرفقاتي";
$trad["OPERATOR"]["ETC"]         	                = "الخ";
$trad["OPERATOR"]["OBJECT-WITHOUT-NAME"]         	                = "[كيان بدون اسم]";

$trad["OPERATOR"]["HIDDEN-MENU"]         	                = "قائمة مخفية";





?>
