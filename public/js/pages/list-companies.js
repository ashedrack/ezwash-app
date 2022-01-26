(function ($) {
    'use strict';
    $('#allCompanies').DataTable({
        order: [
            [4, 'desc']
        ]
    });
})(jQuery);
function companyDelete(form_id, action_url){
    let {setAttributes} = EzwashHelper
    let el = document.createElement('div');
    setAttributes(el, {
        id: "deleteAlertDiv"
    });
    el.innerHTML= document.getElementById('deleteAlertContent').innerHTML;
    return swal({
        title: "Are you sure?",
        content: {
            element: el
        },
        icon: "warning",
        buttons: false
    })
        .then(x => {
            let {confirm} = swal.getState().actions;
            let confirmVal = confirm ? confirm.value : null;
            if(['temporary','permanent'].includes(confirmVal)) {
                let this_form = document.getElementById(form_id);
                setAttributes(this_form, {
                    action: action_url,
                });
                console.log(confirmVal, this_form);
                // return
                if (confirmVal === 'temporary') {
                    $(`#${form_id} #deletion_type`).val('temporary');
                } else if (confirmVal === 'permanent') {
                    $(`#${form_id} #deletion_type`).val('permanent');
                }
                this_form.submit();
            }
        });
}
