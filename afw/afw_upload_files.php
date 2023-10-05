<link href="../pag/assets/css/style.css" rel="stylesheet" />
<?php
    require_once("doc_type.php");
    
    if(!$module_config_token["file_types"]) $module_config_token["file_types"] = "1,2,3,4,5,6";
    
    list($ext_arr, $ft_arr) = DocType::getExentionsAllowed($module_config_token["file_types"]);
    
    $ext_list = implode(", ",$ext_arr);
    $ft_list = implode(", ",$ft_arr);
?>

<!-- HTML -->

<div class="innercontainer">

<h1>تحميل الملفات الجديدة</h1>
<form id="upload" method="post" action="afw_my_upload.php" enctype="multipart/form-data">
			<div id="drop">
				اضغط على   [رفع الملفات] لاختيار الملف أو اسحب الملف مباشرة إلى هذه المنطقة الزرقاء لتحميله
                                <br>
				<a>رفع الملفات</a>
                                <input type="hidden" name="module" value="<?=$MODULE?>" />
				<input type="file" name="upl" multiple />     <br><br>
                                يسمح فقط بالملفات من الأنواع التالية <?=$ext_list?>
                                لأجل أنواع المستندات التالية <?=$ft_list?>
			</div>

			<ul>
				<!-- The file uploads will be shown here -->
			</ul>

		</form>

        
		<!-- JavaScript Includes -->
		<script src="../pag/assets/js/jquery.knob.js"></script>

		<!-- jQuery File Upload Dependencies -->
		<script src="../pag/assets/js/jquery.ui.widget.js"></script>
		<script src="../pag/assets/js/jquery.iframe-transport.js"></script>
		<script src="../pag/assets/js/jquery.fileupload.js"></script>
		
		<!-- Our main JS file -->
		<script src="../pag/assets/js/script.js"></script>
<table style="padding:10px;width:100%">
<tr>
<!--
<td style="padding:10px !important">
        <form name="qedit_updateForm" id="qedit_updateForm" method="post" action="main.php" <?=$target?>>
        <input type="hidden" name="Main_Page" value="afw_mode_qedit.php">
        <input type="hidden" name="cl" value="Afile">
        <input type="hidden" name="currmod" value="pag">
        <table cellspacing="3" cellpadding="1">
        <tbody>
          <tr>
                  <td>
                     <input type="submit" class="yellowbtn btn fright" name="submit" id="submit-form" value="تحديد مواصفات المرفقات التي تم تحميلها مؤخرا">
                  </td>
                  <td>
                     <input type="hidden" size="3" name="newo" value="0">
                  </td>
          </tr>
        </tbody>
        </table>
        <input type="hidden" name="limit" value="200">
        <input type="hidden" name="popup" value="<?=$popup_t?>">
        <input type="hidden" name="ids" value="all">
        <input type="hidden" name="fixm" value="owner_id=<?=$me?>">
        <input type="hidden" name="sel_doc_type_id" value="1">
        <input type="hidden" name="sel_owner_id" value="<?=$me?>">
        <input type="hidden" name="fixmtit" value="تحديد مواصفات الملفات التي تم تحميلها مؤخرا">
        <input type="hidden" name="fixmdisable" value="1">
        <input type="hidden" name="not_found_mess" value="لا يوجد ملفات تم تحميلها مؤخرا وبحاجة لتعديل مواصفاتها">
        
        
        </form>
</td>-->
<?
$codeme = substr(md5("code".$me),0,8);
?>

<td style="padding:10px !important">   
        <form name="qedit_updateForm" id="qedit_updateForm" method="post" action="afw_edit_my_files.php" <?=$target?>>
                <input type="hidden" name="x" value="<?=$me?>">
                <input type="hidden" name="y" value="<?=$codeme?>">
                <table cellspacing="3" cellpadding="1"  style="width:100%">
                <tbody>
                  <tr>
                          <td style="text-align: center;">
                             <input type="submit" class="nice_button nice_blue" name="submit" id="submit-form" value="إدارة  مواصفات المرفقات التي تم تحميلها" style="max-width: 600px !important;width: 600px;">
                          </td>
                          <td>
                             <input type="hidden" size="3" name="newo" value="0">
                          </td>
                  </tr>
                </tbody>
                </table>
        </form>
</td>

</tr>
</table>
</div>

