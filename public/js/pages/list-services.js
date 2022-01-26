function toggle_service({name = null, price = null, service_id = null, action = null}){
    let templateClone = document.querySelector('template#editServiceTemplate').content.cloneNode(true);

    $(templateClone).find('#edit_service_form')[0].setAttribute('action', action);
    $(templateClone).find('#edit_name')[0].setAttribute('value', name);
    $(templateClone).find('#edit_price')[0].setAttribute('value', price);
    $(templateClone).find('#service_id')[0].setAttribute('value', service_id);
    return swal({
        content: {
            element: templateClone
        },
        buttons: {
            cancel: {
                text: "Cancel",
                visible: true,
                className: "btn-secondary",
                closeModal: true,
            }
        }
    }).then(x => {
        console.log(x);
    });
}
