function prepareRatingWithStars(numberOfPossibleAnswers) {
    //get all stars
    var starElements = $('.star-rating')
        //Define the animation on mouseover
        .on("mouseenter", function () {
            var thisnum = $(this).data('star');
            var inputname = $(this).data('inputname');
            
            $('#rating-label-'+inputname).html(starlabel(inputname, thisnum));
            $('#rating-label-'+inputname).removeClass('hover-star-1');
            $('#rating-label-'+inputname).removeClass('hover-star-2');
            $('#rating-label-'+inputname).removeClass('hover-star-3');
            $('#rating-label-'+inputname).removeClass('hover-star-4');
            $('#rating-label-'+inputname).removeClass('hover-star-5');
            $('#rating-label-'+inputname).removeClass('hover-star-6');
            $('#rating-label-'+inputname).removeClass('hover-star-7');
            $('#rating-label-'+inputname).removeClass('hover-star-8');
            $('#rating-label-'+inputname).removeClass('hover-star-9');
            $('#rating-label-'+inputname).removeClass('hover-star-10');
            $('#rating-label-'+inputname).addClass('hover-star-'+thisnum);
            //mar the current star
            $(this).addClass("star-drained").addClass("star-hover");
            //add/remove classes from sibling-elements
            $(this).siblings('.star-rating').each(function () {
                //smaller than the chosen and not "no answer" => add class to emphasize them
                if ($(this).data('star') < thisnum) { // && thisnum != numberOfPossibleAnswers
                    $(this).addClass("star-drained");
                } else {
                    $(this).addClass("star-stub");
                }
            });
        })
        //define animation on mouseleave
        .on("mouseleave", function () {
            var thisnum = $(this).data('star');
            var inputname = $(this).data('inputname');
            var thechoice = $('#'+inputname).val();
            var the_starlabel = '---';
            if(thechoice>0) the_starlabel = starlabel(inputname, thechoice)
            $('#rating-label-'+inputname).html(the_starlabel);
            $('#rating-label-'+inputname).removeClass('hover-star-1');
            $('#rating-label-'+inputname).removeClass('hover-star-2');
            $('#rating-label-'+inputname).removeClass('hover-star-3');
            $('#rating-label-'+inputname).removeClass('hover-star-4');
            $('#rating-label-'+inputname).removeClass('hover-star-5');
            $('#rating-label-'+inputname).removeClass('hover-star-6');
            $('#rating-label-'+inputname).removeClass('hover-star-7');
            $('#rating-label-'+inputname).removeClass('hover-star-8');
            $('#rating-label-'+inputname).removeClass('hover-star-9');
            $('#rating-label-'+inputname).removeClass('hover-star-10');
            //remove hover-classes from this element
            $(this).removeClass("star-drained star-hover star-stub");
            //remove the selector classes from the siblings
            $(this).siblings('.star-rating').each(function () {
                $(this).removeClass("star-stub");
                $(this).removeClass("star-drained");
            });
        })
        //define the click-event
        .on("click", function (event) {
            var thischoice = $(this).data('star');
            var inputname = $(this).data('inputname');
            $('#'+inputname).val(thischoice);
            $('#rating-label-'+inputname).html(starlabel(inputname, thischoice));
            $('#rating-label-'+inputname).removeClass('star-1');
            $('#rating-label-'+inputname).removeClass('star-2');
            $('#rating-label-'+inputname).removeClass('star-3');
            $('#rating-label-'+inputname).removeClass('star-4');
            $('#rating-label-'+inputname).removeClass('star-5');
            $('#rating-label-'+inputname).removeClass('star-6');
            $('#rating-label-'+inputname).removeClass('star-7');
            $('#rating-label-'+inputname).removeClass('star-8');
            $('#rating-label-'+inputname).removeClass('star-9');
            $('#rating-label-'+inputname).removeClass('star-10');
            $('#rating-label-'+inputname).addClass('star-'+thischoice);
            //toggle the em-action on the hidden input
            /*
            answersList.find("input[type=radio]").prop('checked', false);
            answersList.find("input[value='" + thischoice + "']").prop('checked', true).trigger('change');
            */
            //clean up classes
            $(this).siblings('.star-rating').removeClass("star-thisrated").removeClass("star-rated").removeClass("star-rated-on");
            //mark the chosen star
            $(this).addClass("star-rated").addClass("star-thisrated").addClass("star-rated-on");
            //iterate through the siblings to mark the stars lower than the current
            $(this).siblings('.star-rating').each(function () {
                if ($(this).data("star") < thischoice) {
                    $(this).addClass("star-rated").addClass("star-rated-on");
                }
            });
            // if cancel, remove all classes
            /*
            if ($(this).hasClass('star-cancel')) {
                $(this).siblings('.star-rating').removeClass("star-rated-on").removeClass("star-rated");
                answersList.find('.noanswer-item').find("input[type=radio]").prop('checked', true).trigger('change');
            }*/

        });

    //hide the standard-items
    //answersList.addClass("starred-list visually-hidden");
}
