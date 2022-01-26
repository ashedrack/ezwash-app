(function ($) {
    $(document).ready(function() {
        let filterSection = $('#order_filter_section');
        const orderRequestStatusSelector = '#order_request_status';

        if($(orderRequestStatusSelector).length > 0){
            $(orderRequestStatusSelector).multiselect({
                numberDisplayed: 3,
                enableCaseInsensitiveFiltering: true,
                dropUp: false,
                maxHeight: 200
            });
        }
        $(document).on('click', '#toggle_filter_section', function () {
            if (filterSection.hasClass('hide-section')) {
                filterSection.removeClass('hide-section')
            } else {
                filterSection.addClass('hide-section');
            }
        });

        $(document).on('click', '#hide-order-filter', function () {
            filterSection.addClass('hide-section');
        });

        $('#orders_filter').submit(function (e) {
            let allOptions = $('#orders_filter').find('select option, input');
            for (let option of allOptions) {
                if ($(option).val() === '') {
                    $(option).attr('disabled', 'disabled');
                }
            }
            if (!$('#filter_name_or_email').val()) {
                $('#filter_name_or_email').attr('disabled', 'disabled')
            }
        });
    });

})(jQuery);
