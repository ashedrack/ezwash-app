//Place this file after jQuery and sweet-alert scripts have been added
(function ($) {
    function showDeletionAlert(alertTemplate, formId, actionUrl) {
        let templateClone = alertTemplate.content.cloneNode(true);
        let {setAttributes} = EzwashHelper;
        return swal({
            title: "Are you sure?",
            content: {
                element: templateClone
            },
            icon: "warning",
            buttons: false
        })
            .then(x => {
                let {confirm} = swal.getState().actions;
                let confirmVal = confirm ? confirm.value : null;
                if(['temporary','permanent'].includes(confirmVal)) {
                    let this_form = document.getElementById(formId);
                    setAttributes(this_form, {
                        action: actionUrl,
                    });
                    let deletionTypeInput =  $(`#${formId} #deletion_type`);
                    if(deletionTypeInput.length === 0){
                        deletionTypeInput = document.createElement('input');
                        setAttributes(deletionTypeInput,{
                            type: 'hidden',
                            id: 'deletion_type',
                            name: 'deletion_type'
                        });
                        $(`#${formId}`).append(deletionTypeInput);
                    }

                    $(`#${formId} #deletion_type`).val(confirmVal);
                    this_form.submit();
                }
            });
    }
    let triggerButton = document.querySelectorAll('[data-deletion-prompt]');
    let deletionTemplate = document.querySelector('template[data-deletion-template]');
    $(triggerButton).on('click', function (){
        let formId = $(this).attr('data-deletion-form');
        let actionUrl = $(this).attr('data-deletion-url');
        return showDeletionAlert(deletionTemplate, formId, actionUrl)
    });
})(jQuery);


