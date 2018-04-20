define(['core/ajax', 'core/url', 'jquery', 'local_learning_analytics/outputs'], function (ajax, url, $, outputs) {

    var importInfo;
    var textInfo;
    var progressBar;

    var runImport = false;

    function showStatus(response) {
        var percent = response.perc * 100;
        var stoppedText = '';
        if (!runImport) {
            stoppedText = ' <strong>(STOPPED)</strong>';
        }
        progressBar.val(percent);
        textInfo.html('<p>' + (response.perc * 100).toFixed(2) +'% ' + stoppedText + '</p>' +
            '<p>Current user: ' + response.user + ' (offset: ' + response.offset + ')</p>');
    }

    function callAjax() {
        ajax.call([{
            methodname: 'local_learning_analytics_ajax_import',
            args: {}
        }])[0].done(function (response) {
            showStatus(response);
            if (runImport) {
                callAjax();
            }
        });
    }

    function startImport() {
        runImport = true;

        callAjax();
    }

    return {
        init: function (report, type, params) {
            progressBar = $('<progress class="progress progress-striped progress-animated" value="0" max="100"></progress>');
            textInfo = $('<div></div>');

            importInfo = $('#import_info')
                .append(progressBar)
                .append(textInfo);

            $('#start_import_btn').click(function() {
                startImport();
            });
            $('#stop_import_btn').click(function() {
                runImport = false;
            })
        }
    }
});