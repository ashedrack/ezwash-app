(function ($) {
    "use strict";
    $(document).ready(function(){
        $('#order_services').DataTable({
            paging: false,
            ordering: false
        });
        $('#order_services_filter, #order_services_info').hide();

    });

})(jQuery);
