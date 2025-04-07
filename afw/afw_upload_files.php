<link href="../lib/assets/css/style.css" rel="stylesheet" />
<?php
    if(!$MODULE) throw new AfwRuntimeException("MODULE var should be defined for file uploads");

    $file_types = AfwSession::config("$MODULE-file_types", AfwSession::config("file_types", [10,13]));
    if((!$file_types) or (count($file_types)==0)) throw new AfwRuntimeException("file_types for $MODULE is to be defined for file uploads process");
    list($ext_arr, $ft_arr) = DocType::getExentionsAllowed($file_types);
    
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
		<script src="../lib/assets/js/jquery.knob.js"></script>

		<!-- jQuery File Upload Dependencies -->
		<script src="../lib/assets/js/jquery.ui.widget.js"></script>
		<script src="../lib/assets/js/jquery.iframe-transport.js"></script>
		<script src="../lib/assets/js/jquery.fileupload.js"></script>
		
		<!-- Our main JS file -->
		<script src="../lib/assets/js/script.js"></script>
<table style="padding:10px;width:100%">
<tr>
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

