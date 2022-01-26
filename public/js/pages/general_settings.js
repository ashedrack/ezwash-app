const {formToArray} = EzwashHelper;
(function ($) {
    "use strict";
    $('#recipientsTable').DataTable({
        order: []
    });
})(jQuery);

function generateUsersList(){
    let formData = formToArray('generate_users');
    let formDataReadable = {
        company: $('#filter_company option:selected').html(),
        location: $('#filter_location option:selected').html()
    };
    let contentWrap = document.createElement('div');
    let details = document.createElement('ul');
    let last_activity_start_at = formData.last_activity_start_at;
    let last_activity_end_at = formData.last_activity_end_at;
    $(details).append(`
        <li>Company: <span class="text-warning">${formData.company? formDataReadable.company : 'All'}</span></li>
        <li>Location: <span class="text-warning">${formData.location? formDataReadable.location : 'All'}</span></li>
        <li>Activity Range: From <span class="text-warning"> ${last_activity_start_at || 'All'}</span> to <span class="text-warning">${last_activity_end_at || 'All'}</span></li>
    `).addClass('text-left');
    contentWrap.appendChild(details);
    $(contentWrap).append(`
        <strong>This report will be sent via mail to the users that are allowed to receive mail reports</strong>
    `);
    return swal({
        title: 'Filter Details',
        content: contentWrap,
        // icon: "info",
        buttons: ["Cancel", "Generate Report"],
        dangerMode: true,
    }).then((response) => {
        $('#mail-sent').remove();
        if (response) {

            // $('#generate_users').prepend(`
            //     <div id="mail-sent" class="alert alert-primary alert-dismissible fade show col-12" role="alert">
            //         List has been sent to your email
            //         <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            //             <span aria-hidden="true">&times;</span>
            //         </button>
            //     </div>
            // `);
            $('#generate_users').submit(); //Submit form
        }
    });
}
