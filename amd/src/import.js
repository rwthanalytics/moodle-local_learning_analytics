define(['core/ajax', 'core/url', 'jquery'], function (ajax, url, $) {

    var importInfo;
    var textInfo;
    var progressBar;

    var lastSavePoint = 0;

    var runImport = false;

    function numberSort(a, b) {
        return a - b;
    }

    function showStatus() {
        var percent = ((minUserid + doneUserImports.length) / maxUserid) * 100;
        var stoppedText = '';
        if (!runImport) {
            stoppedText = ' <strong>(not running)</strong>';
        }
        var minUserText = '';
        if (doneUserImports.length !== 0) {
            minUserText = ' (minuserid: ' + minUserid + ')';
        }
        progressBar.val(percent);
        textInfo.html('<p>' + percent.toFixed(2) +'% ' + stoppedText + '</p>' +
            '<p>Users done: ' + (minUserid + doneUserImports.length) + minUserText + '</p>' +
            '<p>Save point: ' + lastSavePoint + '</p>' +
            '<p>Next user: ' + nextUserid + '</p>' +
            '<p>Working on users: ' + activeUserImports.join(', ') + '</p>');
    }

    var nextUserid;
    var maxUserid = 1;

    var activeUserImports = [];
    var doneUserImports = [];
    var minUserid;

    var PARALLEL_REQUESTS = 6;

    function callRestorePoint(userid) {
        ajax.call([{
            methodname: 'local_learning_analytics_ajax_import',
            args: {
                action: 'savepoint',
                userid: userid,
                offset: 0
            }
        }])[0].done(function (response) {
            lastSavePoint = userid;
        }).fail(function (err) {
            alert('An unrevolerable error occured during import. Check the browser console.');
            console.log(err);
        });
    }

    function callAjaxParallel(userid, offset) {
        userid = userid || nextUserid;
        offset = offset || 0;

        if (userid > maxUserid) {
            runImport = false;
        }
        if (!runImport && offset === 0) {
            showStatus();
            return;
        }

        if (activeUserImports.length < PARALLEL_REQUESTS || offset !== 0) {
            (function(userid, offset) {
                ajax.call([{
                    methodname: 'local_learning_analytics_ajax_import',
                    args: {
                        action: 'import',
                        userid: userid,
                        offset: offset
                    }
                }])[0].done(function (response) {
                    maxUserid = Math.max(maxUserid, response.maxUserid); // just in case someone registers while we run this script
                    if (response.nextOffset === -1) {
                        // no more user data to handle, start with next user
                        var importPos = activeUserImports.indexOf(userid);
                        activeUserImports.splice(importPos, 1);
                        doneUserImports.push(userid);
                        doneUserImports.sort(numberSort);

                        // smallest userid -> mark this as done
                        while (doneUserImports[0] === minUserid + 1) {
                            minUserid = doneUserImports.shift();
                            if (minUserid % 100 === 0) {
                                callRestorePoint(minUserid);
                            }
                        }

                        callAjaxParallel();
                    } else {
                        callAjaxParallel(userid, response.nextOffset);
                    }
                });
            }(userid, offset));
            if (offset === 0) {
                activeUserImports.push(userid);
                nextUserid++;
                showStatus();
            }
            callAjaxParallel();
        }
    }

    function startImport() {
        runImport = true;

        callAjaxParallel();
    }

    return {
        init: function (startUserid, maxUsers) {
            minUserid = startUserid;
            nextUserid = minUserid + 1;
            maxUserid = maxUsers;
            lastSavePoint = startUserid;

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
            });

            showStatus();
        }
    }
});