$(function() {

            /*------Jquery Accordion Script Start-------*/
            var icons = {
                header: "ui-icon-circle-arrow-e",
                activeHeader: "ui-icon-circle-arrow-s"
            };
            $("#accordion").accordion({
                icons: icons,
                heightStyle: "content",
                collapsible: true,
                active: false
            });
            $("#toggle").button().click(function () {
                if ($("#accordion").accordion("option", "icons")) {
                    $("#accordion").accordion("option", "icons", null);
                } else {
                    $("#accordion").accordion("option", "icons", icons);
                }
            });
            /*------Jquery Accordion Script End-------*/

/*   $('.searchbtn').click(function(){
	  $('.searchtxt').slideToggle("slow");
  });
 */
 $('#slider').nivoSlider({
	 effect: 'random',
	 pauseTime: 4000,
	 animSpeed: 1200
 });

 $('.newsticker').newsTicker({
	    row_height: 45,
	    speed: 800,
	    duration: 4000,
	    pauseOnHover: 1,
	    prevButton:  $('#prev-button'),
	    nextButton:  $('#next-button'),
	        
	});
    
});
