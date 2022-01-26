(function ($) {
    $(document).ready(function () {
        $('.pick-date').datepicker({
            dateFormat: 'yy-mm-dd',
            maxDate: '0d',
            changeMonth: true,
            changeYear: true
        });
        const fromDateField = $('.from-date');
        const toDateField = $('.to-date');
        fromDateField.change(function () {
            const minToDate = this.value ? new Date(this.value).valueOf() : null;
            const toDateValTimestamp = toDateField.val() ? new Date(toDateField.val()).valueOf(): null;
            console.log(this.value, toDateField.val());
            if(minToDate && (!toDateValTimestamp || toDateValTimestamp < minToDate)) {
                $('.to-date')
                    .datepicker('destroy')
                    .datepicker({
                        minDate: this.value,
                        maxDate: '0d',
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true
                    }).val('');
            }
        });

        toDateField.change(function () {
            const toDateTimestamp = this.value ? new Date(this.value).valueOf() : null;
            const fromDateValTimestamp = fromDateField.val() ? new Date(fromDateField.val()).valueOf(): null;
            console.log(this.value, fromDateField.val());
            if(toDateTimestamp && (!fromDateValTimestamp || fromDateValTimestamp > toDateTimestamp)) {
                $('.from-date')
                    .datepicker('destroy')
                    .datepicker({
                        maxDate: this.value,
                        dateFormat: "yy-mm-dd",
                        changeMonth: true,
                        changeYear: true
                    }).val('');
            }
        });
        fromDateField.trigger('change');
        toDateField.trigger('change');
    })
})(jQuery);