(function($) {
    'use strict';

    function checkLatLng(validator){
        if(!$('#latitude').val()  || !$('#longitude').val()){
            validator.showErrors({
                "address": "You have not set a valid address"
            });
            return false;
        }else{
            return true;
        }
    }

    let locationForm = $('#editLocation');
    let locationValidator = locationForm.validate();
    $(document).on('submit', '#createLocation',function (e) {
        return checkLatLng(locationValidator);
    });
    $("#store_image").change(function (){
        let options = {
            maxSize: 1
        };
        imageUploadCheck(this, options, (response) => {
            if(!response.correctFileType || !response.correctMaxSize){
                $("#store-image-preview").html('');
                $("#store_image").val('');
                return;
            }
            readURL(this, '#store-image-preview');
        })
    });

    let addressField = document.getElementById('address');
    let lngField = document.getElementById('longitude');
    let latField = document.getElementById('latitude');
    const placesAutocomplete = new google.maps.places.Autocomplete(addressField);

    placesAutocomplete.addListener('place_changed', function() {
        let place = placesAutocomplete.getPlace();
        if (place.geometry) {
            let location = place.geometry.location;
            $(lngField).val(location.lng());
            $(latField).val(location.lat());
        }else {
            $(lngField).val('');
            $(latField).val('');
        }
    });


})(jQuery);
