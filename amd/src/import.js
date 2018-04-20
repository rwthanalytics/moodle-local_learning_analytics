define(['core/ajax', 'core/url', 'jquery', 'local_learning_analytics/outputs'], function (ajax, url, $, outputs) {

    var importInfo;
    var textInfo;
    var progressBar;

    function startImport() {
        progressBar = $('<progress class="progress progress-striped progress-animated" value="15" max="100"></progress>');
        textInfo = $('<div></div>');

        importInfo = $('#import_info')
            .append(progressBar)
            .append(textInfo);
    }

    return {
        init: function (report, type, params) {
            $('#start_import_btn').click(function() {
                startImport();
            });
        }
    }
});