(function($){
    $(document).ready(function() {
        $("#org-wallet-submit").on("click", function(e) {
            e.preventDefault();
            var walletUrl = $("#org-wallet-url").val();

            var data = {
                action: "universal_openid4vp_presentation_exchange_ajax",
                nonce: my_ajax_obj.nonce,
                walletUrl: walletUrl
            };

            $.post(my_ajax_obj.ajax_url, data, function(response) {
                if (response && response.request_uri) {
                    window.location = response.request_uri;
                }
            }, "json");
        });
    });
})(jQuery);
