<?php 
        list($code_error, $text_error) = explode(":", $error_message);
        $code_error = substr(md5(trim($code_error)),0,5);
        $show_technical_details = "show";
        if(!AfwSession::config("MODE_DEVELOPMENT",false))
        {
                $show_technical_details = "hide";
        }
?>
<style>
body{
        font-family:tahoma;
}
.body_front_error {
        float: right;
        width: 60%;
        display: block;
        margin-top: 55px;
        margin-left: 20%;
        margin-right: 20%;
        background-color: red;
        color: white;
        padding: 10px;
        margin-bottom: 80px;
}    

.body_front_error>p {
        color: white;
}

table.dataTable {
    width: 90%;
    margin: 0 auto;
    clear: both;
    border-collapse: separate;
    border-spacing: 0;
    border-style: solid;
    border-color: rgba(244, 248, 250, 0.74);
    background-color: rgba(255, 255, 255, 0.42);
    box-shadow: 5px 6px 15px #d8e4f0a6;
    border-width: 1px;
}

table.dataTable thead th, table.dataTable tfoot th {
    font-weight: bold;
    background-color: rgba(249, 253, 255, 0.54);
}
table.dataTable thead th, table.dataTable thead td {
    padding: 10px 10px 6px 18px;
    border-bottom: 1px solid rgb(135, 145, 149);
    /*font-size: 13px !important;*/
    text-align: right;
}
table.dataTable thead th:active, table.dataTable thead td:active {
	outline: none
}
table.dataTable tfoot th, table.dataTable tfoot td {
	padding: 10px 4px 6px 18px;
	border-top: 1px solid rgba(41, 146, 195, 0.69);
        font-size: 13px !important;
        text-align: right;
}
table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc {
	cursor: pointer;
	*cursor: hand
}
table.dataTable thead .sorting, table.dataTable thead .sorting_asc, table.dataTable thead .sorting_desc, table.dataTable thead .sorting_asc_disabled, table.dataTable thead .sorting_desc_disabled {
	background-repeat: no-repeat;
	background-position: center right
}
table.dataTable thead .sorting {
	background-image: url("../../lib/images/sort_both.png");
        background-position-x: 5px;
}
table.dataTable thead .sorting_asc {
	background-image: url("../../lib/images/sort_asc.png");
        background-position-x: 5px;
}
table.dataTable thead .sorting_desc {
	background-image: url("../../lib/images/sort_desc.png");
        background-position-x: 5px;
}
table.dataTable thead .sorting_asc_disabled {
	background-image: url("../../lib/images/sort_asc_disabled.png");
        background-position-x: 5px;
}
table.dataTable thead .sorting_desc_disabled {
	background-image: url("../../lib/images/sort_desc_disabled.png");
        background-position-x: 5px;
}
table.dataTable tbody tr {
    background-color: rgba(228, 240, 252, 0.27);
}
table.dataTable tbody tr.selected {
	background-color: #B0BED9
}
table.dataTable tbody th, table.dataTable tbody td {
	padding: 8px 6px;
        vertical-align: middle;
        text-align: right;
}
table.dataTable.row-border tbody th, table.dataTable.row-border tbody td, table.dataTable.display tbody th, table.dataTable.display tbody td {
	border-top: 1px solid rgba(46, 163, 232, 0.35);
}
table.dataTable.display tbody tr {
    text-align: center;
}

table.dataTable.row-border tbody tr:first-child th, table.dataTable.row-border tbody tr:first-child td, table.dataTable.display tbody tr:first-child th, table.dataTable.display tbody tr:first-child td {
	border-top: none
}
table.dataTable.cell-border tbody th, table.dataTable.cell-border tbody td {
	border-top: 1px solid #ddd;
	border-right: 1px solid #ddd
}
table.dataTable.cell-border tbody tr th:first-child, table.dataTable.cell-border tbody tr td:first-child {
	border-left: 1px solid #ddd
}
table.dataTable.cell-border tbody tr:first-child th, table.dataTable.cell-border tbody tr:first-child td {
	border-top: none
}



table.display.dataTable tbody tr.odd td table tr td {
         background-color: rgba(236, 246, 251, 0.49);    
}

table.dataTable.stripe tbody tr.odd, table.dataTable.display tbody tr.odd {
    background-color: rgb(255, 255, 255);
}
table.dataTable.stripe tbody tr.odd.selected, table.dataTable.display tbody tr.odd.selected {
	background-color: #acbad4
}
table.dataTable.hover tbody tr:hover, table.dataTable.display tbody tr:hover {
	background-color: rgba(206, 240, 255, 0.15)
}
table.dataTable.hover tbody tr:hover.selected, table.dataTable.display tbody tr:hover.selected {
	background-color: #aab7d1
}

</style>
<div class="body_front_error">     
        <p>
                المعذرة عميلنا العزيز، حصل خطأ غير متوقع.
                <br>يمكنك التواصل مع فريق الدعم الفني أو إعادة المحاولة بعد قليل        
                <br> رمز الخطأ : <?php echo $code_error ?>
        </p>
        <p class="tech <?php echo $show_technical_details?>">
                <?php echo $error_message?>
                <br>                
        </p>
        <div class="back_tr tech <?php echo $show_technical_details?>">
                Error details : <br>
                <?php echo $error_details;?><br>
                <?php echo _back_trace();?>
        </div>
</div>