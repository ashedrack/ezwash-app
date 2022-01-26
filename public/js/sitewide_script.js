const EzwashHelper = {
    ORDER_PENDING: 'pending',
    ORDER_COMPLETE: 'completed',
    BASE_URL: window.location.protocol + '//' + window.location.host,

    /**
     * Link: https://stackoverflow.com/a/12274782
     * @param element
     * @param attributes
     */
    setAttributes: function (element, attributes) {
        for(let key in attributes) {
            element.setAttribute(key, attributes[key]);
        }
    },
    deletionWarning: (form_id, action_url, message = "Once deleted, you will not be able to recover it!") => {
        return swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
            .then((continueDelete) => {
                if (continueDelete) {
                    let this_form = $(`#${form_id}`);
                    this_form.attr('action', action_url);
                    this_form.submit();
                }
            });
    },
    deactivationWarning: (form_id, action_url, message = "Current activities by this users will be halted and login attempts blocked") => {
        return swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((continueDeactivate) => {
            if (continueDeactivate) {
                let this_form = $(`#${form_id}`);
                this_form.attr('action', action_url);
                this_form.submit();
            }
        });
    },
    reactivationWarning: (form_id, action_url, message = "Activities on this resource will resume") => {
        return swal({
            title: "Are you sure?",
            text: message,
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((continueDeactivate) => {
            if (continueDeactivate) {
                let this_form = $(`#${form_id}`);
                this_form.attr('action', action_url);
                this_form.submit();
            }
        });
    },
    /**
     * This will return true or false depending on what was chosen by the user
     *
     * @param title
     * @param message
     * @param buttons
     * @returns {Promise<T | never>}
     */
    actionConfirmationPrompt: function({title = 'Are you sure?', message = 'Click "OK" to continue', buttons = true}){
        return swal({
            title: title,
            text: message,
            buttons: buttons
        }).then(function(actionConfirmed){
            if(actionConfirmed){
                return true;
            }
            throw new Error('Action Stopped');
        })
        .catch(error => {
            logMessage(error);
            throw error
        });
    },
    updateOrderAmount: function (the_map, total_amount_element) {
        let total = _.map(the_map, x => Number(x.price) * Number (x.quantity)).reduce((a,b) => a + b, 0);
        let discount = parseFloat($('#discount-earned').html() || 0);
        let pickupCost = parseFloat($('#pickup-cost').html());
        let deliveryCost = parseFloat($('#delivery-cost').html());
        let grandTotalID = '#grand_total';
        $(total_amount_element).html(total);
        let grandTotal = total;
        if(discount){
            console.log(grandTotal, total, discount);
            grandTotal -= discount;
        }
        if(pickupCost){
            grandTotal += pickupCost;
        }
        if(deliveryCost){
            grandTotal += deliveryCost;
        }
        // console.log(grandTotal, pickupCost, (total - discount));
        $(grandTotalID).html(grandTotal)
    },
    /**
     * @param the_map "The services already selected"
     * @param serviceData "The service that was selected or deselected"
     * @param action "'selected', 'deselected'"
     * @param dataTable "The datatable containing the services"
     * @param total_price_field The field that contains the total amount of the order
     * @param all_services_dropdown
     */
    updateOrderService: ({the_map = {}, serviceData = {}, action = 'add', dataTable = '', total_price_field = '', all_services_dropdown = ''}) => {
        let {updateOrderAmount, setAttributes} = EzwashHelper;
        if(!['add', 'remove', 'update'].includes(action)){
            console.error(`Invalid Action specified, expected "add" or "remove" or "update" but got ${action}`);
            return false;
        }
        if(action === 'add'){
            //The Datatable plugin provides a draw() attribute that refreshes the table DOM after any (CRUD) operation
            if($('tr#service-' + serviceData.id).length === 0){//Only add a new row if it didn't exist;
                the_map[serviceData.id] = _.clone(serviceData);
                serviceData.delete_row = `<button class="btn btn-danger remove-service">Remove</button>`;
                serviceData.quantity = `<input class="service-quantity" type="number" min="1" value="${serviceData.quantity}"/>`;
                dataTable.rows.add([serviceData]).draw();
                updateOrderAmount(the_map, total_price_field);
            }
        }
        else if(action === 'remove'){
            delete the_map[serviceData.id];
            dataTable.row('#service-' + serviceData.id).remove().draw();
            updateOrderAmount(the_map, total_price_field);
            setAttributes($(`#all-services-options option[value=${serviceData.id}]`)[0], {
                'data-quantity': 1
            });
        }
        else if(action === 'update'){
            the_map[serviceData.id] = _.clone(serviceData);
            setAttributes($(`#all-services-options option[value=${serviceData.id}]`)[0], {
                'data-quantity': serviceData.quantity
            });
            serviceData.delete_row = `<button class="btn btn-danger remove-service">Remove</button>`;
            serviceData.quantity = `<input class="service-quantity" type="number" min="1" value="${serviceData.quantity}"/>`;
            dataTable.row('#service-' + serviceData.id).data(serviceData).draw();

            updateOrderAmount(the_map, total_price_field);
        }
    },
    orderDetailsHandler: function () {
        const SERVICES_MAP = {};
        let { updateOrderAmount, updateOrderService, setAttributes } = EzwashHelper;
        let servicesTable = $('#order_services');

        let orderServicesTable = servicesTable.DataTable({
            paging: false,
            ordering: false,
            data: _.map(SERVICES_MAP,(x) => x),
            createdRow: function (row, data) {
                $(row).attr('id', 'service-' + data.id);
            },
            searching: false,
            info: false,
            columns: [
                { data: "name" },
                { data: "price"},
                { data: "quantity" },
                { data: "total" },
                { data: "delete_row" }
            ]
        });

        $('#all-services-options option:selected').each(function(x, option) {
            let $option = $(option);
            let selected = {
                id: $option.val(),
                name: $option.attr('data-name'),
                price: $option.attr('data-price'),
                quantity: $option.attr('data-quantity'),
                total: Number($option.attr('data-price')) * Number($option.attr('data-quantity'))
            };

            updateOrderService({
                the_map: SERVICES_MAP,
                serviceData: selected,
                action:'add',
                dataTable: orderServicesTable,
                total_price_field: '#total_price'
            });
        });

        $('#all-services-options').multiselect({
            numberDisplayed: 1,
            nonSelectedText: 'Select A Service!',
            enableCaseInsensitiveFiltering: true,
            onChange: function (option, checked) {
                let $option = $(option);
                let selected = {
                    id: $option.val(),
                    name: $option.attr('data-name'),
                    price: $option.attr('data-price'),
                    quantity: $option.attr('data-quantity'),
                    total: Number($option.attr('data-price')) * Number($option.attr('data-quantity'))
                };
                if(checked){
                    updateOrderService({
                        the_map: SERVICES_MAP,
                        serviceData: selected,
                        action:'add',
                        dataTable: orderServicesTable,
                        total_price_field: '#total_price'
                    });
                }
                else{
                    updateOrderService({
                        the_map: SERVICES_MAP,
                        serviceData: selected,
                        action: 'remove',
                        dataTable: orderServicesTable,
                        total_price_field: '#total_price'
                    });
                }
                console.log(SERVICES_MAP);
            }
        });

        servicesTable.on('change', 'tbody tr td .service-quantity', _.debounce(function (event) {
            let rowData = orderServicesTable.row(this.parentElement).data();
            let quantity = event.target.value;
            if(quantity < 1){
                quantity = 1;
            }
            let selected = SERVICES_MAP[rowData.id];
            Object.assign(selected, {
                quantity: quantity,
                total: Number(selected.price) * Number(quantity)
            });
            updateOrderService({
                the_map: SERVICES_MAP,
                serviceData: selected,
                action: 'update',
                dataTable: orderServicesTable,
                total_price_field: '#total_price',
                all_services_dropdown: '#all-services-options'
            });
        }, 700));

        servicesTable.on('click', 'tbody tr td .remove-service', function () {
            let rowData = orderServicesTable.row(this.parentElement).data();
            orderServicesTable.row('#service-' + rowData.id).remove().draw();
            $('#all-services-options').multiselect('deselect', rowData.id);
            delete SERVICES_MAP[rowData.id];
            updateOrderAmount(SERVICES_MAP, '#total_price');
        });

        return {
            continueToSubmit: function(orderForm, this_form) {
                let n = 0;
                $.each(SERVICES_MAP, (x, service) => {
                    let idField = document.createElement('input');
                    setAttributes(idField, {
                        name: `services[${n}][id]`,
                        value: service.id,
                        type: 'hidden'
                    });
                    let qtyField = document.createElement('input');
                    setAttributes(qtyField, {
                        name: `services[${n}][quantity]`,
                        value: service.quantity,
                        type: 'hidden'
                    });
                    orderForm.append(idField);
                    orderForm.append(qtyField);
                    n++;
                });
                let paymentMethod = document.createElement('input');
                setAttributes(paymentMethod, {
                    name: `payment_method`,
                    value: $('#payment_method').val(),
                    type: 'hidden'
                });
                orderForm.append(paymentMethod);
                this_form.submit();
            },
            SERVICES_MAP : SERVICES_MAP
        }

    },
    searchCustomers: function(e, url = null){
        e.preventDefault();
        let _this = this;
        let resultModal = $('#sitewide-search-result');
        resultModal.modal('hide');
        let data = {
            _token: $('#gen_search_token').val()
        };
        if(!url){
            _this.dataLoading({wrapper: 'body'});
            Object.assign(data, {
                query_string: $('#gen_search_string').val(),
                records_per_page: 3
            });
            url = `${this.BASE_URL}/search_customer`;
        }
        $.ajax({
            url: url,
            method: "GET",
            dataType: 'json',
            data: data
        }).done(function (result) {
            if(result.status) {
                let {customers, metadata, links} = result.data;
                console.log({customers, metadata, links});
                customers = customers.map(customer => {
                    customer.actions = `<td class="text-truncate">
                                <a class="btn btn-sm btn-outline-primary round" href="${_this.BASE_URL}/customer/${customer.id}"> View </a>
                                <a class="btn btn-sm btn-primary round" href="${_this.BASE_URL}/add_order/${customer.id}">Create Order</a>
                            </td>
                        </tr>
                    `;
                    return customer;
                });
                $('#customerGenSearchResults').DataTable({
                    destroy: true,
                    data:customers,
                    columns: [
                        {data: "name"},
                        {data: "email"},
                        {data: "created_at"},
                        {data: "actions"}
                    ],
                    language : {
                        emptyTable: "No Customers Found"
                    },
                    searching: false,
                    paging: false,
                    info: false
                });
                document.querySelectorAll('.gsr_pagelinks').forEach(el => {
                    el.innerHTML = links;
                });
            }
        }).fail(function (errorResponse, status, x) {
            console.error({errorResponse, status, x})
        }).always(function(result, status){
            _this.dataLoading({wrapper: 'body', show: false});
            if(status === 'success'){
                resultModal.modal('show');
            }else {
                console.
                swal('Oops', 'Error trying to search customers', 'error');
            }

        });

    },
    allowOnlyNum : function(e){
        let charCode = (e.which) ? e.which : e.keyCode;
        return (charCode === 46 || charCode >= 48 || charCode <= 57);
    },
    /**
     *
     * @param element The ID of the form
     * @returns object Key value pair of the form data
     */
    formToArray: function(element) {
        let formData = $("#" + element).serializeArray();
        let dataArray = {};
        for (let i in formData) {
            dataArray[formData[i].name.trim()] = formData[i].value.trim();
        }
        return dataArray;
    },
    /**
     *
     * @param wrapper {string} The id of the section that the loader should display
     * @param show {boolean} Defaults to true, determines whether show or hide the loader
     * @param loading_text {string} Text to display in the loader
     */
    dataLoading: function ({wrapper = '', show = true, loading_text= "Fetching Data"}) {
        if(show){
            let loader = $('#data-loader-wrapper .data-loading').clone();
            loader.find('#loading-message')[0].textContent = loading_text;
            $(wrapper).append(loader).css('position', 'relative');
        }else{
            $(wrapper + ' > .data-loading').remove();
        }
    },

    number_format: (number, maximumFractionDigits = 2, minimumFractionDigits = 2) => {
        return Number(number).toLocaleString(undefined, { maximumFractionDigits, minimumFractionDigits})
    },

    autocompleteHelper: (inputId, selectFunction, responseFunction = null) => {
        const target =  $(`#${inputId}`);
        const url = target.data('source-url');
        target.autocomplete({
            source: url,
            minLength: 1,
            response: responseFunction,
            select: function( event, ui ) {
                if(selectFunction) {
                    selectFunction(event, ui);
                }
                return false;
            }
        });
    }
};

(function($){
    $(document).on('mouseover', '#main-menu-navigation .nav-item', function(e){
        $('#main-menu-navigation .nav-item').removeClass('show');
        $(this).addClass('show');
    });
    $(document).on('mouseout', '#main-menu-navigation .nav-item', function(e){
        $('#main-menu-navigation .nav-item').removeClass('show');
    });
    $(document).ready(function(){
        $('.preloader').hide();
        $('#sitewide-search-form').submit(function (e) {
            EzwashHelper.searchCustomers(e);
        })
    });
    $.validator.addMethod("validphone", phoneNumberCheck,
        "Invalid phone number");

    $.each($('.naira-prefix'), (x, item) => {
        const itemValue = $(item).html();
        if (!isNaN(itemValue) && !$(item).hasClass('icon')) {
            $(item).html(EzwashHelper.number_format(itemValue));
        }
    });
    const datePickers = $(".datepicker");
    if(datePickers.length > 0){
        datePickers.datepicker();
    }
})(jQuery);

function allowOnlyNum(e) {
    let charCode = (e.which) ? e.which : e.keyCode;
    if(charCode > 31 && (charCode < 48 || charCode > 57)){
        return false;
    }
    return true;
}

function allowOnlyPhoneCharacters(e) {
    let charCode = (e.which) ? e.which : e.keyCode;
    if(charCode === 43){
        return true;
    }
    else if(charCode > 31 && (charCode < 48 || charCode > 57)){
        return false;
    }
    return true;
}

function phoneNumberCheck(value, element, params) {
    const regex = RegExp('(^0[0-9]{10}$|^\\+234[1-9][0-9]{9}$)');
    return regex.test(value);
}

function readURL(input, preview_id) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $(preview_id).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
    }
}
function imageUploadCheck(uploadField, {allowedTypes = ['image/png', 'image/jpg', 'image/jpeg'], maxSize=2}, callback) {
    let response = {
        correctFileType: true,
        correctMaxSize: true
    };
    let mimes = allowedTypes.map(x => x.replace('image/', '')).join(', ');
    let thisFileType = uploadField.files[0].type;
    if (allowedTypes.indexOf(thisFileType) === -1) {
        response.correctFileType = false;
        swal(`Only ${mimes} images are allowed`);
    }
    if (!validateUploadSize(uploadField, maxSize)) {
        response.correctMaxSize = false;
        swal('File size is ' + uploadField.files[0].size / 1024 / 1024 + 'MB. It should not exceed ' + maxSize + 'MB');
    }
    return callback(response);
}
function validateUploadSize(file, max_size) {
    var FileSize = file.files[0].size / 1024 / 1024; // in MB
    if (FileSize > max_size) {
        return false;
    }
    return true;
}

/**
 * JavaScript Get URL Parameter
 *
 * @param {String} prop The specific URL parameter you want to retrieve the value for
 * @return {String|Object} If prop is provided a string value is returned, otherwise an object of all properties is returned
 */
function getUrlParams( prop ) {
    let params = {};
    let search = decodeURIComponent( window.location.href.slice( window.location.href.indexOf( '?' ) + 1 ) );
    let definitions = search.split( '&' );

    definitions.forEach( function( val, key ) {
        let parts = val.split( '=', 2 );
        params[ parts[ 0 ] ] = parts[ 1 ];
    } );

    return ( prop && prop in params ) ? params[ prop ] : params;
}
