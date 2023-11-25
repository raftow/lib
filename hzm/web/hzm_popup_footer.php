<!-- #Footer -->
</CENTER>
</div>

<?
  if($datatable_on) include("../lib/datatable/datatable_js.php");
  
  if($force_close_btn)
  {
?>
<br>
<br>
<center><input type="button" class="closebtn btn" value="غلق الشاشة" onClick="javascript:close_window();"/></center>
<br>
<?
  }
?>

</body></html>