(function ($) {
    let {dataLoading, formToArray} = EzwashHelper;
    $(document).on('submit', '#filter-reports', function (e) {
        e.preventDefault();
        let formFields = $('#filter-reports input, #filter-reports select');
        dataLoading({wrapper: '#reports-filter-results'});
        let url = $(this).attr('action');
        let filterOptions = formToArray('filter-reports');
        let {company, location, start_date, end_date, _token} = filterOptions;
        formFields.prop('disabled', 'on');
        setTimeout( () => {
            $.ajax({
                url: url,
                method: "POST",
                dataType: 'json',
                data: {_token, company, location, start_date, end_date}
            }).done(function (result) {
                let data = result;
                console.log({result});
                //Update report fields
                $('#sales_by_filter').text(addCommasToNumbers(data.total_income));
                $('#sales_count').text(addCommasToNumbers(data.completed_orders));
                $('#users_added').text(data.new_customers);
                $('#discounts_amount').text(addCommasToNumbers(data.discounts));
                $('#pending_sales').text(addCommasToNumbers(data.pending_income));
                $('#card_transactions').text(addCommasToNumbers(data.card_income));
                $('#cash_transactions').text(addCommasToNumbers(data.cash_income));
                $('#pos_transactions').text(addCommasToNumbers(data.pos_income));
                $('.sales_count').text(data.sales_count);
                $('#discounts_count').text(data.discounts_count);
                $('#pending_sales_count').text(data.pending_orders);
                $('#card_tr_count').text(data.card_income_count);
                $('#cash_tr_count').text(data.cash_income_count);
                $('#pos_tr_count').text(data.pos_income_count);
                // $('.filtered_value').val(JSON.stringify(filterOptions));
                dataLoading({wrapper: '#reports-filter-results', show: false});
                formFields.prop('disabled', false);
            }).fail(function (response) {
                dataLoading({wrapper: '#reports-filter-results', show: false});
                formFields.prop('disabled', false);
                console.error({response})
            });
        }, 500);
    })
})(jQuery);

function addCommasToNumbers(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
