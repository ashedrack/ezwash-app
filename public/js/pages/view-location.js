(function($) {
    'use strict';
    $('#locationEmployeesTable').dataTable({
        language : {
            emptyTable: "No Employees Found"
        },
        searching: false,
        lengthMenu: [ 20, 50, 75, 100 ]
    });

    $('#locationOrdersTable').dataTable({
        language : {
            emptyTable: "No Orders Found"
        },
        searching: false,
        lengthMenu: [ 20, 50, 75, 100 ]
    });
})(jQuery);
