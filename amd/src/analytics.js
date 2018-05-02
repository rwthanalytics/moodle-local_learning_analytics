define(['core/ajax'], function (ajax) {
    function keep_alive(session) {
        ajax.call([
            {
                methodname: 'local_learning_analytics_keep_alive',
                args: {
                    session: session
                }
            }
        ]);
    }
    return {
        init: function (session) {
            keep_alive();

            setInterval(function () {
                keep_alive(session);
            }, 60 * 1000);
        }
    }
});