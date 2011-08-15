setTimeout(function() {
    if ($('#mod-jirafe') == undefined){
        $('messages').insert ("<ul class=\"messages\"><li class=\"error-msg\">We're unable to connect with the Jirafe service for the moment. Please wait a few minutes and refresh this page later.</li></ul>");        
    }        
}, 1500);