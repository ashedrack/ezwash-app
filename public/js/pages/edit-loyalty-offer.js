(function ($) {
    "use strict";

    let baseUrl = window.location.protocol + '//' + window.location.host;
    let companyField = $('#company');
    $('#editLoyaltyForm').on('submit', function ($e) {
        $e.preventDefault();
        let loyaltyOfferForm = this;
        let activeStatus = $('#active_status').prop('checked');
        if(!activeStatus){
            loyaltyOfferForm.submit();
        }else {
            try {
                let company = companyField.val();
                let url = `${baseUrl}/active_loyalty_offer/${company}`;
                $.ajax({
                    url,
                    method: "GET",
                    dataType: 'json',
                    data: {
                        offer_id : $('#offer_id').val()
                    }
                }).done(function (results) {
                    let {status, data} = results;
                    console.log({data});
                    if (status) {
                        let {offer} = data;
                        let content = document.createElement('p');
                        content.innerHTML = `An offer <span class="text-primary">${offer.display_name}</span> is already active, do you want to deactivate it?`;
                        swal({
                            content,
                            buttons: true,
                            dangerMode: true
                        }).then(function (actionConfirmed) {
                            if(actionConfirmed){
                                $('#force_active').prop('checked', true);
                            }else {
                                $('#force_active').prop('checked', false);
                            }

                            loyaltyOfferForm.submit();
                        })
                        .catch(error => {
                            throw error
                        });
                    }else{
                        loyaltyOfferForm.submit();
                    }
                }).fail(function (errorResponse) {
                    console.error(errorResponse)
                });
            } catch (e) {
                console.error('Error while submitting offer creation form', e);
            }
        }
    })
})(jQuery);
