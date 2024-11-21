function iHaveBeenChanged(input)
{
     $('#'+input).addClass('input_changed');
}

function iHaveBeenQEdited(input)
{
     $('#'+input).addClass('input_edited');
     $('#attr_error_'+input).remove();
}

function parsePagFloat(flt)
{
     if(isNaN(parseFloat(flt))) return 0.0;
     else return parseFloat(flt);
     
}

function qedit_col_total(col,rowcount)
{
     tot = 0.0;
     for(i=0;i<rowcount;i++)
     {
           tot += parsePagFloat($('#'+col + '_' + i).val());
     }
     
     $('#'+col + '_total').val(tot);
}



function unify_all_select(col,new_val,new_val_lab,start_row,nb_objs)
{
               for(i=start_row;i<nb_objs;i++)
               {
                    $('select[name="' + col + '_' + i + '"] option[value="'+new_val+'"]').attr('selected','selected');
               }
}

function unify_all_text(col,new_val,start_row,nb_objs)
{
               for(i=start_row;i<nb_objs;i++)
               {
                    $('#'+col + '_' + i).val(new_val);
               }
}



function paste_col(col,start_row)
{
        var clipText = $("#cbrd").val();//window.clipboardData.getData('Text');
        //alert(clipText);
        var tab = clipText.split("\n");
        
        for(i=0;i<tab.length;i++)
        {
            j = i + start_row;
            //alert($('#'+col + '_' + j).val());
            $('#'+col + '_' + j).val(tab[i]);
            //alert($('#'+col + '_' + j).val());
        }
}

function empty_col_text(col,nb_objs)
{
    if(confirm('هل أنت متأكد أنك تريد مسح بيانات العمود بأكمله '))
    {    

       for(i=0;i<nb_objs;i++)
       {
            $('#'+col + '_' + i).val('');
       }
    }   
}
function close_window() 
{
  if (confirm("متأكد أنك تريد غلق الشاشة ?")) 
  {
     window.close();
     window.opener.location.reload(false);
  }
}