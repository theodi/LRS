$( document ).ready(function() {
var parseDate = d3.time.format("%Y-%m-%d").parse;
var parsenvd3Date = d3.time.format("%d/%m/%Y").parse;
d3.csv('../api/v1/trained_stats.php', function(data) {
  data.forEach(function(d) {
    d.Date = parseDate(d.Date);
  });
  out = {};
  data.forEach(function(row) {
    keys = Object.keys(row);
    date = row.Date;
    f2f = row.Attended_Training;
    eLearning = row.eLearning_Complete;
    $('#trained').html(parseInt(f2f) + parseInt(eLearning));
    $('#completions').html(row.eLearning_Modules_Complete);
    $('#active').html(row.eLearning_Active);
    keys.forEach(function(key) {
      if (key != "Date") {
        if (!out[key]) {
          out[key] = {};
          out[key].key = key;
          out[key].values = [];
        }
        value = [date,row[key]*1];
        out[key].values.push(value);
      }
    })
  });
  final = [];
  for (var key in out) {
    final.push(out[key]);
  }

//d3.json('stackedAreaData.json', function(data) {
  nv.addGraph(function() {
    var chart = nv.models.stackedAreaChart()
                  .margin({right: 100})
                  .x(function(d) { return d[0] })   //We can modify the data accessor functions...
                  .y(function(d) { return d[1] })   //...in case your data is formatted differently.
                  .useInteractiveGuideline(true)    //Tooltips which show all data points. Very nice!
                  .rightAlignYAxis(true)      //Let's move the y-axis to the right side.
                  .transitionDuration(500)
                  .showControls(true)       //Allow user to choose 'Stacked', 'Stream', 'Expanded' mode.
                  .clipEdge(true);

    chart.interactiveLayer.tooltip.headerFormatter(function (d) {
          return "Date: " + d3.time.format('%d/%m/%Y')(parsenvd3Date(d));
    })
    //Format x-axis labels with custom function.
    chart.xAxis
        .tickFormat(function(d) { 
          return d3.time.format('%d/%m/%Y')(new Date(d)) 
    });
    

    chart.yAxis
        .tickFormat(d3.format(',.0f'));

    d3.select('#chart svg')
      .datum(final)
      .call(chart);

    nv.utils.windowResize(chart.update);

    return chart;
  });
});
});

