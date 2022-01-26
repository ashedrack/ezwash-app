(function ($) {
    $(document).ready(function () {

        let filterSection = $('#employee_filter_section');
        filterSection.hide();
        $(document).on('click', '#toggle_filter_section', function () {
            filterSection.toggle('slow');
        });

        $(document).on('click', '#hide-employee-filter', function () {
            filterSection.addClass('hide-section');
        });

        $('#employees_filter').submit(function () {
            let allOptions = $('#employees_filter').find('select option');
            for (let option of allOptions) {
                if (!$(option).val()) {
                    $(option).attr('disabled', 'disabled');
                }
            }
            let nameOrEmailField = $('#employee_name_or_email');
            if (!nameOrEmailField.val()) {
                nameOrEmailField.attr('disabled', 'disabled')
            }
        });

        let baseUrl = window.location.protocol + '//' + window.location.host;
        $(document).ready(function () {
            let companyInput = $('#employee_company');
            let defLocation = $('#employee_location').attr('data-selected-location');
            if (companyInput.attr('data-selected-company')) {
                companyInput.trigger('change', [defLocation])
            }
        });
        $(document).on('change', '#employee_company', function (event, selectedLocation) {
            let _token = $('input[name=_token]').val();
            let company = event.target.value;
            let locationInput = $('#employee_location');
            if (!company) {
                locationInput.prop("selectedIndex", 0).prop('disabled', true);
            } else {
                locationInput.empty().prop('disabled', true);
                $.ajax({
                    url: `${baseUrl}/get-company-locations/${company}`,
                    method: "POST",
                    dataType: 'json',

                    data: {_token, company}
                }).done(function (results) {
                    if (results) {
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

                    if (selectedLocation) {
                        console.log({selectedLocation});
                        $(`#employee_location option[value=${selectedLocation}]`).prop('selected', true);
                    }
                    locationInput.prop('disabled', false);
                }).fail(function (errorResponse) {
                    console.error({errorResponse})
                });
            }
        })
    });
})(jQuery);
