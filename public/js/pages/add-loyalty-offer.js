(function ($) {
    "use strict";
    let startDate = $('#offer_start_date').attr('data-min-date');
    $('.offer_date_field').datepicker({
        minDate: startDate,
        dateFormat: "yy-mm-dd"
    });

    let baseUrl = window.location.protocol + '//' + window.location.host;
    let companyField = $('#company_select');
    if(companyField.length){
        $(document).on('change', '#company_select', function (e) {
            let $this = $(e.target);
            if ($this.val()) {
                let selectedOption = $(e.target).find(':selected')[0];
                let minStartDate = $(selectedOption).attr('data-minstartdate');
                if(minStartDate > dateTomorrow) {
                    $('.offer_date_field')
                    .datepicker('destroy')
                    .datepicker({
                        minDate: minStartDate,
                        dateFormat: "yy-mm-dd"
                    }).val('');
                }
            }
        });
    }
    $('#addLoyaltyForm').on('submit', function ($e) {
        $e.preventDefault();
        let loyaltyOfferForm = this;
        let activeStatus = $('#active_status').prop('checked');
        if(!activeStatus){
            loyaltyOfferForm.submit();
        }else {
            try {
                let url = `${baseUrl}/active_loyalty_offer`;
                if (companyField.length > 0) {
                    let company = companyField.val();
                    console.log({company});
                    if (company === '') {
                        swal('Oops!', 'Please select a company to proceed', 'error');
                        throw new Error('Company not selected');
                    }
                    url = `${baseUrl}/active_loyalty_offer/${company}`;
                }
                $.ajax({
                    url,
                    method: "GET",
                    dataType: 'json'
                }).done(function (results) {
                    let {status, message, data} = results;
                    if (status) {
                        let {offer} = data;
                        let content = document.createElement('p');
                        content.innerHTML= `An offer <span class="text-primary">${offer.display_name}</span> is already active, do you want to deactivate it?`;
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
