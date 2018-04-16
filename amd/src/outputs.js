define(['core/ajax', 'jquery'], function (ajax, $) {
    var outputs = {
        plot: function (id) {
            var data = $('#plot-' + id);

            var plot_data = data.data();

            console.log(data.data().plot);
            require(['local_learning_analytics/plotly-lazy'], function (Plotly) {
                Plotly.newPlot(data.get(0), plot_data.plot, plot_data.layout, plot_data.params);
            });
        },

        plot_ajax: function () {

        },

        table_ajax: function (target, value, params) {
            var table = $('table#'+target).get(0);
            var row = table.rows[params.row+1];

            var vals = JSON.parse(atob(value));

            console.log(vals);

            for(var i = 0; i < row.cells.length; i++) {
                var cell = $(row.cells[i+1]);
                cell.html(vals[i]);
            }

        },

        ajax: function (id, method, type, target, params) {
            var request = ajax.call([
                {
                    methodname: 'local_learning_analytics_ajax',
                    args: {
                        method: method,
                        id: id.toString(),
                        params: JSON.stringify(params)
                    }
                }
            ])[0];

            console.log(type + "_ajax");

            request.done(function (response) {
                outputs[type + "_ajax"](target, response.value, params);
            });
        }
    };

    return outputs;
});