// jQuery(document).ready(function($) { 	   //wrapper
//     $("#openid4vp_qrImage").loaded(function() { 		   //event
//         // var this2 = this; 		           //use in callback
//         // $.post(my_ajax_obj.ajax_url, { 	   //POST request
//         //     _ajax_nonce: my_ajax_obj.nonce, //nonce
//         //     action: "my_tag_count",        //action
//         //     title: this.value 	           //data
//         // }, function(data) {		           //callback
//         //     this2.nextSibling.remove();    //remove the current title
//         //     $(this2).after(data); 	       //insert server response
//         // });
//         console.log("qr code loaded")
//     });
// });

(function($){
    $(document).ready( function() {
        // Call pollStatus for the first time
        pollStatus();
    });
    // Function to poll the status
    var pollStatus = function() {
        console.log("Poll status")
        // Prepare the data to send.
        // The "action" is the name of the action hook to trigger.
        // Anything else is data that we want to pass to the PHP function.
        // Here, I am adding the text of the #votes element
        var data = {
            action: "universal_openid4vp_poll_status_ajax",
            nonce: my_ajax_obj.nonce,
            current: window.location.href
        };
        // Send a POST request to the ajaxurl (WordPress variable), using the data
        // we prepared above, and running the below function when we receive the
        // response. The last parameter tells jQuery to expect a JSON response
        $.post( my_ajax_obj.ajax_url, data, function( response ) {
            console.log(response);

            if (response.successUrl) {
                window.location = response.successUrl;
            }
            // var votes = response.votes;
            // $('#votes').text( votes );
            // Wait 2 seconds, and run the function again
            setTimeout( pollStatus, 2000 );
        }, 'json' );
    };
})(jQuery);
