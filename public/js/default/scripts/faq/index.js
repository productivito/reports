jQuery(document).ready(function() {


    //$('li.faq .faqanswer').not(':first').slideToggle('fast');
    $('li.faq .faqquestion').live('click', function() {
        toggle(this);
    });
	

    function toggle(elt) {
        $('.active').toggleClass('');
        $('.faqanswer').slideUp('fast');
        $(elt).toggleClass('active');

        $(elt).siblings('.faqanswer').slideToggle('fast',function(){});
    }
});


