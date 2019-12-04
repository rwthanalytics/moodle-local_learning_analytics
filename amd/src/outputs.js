define(['core/ajax', 'jquery'], function (ajax, $) {

    // Load plotly from other directory
    // guide: https://docs.moodle.org/dev/Guide_to_adding_third_party_jQuery_for_AMD
    window.requirejs.config({
        paths: {
            'local_learning_analytics/plotly': M.cfg.wwwroot + '/local/learning_analytics/js/plotly.min',
        },
    });

    var outputs = {
        plot: function (id) {
            var data = $('#' + id);
            var plot_data = data.data();

            require(['local_learning_analytics/plotly'], function (Plotly) {
                Plotly.newPlot(data.get(0), plot_data.plot, plot_data.layout, plot_data.params);
            });
        }
    };

    return outputs;
});