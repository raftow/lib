function detectmob() 
{ 
	if( navigator.userAgent.match(/Android/i)
	|| navigator.userAgent.match(/webOS/i)
	|| navigator.userAgent.match(/iPhone/i)
	|| navigator.userAgent.match(/iPad/i)
	|| navigator.userAgent.match(/iPod/i)
	|| navigator.userAgent.match(/BlackBerry/i)
	|| navigator.userAgent.match(/Windows Phone/i)
	)
	{
    	return true;
	}
	else 
	{
		return false;
	}
}

var stylesheet_directory = '';

$(document).ready(function() {
		
$(".topmenu").prepend('<a id="mob-menu"><img src="#{facesContext.externalContext.requestContextPath}/resources/images/m-menu.png" />الذهاب إلي ...</a>');
$(".topmenu > ul").addClass('navbar-menu');	

	
	$("#mob-menu").click(function(){
		
		//$(".navbar").toggleClass('navbar-height' , 1000);
		
		$(".navbar-menu").slideToggle();	
		
	});
	
	if(!detectmob())
	{
		$(window).resize(function() {
			if($(window).width() >= 990)
			{
				$(".navbar-menu").show();						
			}
			else
			{	
				$(".navbar-menu").slideUp();
			}
		});
	}
	
///the following line to check if li has sub menu items/////
$(".navbar-menu > li").has('ul').addClass( "has-sub" );
///the following line to prepen span tag(the arrow) to the li with subitems/////
$(".navbar-menu > li.has-sub").prepend('<span></span>');
	

$(".navbar-menu li.has-sub > span").click(function(){
		
		$(this).toggleClass('ss');
		$(this).parent('li').toggleClass('active-mobile');
		$(this).parent('li').children('ul').slideToggle();

	});

	
	
// the fllowing to make page scroll down to content start

if ($("h2.pagetitle").length > 0 && $(window).width() <= 768){
  // do something here
  $('html, body').animate({
        //scrollTop: $('h2.pagetitle').offset().top
    }, 'slow');

}

if ($(window).width() <= 768){
	$('.container').prepend('<span class="back-top">Top</span>');
    $('.back-top').fadeOut();
   
}
	
// fade in #back-top
$(function () {
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('.back-top').fadeIn();
        } else {
            $('.back-top').fadeOut();
        }
    });

    // scroll body to 0px on click
    $('.back-top').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 800);
        return false;
    });
}); 
	
});

