
(function ($) {
    'use strict';
    let baseUrl = window.location.protocol + '//' + window.location.host;
    $(document).ready(function() {
        let companyInput = $('#company');
        let defLocation =  $('#location').attr('data-selected-location');
        if(companyInput.attr('data-selected-company')){
            companyInput.trigger('change', [defLocation])
        }
    });
    $(document).on('change', '#company',function (event, selectedLocation) {
        let _token = $('input[name=_token]').val();
        let company = event.target.value;
        let locationInput = $('#location');
        if(!company){
            locationInput.prop("selectedIndex", 0).prop('disabled', true);
        }else {
            locationInput.empty().prop('disabled', true);
            $.ajax({
                url: `${baseUrl}/get-company-locations/${company}`,
                method: "POST",
                dataType: 'json',

                data: {_token, company}
            }).done(function (results) {
                if(results){
                    locationInput.append(`
                        <option value="">Select a location</option>
                    `);
                    results.forEach(location => {
                        let {id, name} = location;
                        locationInput.append(`
                            <option value="${id}">${name}</option>
                        `)
                    })
                }

                if(selectedLocation){
                    console.log({selectedLocation});
                    $(`#location option[value=${selectedLocation}]`).prop('selected', true);
                }
                locationInput.prop('disabled', false);
            }).fail(function (errorResponse) {
                console.error({errorResponse})
            });
        }
    })
})(jQuery);
