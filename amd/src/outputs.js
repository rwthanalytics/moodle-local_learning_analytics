define(['jquery'], function ($) {
    return {
        plot: function (id) {
            var data = $('#plot-' + id);

            var plot_data = data.data();

            console.log(data.data().plot);
            require(['local_learning_analytics/plotly-lazy'], function (Plotly) {
                Plotly.newPlot(data.get(0), plot_data.plot, plot_data.layout, plot_data.params);
            });
        },
    }
});