(function ($) {
    "use strict";

    $(document).ready(function(){
        const {continueToSubmit, SERVICES_MAP} = EzwashHelper.orderDetailsHandler();
        let orderLocation = $('#order_location');
        if(orderLocation.length > 0) {
            orderLocation.on('change', function (e) {
                console.log(e.target.value);
                $('#order_location_input').val(e.target.value);
            });
        }
        $(document).on('submit', '#createOrderForm', function (e) {
            e.preventDefault();
            let payMethodField = $('#payment_method');
            console.log(SERVICES_MAP);
            if(Object.keys(SERVICES_MAP).length === 0){
                swal('Error', 'Cannot create an empty order, please add services', 'error');
                return false;
            }
            if(orderLocation.length > 0) {
                if(!orderLocation.val()){
                    swal('Error', 'Please select a location to proceed', 'error');
                    return false;
                }
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
            console.log(this);
            if(payMethodField.val() === 'card'){
                continueToSubmit($(e.target), this);
            }
        });
    });

})(jQuery);
