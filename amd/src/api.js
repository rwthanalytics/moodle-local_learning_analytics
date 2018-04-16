define(['core/ajax', 'core/url', 'jquery', 'local_learning_analytics/outputs'], function (ajax, url, $, outputs) {
    return {
        getReport: function (report, type, params) {
            return $.Deferred(function (deferred) {
                var request = ajax.call([
                    {
                        methodname: 'local_learning_analytics_report',
                        args: {
                            report: report,
                            type: type,
                            params: typeof (params) === 'object' ? JSON.stringify(params) : params,
                        }
                    }
                ])[0];

                request.done(function (response) {

                    for (var i = 0; i < response.length; i++) {
                        response[i].content = atob(response[i].content);
                        response[i].params = JSON.parse(response[i].params);
                    }

                    deferred.resolve(response);
                });
            });
        },

        run: function(type, params) {
            switch(type) {
                case 'plot':
                    outputs.plot(params.id);
            }
        },

        ajax: function (method, id) {

        }
    }
});