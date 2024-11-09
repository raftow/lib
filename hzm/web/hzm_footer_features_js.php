<script>

function switchRun(cl, md, swc_id, swc_col)
{
    console.log('switch running before ajax action on md='+md+' cl='+cl+' id='+swc_id+' col='+swc_col);
    $.ajax({
            type:'POST',
            url:'../lib/api/afw_switcher.php',
            data:{cl:cl, currmod:md, swc_id:swc_id, swc_col:swc_col},
            success: function(data)
            {
                data = data.trimLeft();
                data = data.trimRight();

                console.log('#'+md+'-'+cl+'-'+swc_id+'-'+swc_col+' afw_switcher res = '+data);

                if(data=="SWITCHED-OFF")
                {
                    $('#'+md+'-'+cl+'-'+swc_id+'-'+swc_col).html("<img src='../lib/images/off.png' width='36' heigth='24'>");                                     
                }
                else if(data=="SWITCHED-ON")
                {
                    $('#'+md+'-'+cl+'-'+swc_id+'-'+swc_col).html("<img src='../lib/images/on.png' width='36' heigth='24'>");                                     
                }
                else
                {
                    <?php echo $response_data_format ?>
                    swal("<?php echo $you_dont_have_rights?>["+data+"]");
                }
            }

    });
}

$(document).ready(function(){
<?php
  if((!$objme) or (!$objme->isAdmin())) $response_data_format = "data = '';\n";
  else $response_data_format = "";
  if($ivviewer_activate) 
  {
?>  
     $('.gallery-items').on('click', function() {
          $('#overlay')
            .css({backgroundImage: `url(${this.src})`})
            .addClass('open')
            .one('click', function() { $(this).removeClass('open'); });
     });
<?php
  }
?>
    $(".action_lourde").click(function()
                        { 
                                $(".hzm-loader-div").removeClass("hide");            
                                $(".alert-dismissable").fadeOut().remove();            
                        }
    ); 
    
    $("form.form_lourde" ).submit(function( event ) {
      $(".hzm-loader-div").removeClass("hide"); 
      return true;
    });
    // swal("Hello world!");
    $('[data-toggle="tooltip"]').tooltip();
    // $('[data-toggle="tooltip-error"]').tooltip({classes: {"ui-tooltip": "ui-tooltip-error"}});
    $('[data-toggle="tooltip-error"]').tooltip({tooltipClass: "highlight-error"});
    

    //$('[data-toggle="tooltip-error"]').tooltip("option", "classes.ui-tooltip", "highlight-error" );
    $("a").tooltip();

    
    
    $(".HzmModal-close-icon").click(function()
          {
               $("#tipofday").fadeOut().remove();
          }
       );
    $(".trash").click(function()
       {
            var del_id= $(this).attr("id");
            var cl= $(this).attr("cl");
            var md= $(this).attr("md");
            var lbl= $(this).attr("lbl");
            var div_to_del = $(this).attr("div_to_del");
            var lvl= $(this).attr("lvl");
            if(lvl==null) lvl = 2;
            
            var $ele = null;
            //alert("lvl = "+lvl);
            if((div_to_del != "") && (div_to_del != null) && (div_to_del != "undefined"))
            {
                  //alert(div_to_del);
                  $ele = $("#"+div_to_del);
                  
            }
            else
            {
                    if(lvl==2) $ele = $(this).parent().parent();
                    if(lvl==3) $ele = $(this).parent().parent().parent();
                    if(lvl==4) $ele = $(this).parent().parent().parent().parent();
                    if(lvl==5) $ele = $(this).parent().parent().parent().parent().parent();
                    if(lvl==6) $ele = $(this).parent().parent().parent().parent().parent().parent();
                    if(lvl==7) $ele = $(this).parent().parent().parent().parent().parent().parent().parent();
                    if(lvl==8) $ele = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
            }
            $(".alert.messages").fadeOut().remove();
            swal({
                  title: "<?=$are_you_sure?> : "+lbl,
                  text: "<?=$once_deleted?>", // +div_to_del+" / "+$ele.id,
                  icon: "warning",
                  buttons: true,
                  dangerMode: true,
                })
                .then((willDelete) => {
                  if (willDelete) 
                  {
                    $.ajax({
                                type:'POST',
                                url:'../lib/api/afw_trash.php',
                                data:{cl:cl, currmod:md, del_id:del_id},
                                success: function(data)
                                {
                                    data = data.trimLeft();
                                    data = data.trimRight();

                                    if(data=="DELETED")
                                    {
                                        if($ele != null) $ele.fadeOut().remove();
                                        else $("#"+div_to_del).fadeOut().remove();
                                        
                                        swal("<?php echo $has_been_deleted?> : "+lbl, {
                                              icon: "success",
                                            });
                                    }
                                    else
                                    {
                                        <?php echo $response_data_format ?>
                                            swal("<?php echo $you_dont_have_rights_todelete?>["+data+"]");
                                    }
                                }
        
                        })
                    
                    
                  } 
                  else 
                  {
                    swal("<?=$safely_cancelled?>");
                  }
                });
       }
       );

       $(".switcher").click(function()
        {
            var swc_id= $(this).attr("oid");
            var swc_col= $(this).attr("col");
            var cl= $(this).attr("cl");
            var md= $(this).attr("md");
            var ttl = $(this).attr("ttl");
            var txt = $(this).attr("txt");
            
            
            if((ttl != '') && (txt != ''))
            {
                $(".alert.messages").fadeOut().remove();
                swal({
                      title: ttl,
                      text: txt,
                      icon: "warning",
                      buttons: true,
                      dangerMode: false,
                    })
                    .then((willSwitch) => {
                          if(willSwitch)
                          {
                            switchRun(cl, md, swc_id, swc_col);
                          }
                    });
            }
            else 
            {              
              console.log('switch will run because no swal texts defined');
              switchRun(cl, md, swc_id, swc_col);
            }
            
            
            
       }
       );

       $(".switcher-btn").click(function()
       {
          var swc_col = $(this).attr("for");

          old_val = $('#'+swc_col).val();
          if(old_val == 'N') new_val = 'Y'; else new_val = 'N';
          console.log('swc_col='+swc_col+' old_val='+old_val+' new_val='+new_val);
          $('#'+swc_col).val(new_val);
          if(new_val == 'N')
          {
              $('#img-'+swc_col).attr("src", '../lib/images/off.png');                                                   
          }
          else
          {
              $('#img-'+swc_col).attr("src", '../lib/images/on.png');                                     
          }
       }
      );

    // Swal.fire({
    //   template: '#swal-my-notification'
    // });
       
});





</script>