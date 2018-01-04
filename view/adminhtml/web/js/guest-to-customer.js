define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm'
], function ($, alert, confirm) {

    window.gustToCustomerButtonClick = function(url, orderId, msg){
        displayMsg(url, orderId, msg); return;
    };

    var displayMsg = function(url, orderId, msg){
        confirm({
            content: msg,
            actions: {
                confirm: function () {
                    postData(url, orderId);
                }
            }
        });
    };


    var postData = function(url, orderId){
        $.ajax({
            url: url,
            type: 'post',
            dataType: 'json',
            data: {
                form_key: FORM_KEY,
                order_id: orderId
            },
            showLoader: true
        }).done(function(response) {

            if (typeof response === 'object') {
                if (response.error) {
                    alert({ title: 'Error', content: response.message });
                } else if (response.ajaxExpired && response.ajaxRedirect) {
                    window.location.href = response.ajaxRedirect;
                }
                else{
                    location.reload();
                }
            }


        });
    }



});