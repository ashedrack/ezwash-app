(function ($) {
    "use strict";
    $(document).ready(function(){
        const {continueToSubmit, SERVICES_MAP} = EzwashHelper.orderDetailsHandler();
        $(document).on('submit', '#editOrderForm', function (e) {
            e.preventDefault();
            let payMethodField = $('#payment_method');
            if(Object.keys(SERVICES_MAP).length === 0){
                swal('Error', 'Cannot create an empty order, please add services', 'error');
                return false;
            }
            if(!payMethodField.val()){
                swal('Error', 'Please select a payment method to continue', 'error');
                return false;
            }
            if(['cash', 'pos'].includes(payMethodField.val())){
                swal({
                    title: 'Payment Confirmation',
                    text: `Please confirm that payment has been received`,
                    buttons: ["Cancel", "Payment Received"]
                }).then(confirmed => {
                    if(confirmed){
                        continueToSubmit($(e.target), this);
                    }
                })
            }
            if(payMethodField.val() === 'card'){
                continueToSubmit($(e.target), this);
            }
        });
    });

})(jQuery);
