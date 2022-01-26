(function ($) {
    "use strict";
    let SPECIAL_OFFER_CUSTOMERS = {};
    let startDate = $('#offer_start_date').attr('data-min-date');
    $('.offer_date_field').datepicker({
        minDate: startDate,
        dateFormat: "yy-mm-dd"
    });

    let baseUrl = window.location.protocol + '//' + window.location.host;
    let companyField = $('#company_select');
    if (companyField.length) {
        $(document).on('change', '#company_select', function (e) {
            let $this = $(e.target);
            if ($this.val()) {
                let selectedOption = $(e.target).find(':selected')[0];
                let minStartDate = $(selectedOption).attr('data-minstartdate');
                if (minStartDate > dateTomorrow) {
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
    const addNewCustomerRow = (customer) => {
        console.log({customer});
        const emptyCustomer = '#emptyCustomer';
        if (!SPECIAL_OFFER_CUSTOMERS[customer.id]) {
            SPECIAL_OFFER_CUSTOMERS[customer.id] = customer;
            if($(emptyCustomer).length > 0){
                $(emptyCustomer).remove();
            }
            $('#offerCustomers tbody').append(`
                <tr id="customer-row-${customer.id}">
                    <td>${customer.name}</td>
                    <td>${customer.email}</td>
                    <td>${customer.phone}</td>
                    <td><button class="btn-danger remove-customer" data-customer-id="${customer.id}">Remove</button></td>
                </tr>
            `)
        }
    };

    if(EXISTING_CUSTOMERS && Object.keys(EXISTING_CUSTOMERS).length > 0){
        _.forEach(EXISTING_CUSTOMERS, function (customer) {
            addNewCustomerRow(customer);
        })
    }

    EzwashHelper.autocompleteHelper('customersAutocomplete', (el, ui) => {
            const item = ui.item;
            const emptyCustomer = '#emptyCustomer';
            if (!SPECIAL_OFFER_CUSTOMERS[item.id]) {
                SPECIAL_OFFER_CUSTOMERS[item.id] = item;
                if($(emptyCustomer).length > 0){
                    $(emptyCustomer).remove();
                }
                $('#offerCustomers tbody').append(`
                    <tr id="customer-row-${item.id}">
                        <td>${item.name}</td>
                        <td>${item.email}</td>
                        <td>${item.phone}</td>
                        <td><button class="btn-danger remove-customer" data-customer-id="${item.id}">Remove</button></td>
                    </tr>
                `)
            }
            $('#customersAutocomplete').val('');
        });

    $(document).on('click', '.remove-customer', function () {
        const customerID = $(this).data('customer-id');
        $(`#customer-row-${customerID}`).remove();
        delete SPECIAL_OFFER_CUSTOMERS[customerID];
        console.log(SPECIAL_OFFER_CUSTOMERS);
        if($('#offerCustomers tbody tr').length === 0){
            $('#offerCustomers tbody').append(`
                <tr id="emptyCustomer">
                    <td colspan="5" class="text-center">No customer selected</td>
               </tr>
            `);
        }
    });

    $('#specialOfferCreationForm').on('submit', function (e) {
        e.preventDefault();
        let loyaltyOfferForm = this;
        _.forEach(SPECIAL_OFFER_CUSTOMERS, function (value, key) {
            $(loyaltyOfferForm).append(`
                <input type="hidden" value="${key}" name="customers[]" id="hidden-cus-${key}">
            `)
        });
        loyaltyOfferForm.submit();
    });

})(jQuery);

