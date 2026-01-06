<?php
class AfwHtmlFooterJsHelper
{
  public static function render($objme, $lang, $options = [])
  {
    if ($objme) {
      $are_you_sure = $objme->translateMessage('ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_RECORD', $lang);
      $once_deleted = $objme->translateMessage('ONCE_DELETED_YOU_WILL_NOT_BE_ABLE_TO_GO_BACK', $lang);
      $once_moved = $objme->translateMessage('This operation can affect the process work', $lang);
      $are_you_sure_move_up = $objme->translateMessage('Are you sure you want to move up this item ?', $lang);
      $are_you_sure_move_down = $objme->translateMessage('Are you sure you want to move down this item ?', $lang);
      $has_been_deleted = $objme->translateMessage('THE_FOLLOWING_RECORD_HAS_BEEN_DELETED', $lang);
      $you_dont_have_rights_todelete = $objme->translateMessage('CANT_DELETE_THE_ROW', $lang);
      $you_dont_have_rights = $objme->translateMessage('CANT_DO_THIS', $lang);
      $safely_cancelled = $objme->translateMessage('DELETE_HAVE_BEEN_SAFELY_CANCELLED', $lang);
      $move_safely_cancelled = $objme->translateMessage('Move action canceled safely', $lang);
    }

    if ((!$objme) or (!$objme->isAdmin()))
      $response_data_format = "mess = '';\n";
    else
      $response_data_format = 'mess = data.message;\n';

    ob_start();
?>
    <script>
      function save_popup(mod, cls, idobj, col, val) {
        $.ajax({
          type: 'POST',
          url: '../lib/api/afw_col_saver.php',
          data: {
            cls: cls,
            currmod: mod,
            idobj: idobj,
            col: col,
            val: val,
            lang: '<?php echo $lang; ?>'
          },
          dataType: 'json',
          success: function(data) {
            console.log('currmod=' + mod + ' cls=' + cls + ' idobj=' + idobj + ' col=' + col + ' val=' + val + ' afw_col_saver res = ', data);
            if (data.status == "success") {
              $("#span-" + mod + "-" + cls + "-" + idobj + "-" + col).text(data.aff);
              save_popup_done_on(mod, cls, idobj, col, val, data.aff);
            } else {
              mess = '';
              <?php echo $response_data_format ?>
              swal("<?php echo $you_dont_have_rights ?> " + mess); // 
              return [false, null];
            }
          }

        });
      }

      function moveRun(cl, md, mv_id, mv_ord, mv_sens, limitd) {
        console.log('move running before ajax action on md=' + md + ' cl=' + cl + ' id=' + mv_id + ' ord=' + mv_ord + ' sens=' + mv_sens + ' limitd=' + limitd);
        $.ajax({
          type: 'POST',
          url: '../lib/api/afw_mover.php',
          data: {
            cl: cl,
            currmod: md,
            mv_id: mv_id,
            mv_sens: mv_sens,
            limitd: limitd
          },
          success: function(data) {
            data = data.trimLeft();
            data = data.trimRight();

            console.log('#md=' + md + ' cl=' + cl + ' id=' + mv_id + ' sens=' + mv_sens + ' afw_mover res = ' + data);
            arr_data = data.split("-");
            status = arr_data[0];
            msens = arr_data[1];
            switched_id = arr_data[2];
            if ((status == "MOVED") && (msens == "UP")) {
              ord_moved = mv_ord;
              ord_switched = parseInt(mv_ord) - 1;

              console.log('mover-up-' + mv_id + ' attr ord was ' + $("#mover-up-" + mv_id).attr("ord"));
              $("#mover-up-" + mv_id).attr("ord", ord_switched);
              console.log('mover-up-' + mv_id + ' attr ord setted to ' + $("#mover-up-" + mv_id).attr("ord"));

              console.log('mover-up-' + switched_id + ' attr ord was ' + $("#mover-up-" + switched_id).attr("ord"));
              $("#mover-up-" + switched_id).attr("ord", ord_moved);
              console.log('mover-up-' + switched_id + ' attr ord setted to ' + $("#mover-up-" + switched_id).attr("ord"));

              $("#mover-down-" + mv_id).attr("ord", ord_switched);
              $("#mover-down-" + switched_id).attr("ord", ord_moved);
              //

              tr_moved = 'tr-object-' + ord_moved;
              tr_switched = 'tr-object-' + ord_switched;




              console.log('order-id-' + mv_id + ' was ' + $('#order-' + mv_id).html());
              $('#order-' + mv_id).html(ord_switched + ' ');
              console.log('order-id-' + mv_id + ' setted to ' + $('#order-' + mv_id).html());

              console.log('order-id-' + switched_id + ' was ' + $('#order-' + switched_id).html());
              $('#order-' + switched_id).html(ord_moved + ' ');
              console.log('order-id-' + switched_id + ' setted to ' + $('#order-' + switched_id).html());

              html_moved = $("#" + tr_moved).html();
              html_switched = $("#" + tr_switched).html();

              $("#" + tr_moved).html(html_switched);
              $("#" + tr_switched).html(html_moved);
              // location.reload();
              move_triggers();
            } else if ((status == "MOVED") && (msens == "DOWN")) {
              ord_moved = mv_ord;
              ord_switched = parseInt(mv_ord) + 1;

              $("#mover-up-" + mv_id).attr("ord", ord_switched);
              $("#mover-up-" + switched_id).attr("ord", ord_moved);
              $("#mover-down-" + mv_id).attr("ord", ord_switched);
              $("#mover-down-" + switched_id).attr("ord", ord_moved);

              //

              tr_moved = 'tr-object-' + ord_moved;
              tr_switched = 'tr-object-' + ord_switched;



              console.log('order-' + mv_id + ' was ' + $('#order-' + mv_id).html());
              $('#order-' + mv_id).html(ord_switched + ' ');
              console.log('order-' + mv_id + ' setted to ' + $('#order-' + mv_id).html());

              console.log('order-' + switched_id + ' was ' + $('#order-' + switched_id).html());
              $('#order-' + switched_id).html(ord_moved + ' ');
              console.log('order-' + switched_id + ' setted to ' + $('#order-' + switched_id).html());

              html_moved = $("#" + tr_moved).html();
              html_switched = $("#" + tr_switched).html();

              $("#" + tr_moved).html(html_switched);
              $("#" + tr_switched).html(html_moved);
              // location.reload();
              move_triggers();

            } else {
              <?php echo $response_data_format ?>
              swal("<?php echo $you_dont_have_rights ?>[" + data + "]"); // 
            }
          }

        });
      }

      function switchRun(cl, md, swc_id, swc_col) {
        console.log('switch running before ajax action on md=' + md + ' cl=' + cl + ' id=' + swc_id + ' col=' + swc_col);
        $.ajax({
          type: 'POST',
          url: '../lib/api/afw_switcher.php',
          data: {
            cl: cl,
            currmod: md,
            swc_id: swc_id,
            swc_col: swc_col
          },
          success: function(data) {
            data = data.trimLeft();
            data = data.trimRight();

            console.log('#' + md + '-' + cl + '-' + swc_id + '-' + swc_col + ' afw_switcher res = ' + data);

            if (data == "SWITCHED-OFF") {
              $('#' + md + '-' + cl + '-' + swc_id + '-' + swc_col).html("<img src='../lib/images/off.png' width='30' heigth='20'>");
              switch_done_on(md, cl, swc_id, swc_col, 'N');
            } else if (data == "SWITCHED-ON") {
              $('#' + md + '-' + cl + '-' + swc_id + '-' + swc_col).html("<img src='../lib/images/on.png' width='30' heigth='20'>");
              switch_done_on(md, cl, swc_id, swc_col, 'Y');
            } else if (data == "SWITCHED-OFN") {
              $('#' + md + '-' + cl + '-' + swc_id + '-' + swc_col).html("<img src='../lib/images/ofn.png' width='30' heigth='20'>");
              switch_done_on(md, cl, swc_id, swc_col, 'W');
            } else {
              <?php echo $response_data_format ?>
              swal("<?php echo $you_dont_have_rights ?>[" + data + "]"); //                     
            }
          }

        });
      }

      function move_triggers() {
        $(".move-up").unbind('click');
        $(".move-up").click(function() {
          var mv_id = $(this).attr("oid");
          var mv_ord = $(this).attr("ord");
          var mv_sens = -1;
          var cl = $(this).attr("cl");
          var md = $(this).attr("md");
          var lbl = $(this).attr("lbl");
          var bswal = $(this).attr("bswal");
          var limitd = $(this).attr("limitd");
          $(".alert.messages").fadeOut().remove();
          if (bswal == 1) {
            swal({
                title: "<?php echo $are_you_sure_move_up ?> : " + lbl,
                text: "<?php echo $once_moved ?>", // +div_to_del+" / "+$ele.id,
                icon: "warning",
                buttons: true,
                dangerMode: true,
              })
              .then((willMove) => {
                if (willMove) {
                  moveRun(cl, md, mv_id, mv_ord, mv_sens, limitd);
                } else {
                  swal("<?php echo $move_safely_cancelled ?>");
                }
              });
          } else {
            moveRun(cl, md, mv_id, mv_ord, mv_sens, limitd);
          }

        });

        $(".move-down").unbind('click');
        $(".move-down").click(function() {
          var mv_id = $(this).attr("oid");
          var mv_ord = $(this).attr("ord");
          var mv_sens = +1;
          var cl = $(this).attr("cl");
          var md = $(this).attr("md");
          var lbl = $(this).attr("lbl");
          var bswal = $(this).attr("bswal");
          var limitd = $(this).attr("limitd");
          $(".alert.messages").fadeOut().remove();
          if (bswal == 1) {
            swal({
                title: "<?php echo $are_you_sure_move_down ?> : " + lbl,
                text: "<?php echo $once_moved ?>", // +div_to_del+" / "+$ele.id,
                icon: "warning",
                buttons: true,
                dangerMode: true,
              })
              .then((willMove) => {
                if (willMove) {
                  moveRun(cl, md, mv_id, mv_ord, mv_sens, limitd);
                } else {
                  swal("<?php echo $move_safely_cancelled ?>");
                }
              });
          } else {
            moveRun(cl, md, mv_id, mv_ord, mv_sens, limitd);
          }
        });
      }

      $(document).ready(function() {
        <?php

        if ($options['ivviewer_activate']) {
        ?>
          $('.gallery-items').on('click', function() {
            $('#overlay')
              .css({
                backgroundImage: `url(${this.src})`
              })
              .addClass('open')
              .one('click', function() {
                $(this).removeClass('open');
              });
          });
        <?php
        }
        ?>

        /*$(document).ready(function() {
            $(".hasCalendarsPicker").datepicker({ 
                    showAnim: "fold",
                    dateFormat: "yy-mm-dd",
                    changeMonth: true,
                    changeYear: true,
            <?php echo AfwEditMotor::calendar_translations($lang) ?>
                    });
            });*/

        $("#slog-switcher").click(function() {
          $("#system_log_div").toggleClass("hide");
        });

        $(".action_lourde").click(function() {
          $(".hzm-loader-div").removeClass("hide");
          $(".alert-dismissable").fadeOut().remove();
        });

        $("a.close").click(function() {
          $(this).parent().fadeOut().remove();
        });

        $("img.popup-edit").click(function() {
          var pos = $(this).position();
          // console.log('pos = ', pos);
          // console.log('pos.top = '+pos.top);
          pos_top = pos.top - 140;
          var cls = $(this).attr("cls");
          var mod = $(this).attr("mod");
          var idobj = $(this).attr("idobj");
          var col = $(this).attr("col");
          var val = $(this).attr("val");
          var tit = $(this).attr("tit");
          var parent_container = $(this).attr("parent_container");
          var record = $(this).attr("record");

          $("#hzm-popup-edit-" + col).removeClass("hide");
          $("#hzm-popup-edit-" + col).css({
            top: pos_top,
            position: 'absolute'
          });
          $("#popup-edit-" + col + "-title").html(record);
          $("#popup-edit-" + col + "-sub-title").html(tit);

          $("#popup_edit_cls_" + col).val(cls);
          $("#popup_edit_mod_" + col).val(mod);
          $("#popup_edit_parent_" + col).val(parent_container);
          $("#popup_edit_idobj_" + col).val(idobj);
          $("#popup_edit_" + col).val(val);

        });


        $(".popup-save").click(function() {
          var col = $(this).attr("col");
          var parent_container = $("#popup_edit_parent_" + col).val();
          var cls = $("#popup_edit_cls_" + col).val();
          var mod = $("#popup_edit_mod_" + col).val();
          var idobj = $("#popup_edit_idobj_" + col).val();
          var val = $("#popup_edit_" + col).val();
          save_popup(mod, cls, idobj, col, val);
          $("#" + parent_container).addClass("obsolete");
          $("#tr-object-" + idobj).removeClass("hzm_row_Y");
          $("#tr-object-" + idobj).removeClass("hzm_row_N");
          $("#tr-object-" + idobj).removeClass("hzm_row_W");
          $("#tr-object-" + idobj).addClass("hzm_row_0");

          $(".popup-editor").addClass("hide");
        });

        $(".popup-cancel").click(function() {
          $(".popup-editor").addClass("hide");
        });

        $("form.form_lourde").submit(function(event) {
          $(".hzm-loader-div").removeClass("hide");
          return true;
        });
        // swal("Hello world!");
        $('[data-toggle="tooltip"]').tooltip();
        // $('[data-toggle="tooltip-error"]').tooltip({classes: {"ui-tooltip": "ui-tooltip-error"}});
        $('[data-toggle="tooltip-error"]').tooltip({
          tooltipClass: "highlight-error"
        });


        //$('[data-toggle="tooltip-error"]').tooltip("option", "classes.ui-tooltip", "highlight-error" );
        $("a").tooltip();



        $(".HzmModal-close-icon").click(function() {
          $("#tipofday").fadeOut().remove();
        });
        $(".trash").click(function() {
          var del_id = $(this).attr("id");
          var cl = $(this).attr("cl");
          var md = $(this).attr("md");
          var lbl = $(this).attr("lbl");
          var div_to_del = $(this).attr("div_to_del");
          var lvl = $(this).attr("lvl");
          if (lvl == null) lvl = 2;

          var $ele = null;
          //alert("lvl = "+lvl);
          if ((div_to_del != "") && (div_to_del != null) && (div_to_del != "undefined")) {
            //alert(div_to_del);
            $ele = $("#" + div_to_del);

          } else {
            if (lvl == 2) $ele = $(this).parent().parent();
            if (lvl == 3) $ele = $(this).parent().parent().parent();
            if (lvl == 4) $ele = $(this).parent().parent().parent().parent();
            if (lvl == 5) $ele = $(this).parent().parent().parent().parent().parent();
            if (lvl == 6) $ele = $(this).parent().parent().parent().parent().parent().parent();
            if (lvl == 7) $ele = $(this).parent().parent().parent().parent().parent().parent().parent();
            if (lvl == 8) $ele = $(this).parent().parent().parent().parent().parent().parent().parent().parent();
          }
          $(".alert.messages").fadeOut().remove();
          swal({
              title: "<?php echo $are_you_sure ?> : " + lbl,
              text: "<?php echo $once_deleted ?>", // +div_to_del+" / "+$ele.id,
              icon: "warning",
              buttons: true,
              dangerMode: true,
            })
            .then((willDelete) => {
              if (willDelete) {
                $.ajax({
                  type: 'POST',
                  url: '../lib/api/afw_trash.php',
                  data: {
                    cl: cl,
                    currmod: md,
                    del_id: del_id,
                    lang: '<?php echo $lang ?>'
                  },
                  success: function(data) {
                    data = data.trimLeft();
                    data = data.trimRight();

                    if (data == "DELETED") {
                      if ($ele != null) $ele.fadeOut().remove();
                      else $("#" + div_to_del).fadeOut().remove();

                      swal("<?php echo $has_been_deleted ?> : " + lbl, {
                        icon: "success",
                      });
                    } else {
                      <?php echo $response_data_format ?>
                      swal("<?php echo $you_dont_have_rights_todelete ?>[" + data + "]");
                    }
                  }

                })


              } else {
                swal("<?php echo $safely_cancelled ?>");
              }
            });
        });

        move_triggers();

        $(".switcher").click(function() {
          var swc_id = $(this).attr("oid");
          var swc_col = $(this).attr("col");
          var cl = $(this).attr("cl");
          var md = $(this).attr("md");
          var ttl = $(this).attr("ttl");
          var txt = $(this).attr("txt");


          if ((ttl != '') && (txt != '')) {
            $(".alert.messages").fadeOut().remove();
            swal({
                title: ttl,
                text: txt,
                icon: "warning",
                buttons: true,
                dangerMode: false,
              })
              .then((willSwitch) => {
                if (willSwitch) {
                  switchRun(cl, md, swc_id, swc_col);
                }
              });
          } else {
            console.log('switch will run because no swal texts defined');
            switchRun(cl, md, swc_id, swc_col);
          }



        });

        $(".switcher-btn").click(function() {
          var swc_col = $(this).attr("for");

          old_val = $('#' + swc_col).val();
          if (old_val == 'N') new_val = 'Y';
          else new_val = 'N';
          console.log('swc_col=' + swc_col + ' old_val=' + old_val + ' new_val=' + new_val);
          $('#' + swc_col).val(new_val);
          if (new_val == 'N') {
            $('#img-' + swc_col).attr("src", '../lib/images/off.png');
          } else {
            $('#img-' + swc_col).attr("src", '../lib/images/on.png');
          }
        });

        $('#example').DataTable({
          "pagingType": "full_numbers"
        });


        // Swal.fire({
        //   template: '#swal-my-notification'
        // });

      });
    </script>

<?php
    return ob_get_clean();
  }
}
?>