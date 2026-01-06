jQuery(function ($) {

    $('#payment-hub-test').on('click', function (e) {
        e.preventDefault();

        $('#payment-hub-test-result').text('Test en cours...');

        $.post(PaymentHub.ajax_url, {
            action: 'payment_hub_test_connection',
            nonce: PaymentHub.nonce
        }, function (resp) {

            if (resp.success) {
                $('#payment-hub-test-result').html(
                    '<span style="color:green">✔ Connexion OK</span>'
                );
            } else {
                $('#payment-hub-test-result').html(
                    '<span style="color:red">✖ ' + resp.data + '</span>'
                );
            }
        });
    });

});
