jQuery(document).ready(function(){
    contactForm.initialize();
})
var contactForm = new Object();

contactForm.lastValue = '';
contactForm = {

    initialize: function(){
        var errorList = '';
        $('.errors').parent().parent().find(':first-child').css('color','#E11B22');

        $('.errors').parent().parent().find(':first-child').children('label').each(function(){
            errorList += '*' + $(this).html() + '<br/>';
        });
        if(errorList != ""){
            $('.contactFormModule_errors').show();
            $('#contactFormsModule_errorFields').html(errorList);
            $('.errors').hide();
        }
    }


}


