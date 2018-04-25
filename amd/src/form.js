define(['jquery'], function ($) {
    return {
        init: function() {
            var form = $('form.la_params_form:not(.la_long_running');

            console.log(form);

            if(form !== undefined) {

                $('select', form).on('change', function (e) {
                    if(form.get(0).checkValidity()) {
                        console.log("test");
                        form.submit();
                    }
                });
            }
        }
    }
});