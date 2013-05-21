jQuery(document).ready(function(){
    divButton.initialize();
})
var divButton = new Object();


divButton = {

    initialize: function(){
       $('.divButton').each(function(){
           if($(this).attr('rel').length != 0){
                $(this).click(function(){
                   window.location = $('body').attr('rel') + $(this).attr('rel');
                });
           }
       });
    }


}


