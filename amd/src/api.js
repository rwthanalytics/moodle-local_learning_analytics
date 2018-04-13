define(['core/ajax', 'core/url', 'jquery'], function (ajax, url, $) {
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
                    deferred.resolve(response);
                });
            });
        },
    }
});