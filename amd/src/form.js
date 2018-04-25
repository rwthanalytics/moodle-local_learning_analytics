define(['jquery'], function ($) {
    return {
        init: function() {
            var form = $('form.la_params_form:not(.la_long_running');

            console.log(form);

            if(form !== undefined) {
                $('select', form).on('change', function (e) {
                    form.submit();
                    console.log("test");
                });

                console.log($('select', form));

                console.log("Added all handler");
            }
        }
    }
});