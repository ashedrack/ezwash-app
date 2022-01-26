$(window).on("load", function () {
    $('#recent-buyers, #new-orders').perfectScrollbar({
        wheelPropagation: true
    });

    /********************************************
     *               Monthly Sales               *
     ********************************************/
    // Morris.Bar.prototype.fillForSeries = function(i) {
    //   var color;
    //   return "0-#fff-#f00:20-#000";
    // };
    //
    // Morris.Bar({
    //     element: 'monthly-sales',
    //     data: [{month: 'Jan', sales: 1835 }, {month: 'Feb', sales: 2356 }, {month: 'Mar', sales: 1459 }, {month: 'Apr', sales: 1289 }, {month: 'May', sales: 1647 }, {month: 'Jun', sales: 2156 }, {month: 'Jul', sales: 1835 }, {month: 'Aug', sales: 2356 }, {month: 'Sep', sales: 1459 }, {month: 'Oct', sales: 1289 }, {month: 'Nov', sales: 1647 }, {month: 'Dec', sales: 2156 }],
    //     xkey: 'month',
    //     ykeys: ['sales'],
    //     labels: ['Sales'],
    //     barGap: 4,
    //     barSizeRatio: 0.3,
    //     gridTextColor: '#bfbfbf',
    //     gridLineColor: '#E4E7ED',
    //     numLines: 5,
    //     gridtextSize: 14,
    //     resize: true,
    //     barColors: ['#FF394F'],
    //     hideHover: 'auto',
    // });

});
(function (window, document, $) {
    'use strict';

    $.ajax({
        url: url,
        method: "POST",
        dataType: 'json',
        data: {_token, company, location, start_date, end_date}
    }).done(function (result) {
        const {status, data, message} = result;
        dataLoading({wrapper: '#reports-filter-results', show: false});
        formFields.prop('disabled', false);
    }).fail(function (response) {
        dataLoading({wrapper: '#reports-filter-results', show: false});
        formFields.prop('disabled', false);
        console.error({response})
    });


})(window, document, jQuery);
