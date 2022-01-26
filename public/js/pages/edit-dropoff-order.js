(function ($) {
    "use strict";
    $(document).ready(function(){
        const {continueToSubmit, SERVICES_MAP} = EzwashHelper.orderDetailsHandler();
        let updateFormId = 'editOrderForm';
        $(document).on('submit', '#' + updateFormId, function (e) {
            e.preventDefault();
            let payMethodField = $('#payment_method');
            if(Object.keys(SERVICES_MAP).length === 0){
                swal('Error', 'Please select a service to continue', 'error');
                return false;
            }
            if(payMethodField.val() && ['cash', 'pos'].includes(payMethodField.val())){
                swal({
                    title: 'Payment Confirmation',
                    text: `Please confirm that payment has been received`,
                    buttons: ["Cancel", "Payment Received"]
                }).then(confirmed => {
                    if(confirmed){
                        continueToSubmit($(e.target), this);
                    }
                })
            }else {
                continueToSubmit($(e.target), this);
            }
        });

        $(document).on('click', '.locker-box', function (e) {
            let box = $(e.target);
            let lockerNumber = box.attr('data-locker-number');
            if(!box.hasClass('occupied')){
                if(box.hasClass('selected')){
                    box.removeClass('selected');
                    $(`#locker-input-${lockerNumber}`).prop('checked', false);
                }else{
                    box.addClass('selected');
                    $(`#locker-input-${lockerNumber}`).prop('checked', true);
                }
            }

        })
    });

})(jQuery);
