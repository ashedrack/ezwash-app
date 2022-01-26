(function ($) {
    $('#employeesTable').dataTable({
        language : {
            emptyTable: "No Employees Found"
        },
        searching: false,
        paging: false,
        info: false
    });
})(jQuery);
