define(['jquery'], function ($) {
    return {
        plot: function (id) {
            var data = $('#plot-' + id);

            console.log(data.data().plot);
            require(['local_learning_analytics/plotly-lazy'], function (Plotly) {
                Plotly.newPlot(data.get(0), data.data().plot);
            });
        },
    }
});