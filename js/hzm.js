  'use strict';
  /**
   * @return {number}
   */
  function validateSaudiID(id) {
    
    id = id.trim();
    if (isNaN(parseInt(id))) {
      return -1;
    }
    if (id.length !== 10) {
      return -1;
    }
    var type = id.substr(0, 1);
    if (type !== '2' && type !== '1') {
      return -1;
    }
    var sum = 0;
    for (var i = 0; i < 10; i++) {
      if (i % 2 === 0) {
        var ZFOdd = String('00' + String(Number(id.substr(i, 1)) * 2)).slice(-2);
        sum += Number(ZFOdd.substr(0, 1)) + Number(ZFOdd.substr(1, 1));
      } else {
        sum += Number(id.substr(i, 1));
      }

    }
    return (sum % 10 !== 0) ? -1 : type;
  }

  function containsNumbers(str) {
    const numbers = /\d/;
    return numbers.test(str);
  }

  function containsSpecialChars(str) {
    const specialChars = /[`!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?~]/;
    return specialChars.test(str);
  }

  function isCorrectName(name)
  {
    if(containsNumbers(name)) return false;
    if(containsSpecialChars(name)) return false;
    return true;
  }

  function isCorrectMobileNumber(mobile)
  {
    var regex = new RegExp(/^(05)([0-9]{8})$/);
    return regex.test(mobile);
  }

  function isCorrectHijriDate(hijri)
  {
    var regex = new RegExp(/^(1)(3|4|5)([0-9]{2})-(01|02|03|04|05|06|07|08|09|10|11|12)-(01|02|03|04|05|06|07|08|09|10|11|12|13|14|15|16|17|18|19|20|21|22|23|24|25|26|27|28|29|30)$/);
    return regex.test(hijri);
  }

  function isCorrectEmail(email)
  {
    var regex = new RegExp(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
    return regex.test(email.toLowerCase());
  }

  function toggleHzmBtn(inputname,listVal, listCod, listCodOrder, listClass, nbVals)
  {
    console.log("input name="+inputname);
    // $("input#"+inputname).val(55);
    var ival = $("input#"+inputname).val();
    console.log("input value : ival(input#"+inputname+")="+ival);
    console.log(listCodOrder);
    console.log("listCodOrder["+ival+"]="+listCodOrder[ival]);    
        var ord = listCodOrder[ival];
        // alert("ord="+ord);
        var neword = parseInt(ord) + 1;
        // alert("neword="+neword);
        if(neword >= nbVals) neword = 0;
        var css_class = "btn btn-secondary";
        var display = 'ord'+neword;
        
        display = listVal[neword];
        css_class = 'toggle-hzm-btn '+listClass[neword];
        
        /* 
        if(listname=='list1')
        {
            if(newval==1) css_class = "btn-danger";
            if(newval==2) css_class = "btn-warning";
            if(newval==3) css_class = "btn-info";
            if(newval==4) css_class = "btn-success";
            if(newval==5) css_class = "btn-primary";
            if(newval==6) css_class = "btn-secondary";
        
            if(newval==1) display = 'ضعيف';
            if(newval==2) display = 'مقبول';
            if(newval==3) display = 'جيد';
            if(newval==4) display = 'جيد جدا';
            if(newval==5) display = 'ممتاز';
        }
        */
        
        $("#btn_"+inputname).text(display);
        /*
        $("#btn_"+inputname).removeClass("btn-danger");
        $("#btn_"+inputname).removeClass("btn-warning");
        $("#btn_"+inputname).removeClass("btn-info");
        $("#btn_"+inputname).removeClass("btn-success");
        $("#btn_"+inputname).removeClass("btn-primary");
        $("#btn_"+inputname).removeClass("btn-secondary");
        $("#btn_"+inputname).addClass(css_class);*/
        $("#btn_"+inputname).attr('class', css_class);
        $("#"+inputname).val(listCod[neword]);
  }

  function open_loading()
  {
     $(".loader").css('visibility', 'visible');
     $(".loader_container").css('visibility', 'visible');
     
     return true;
  }
