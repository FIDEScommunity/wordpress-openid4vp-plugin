( function ( $ ) {
    var POLL_INTERVAL_MS = 2000;

    function pollStatus() {
        $.post(
            my_ajax_obj.ajax_url,
            {
                action: 'universal_openid4vp_poll_status_ajax',
                nonce: my_ajax_obj.nonce,
                current: window.location.href,
            },
            function ( response ) {
                if ( response && response.successUrl ) {
                    window.location = response.successUrl;
                    return;
                }
                setTimeout( pollStatus, POLL_INTERVAL_MS );
            },
            'json'
        );
    }

    $( document ).ready( pollStatus );
} )( jQuery );
