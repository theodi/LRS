
function getUrlVars() {
	var vars = {};
	var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
		vars[key] = value;
	});
	return vars;
}

function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

var module = getUrlVars()["module"];
var completeLine = dc.rowChart('#complete-line');
var passedLine = dc.rowChart('#assessment-line');
var platformLine = dc.rowChart('#platform-line');
var langLine = dc.rowChart('#lang-line');
var emailLine = dc.rowChart('#email-line');
var themeLine = dc.rowChart('#theme-line');
var completeBar = dc.barChart('#percentage-bar');
var timeBar = dc.barChart('#time-bar');
var questionIDs = [];

d3.csv('../api/v1/data2.php?module='+module+'&theme='+theme, function (data) {
    var ndx = crossfilter(data);
    var all = ndx.groupAll();
    var doneLabels = [];

    questions = {};
    questionData = {};
    data.forEach(function(row) {
      keys = Object.keys(row);
      for (i=9;i<keys.length;i++) {
        questionId = keys[i].substr(0,keys[i].lastIndexOf("_"));
        questionPart = keys[i].substr(keys[i].lastIndexOf("_")+1,keys[i].length);
        if (questionId) {
          if (!questions[questionId]) questions[questionId] = {};
          questions[questionId][questionPart] = true;
        }
      }
    });

    var topCount = {};
    data.forEach(function(row) {
      for(var key in questions) {
        if (!topCount[key]) topCount[key] = {};
        for(var part in questions[key]) {
          if (!topCount[key][part]) topCount[key][part] = {};
          if(row[key+"_"+part] != "" && row[key+"_"+part]) {
            if (!topCount[key][part][questions[key][part]]) topCount[key][part][questions[key][part]] = 0;
            topCount[key][part][questions[key][part]]++;
          }
        }
      }
    });

    var range = {};
    for (var key in questions) {
      range[key] = 0;
      for(var part in questions[key]) {
          if (topCount[key][part][questions[key][part]] > range[key]) {
            range[key] = topCount[key][part][questions[key][part]];
          }
      }
    }

    questionsHTML = "";
    for (var key in questions) {
      questionid = "question_"+key;
      questionsHTML = "";
      questionsHTML += "<subsection id='"+questionid+"' class='question'>";
      questionsHTML += "<div class='correct_block'><div class='label'>Correct answers:</div><div id='question_" + key + "_isCorrect' class='number-chart'></div></div>"
      questionsHTML += "<table id='table_"+key+"'></table></subsection>";
      $('#questions').append(questionsHTML);
      for(var part in questions[key]) {
        if (part == "isCorrect") {
          continue;
        }
        lkey = key+"_"+part;
        docid = "question_"+lkey;
        labelid = "label_"+lkey;
        questionsHTML = "";
        questionsHTML += "<tr><td class='label-cell'><div class='label' id='" + labelid + "'>Question answer</div></td>";
        questionsHTML += "<td><div id='" + docid + "' class='dc-chart question-chart'></div></td></tr>";
        $('#table_'+key).append(questionsHTML);
      }
      $.getJSON( "../api/v1/getComponent.php?id="+key+"&module="+module, function( data ) {
        console.log(data);
        questionData[data["_id"]] = data;
        title = "<div class='questionText'><h3>" + data["title"] +"</h3>" + data["body"] + "</div>";
        $('#question_'+data["_id"]).prepend(title);
        items = data["_items"];
        for(i=0;i<items.length;i++) {
          if (items[i]["_shouldBeSelected"]) {
            $('#label_'+data["_id"]+'_'+i).parent().addClass("correct");
          }
          $('#label_'+data["_id"]+'_'+i).html(items[i]["text"]);
        }
        $('#label_'+data["_id"]+'_isCorrect').html('Correct users')
      });
    }
    
    function remove_empty_bins(source_group) {
        return {
            all:function () {
                return source_group.all().filter(function(d) {
                    return d.key != "";
                });
            },
            top:function () {
              return source_group.all().filter(function(d) {
                    return d.key != "";
                });
            }
        };
    }

    var complete = ndx.dimension(function(d) {
        return d.complete;
    });
    
    var completeGroup = complete.group();
    doneLabels["complete"] = [];

    completeLine
    .width(320)
    .height(150)
    .dimension(complete)
    .group(completeGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      labelText = "Incomplete";
      if (d.key == "true") { labelText = "Complete"; }
      if (!doneLabels["complete"][d.key]) {
        $('#complete-labels').append("<div class='chart-label complete-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["complete"][d.key] = true;
        height = 100 / Object.keys(doneLabels["complete"]).length;
        $('.complete-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);

    var passed = ndx.dimension(function(d) {
        return d.passed;
    });

    var passedGroup = passed.group();

    doneLabels["passed"] = [];
    passedLine
    .width(320)
    .height(150)
    .dimension(passed)
    .group(passedGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      if (!doneLabels["passed"][d.key]) {
        labelText = "Not attempted";
        if (d.key == "true") { labelText = "Passed";}
        if (d.key == "false") {labelText = "Failed";}
        $('#assessment-labels').append("<div class='chart-label assessment-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["passed"][d.key] = true;
        height = 100 / Object.keys(doneLabels["passed"]).length;
        $('.assessment-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);

    var lang = ndx.dimension(function(d) {
      if (d.lang == "" || typeof d.lang == 'undefined') {
          return "";
      } else {
          return d.lang;
      }
    });

    var langGroup = lang.group();
    langGroup = remove_empty_bins(langGroup);

    doneLabels["language"] = [];

    langLine
    .width(320)
    .height(150)
    .dimension(lang)
    .group(langGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      if (!doneLabels["language"][d.key]) {
        labelText = d.key;
        $('#language-labels').append("<div class='chart-label language-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["language"][d.key] = true;
        height = 100 / Object.keys(doneLabels["language"]).length;
        $('.language-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);

    var platform = ndx.dimension(function(d) {
        if (d.platform == "" || typeof d.platform == 'undefined') {
        return "web";
      } else {
        return d.platform;
      }
    });

    var platformGroup = platform.group();
    doneLabels["platform"] = [];

    platformLine
    .width(320)
    .height(150)
    .dimension(platform)
    .group(platformGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      if (!doneLabels["platform"][d.key]) {
        labelText = d.key;
        $('#platform-labels').append("<div class='chart-label platform-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["platform"][d.key] = true;
        height = 100 / Object.keys(doneLabels["platform"]).length;
        $('.platform-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);


    var email = ndx.dimension(function(d) {
        return d.email;
    });

    var emailGroup = email.group();
    doneLabels["email"] = [];

    emailLine
    .width(320)
    .height(150)
    .dimension(email)
    .group(emailGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      if (!doneLabels["email"][d.key]) {
        labelText = "No";
        if (d.key == "true") { labelText = "Yes"; }
        $('#email-labels').append("<div class='chart-label email-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["email"][d.key] = true;
        height = 100 / Object.keys(doneLabels["email"]).length;
        $('.email-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);

    var theme = ndx.dimension(function(d) {
      if (d.theme == "" || typeof d.theme == 'undefined') {
        return "ODI";
      } else {
        return d.theme;
      }
    });

    var themeGroup = theme.group();
    doneLabels["theme"] = [];

    themeLine
    .width(320)
    .height(150)
    .dimension(theme)
    .group(themeGroup)
    .margins({top: 0, left: 0, right: 10, bottom: -1})
    .renderLabel(true)
    .label(function (d) {
      if (!doneLabels["theme"][d.key]) {
        labelText = d.key;
        $('#theme-labels').append("<div class='chart-label theme-label'><div class='chart-label-text label'>"+labelText+"</div></div>");
        doneLabels["theme"][d.key] = true;
        height = 100 / Object.keys(doneLabels["theme"]).length;
        $('.theme-label').css('height',height + '%');
      }
      return d.value;
    })
    .elasticX(false)
    .xAxis().ticks(0);


    var percent = ndx.dimension(function(d) {
      //return d.completion;
      value = Math.round(d.completion * 100);
      value = (Math.floor(value / 10) * 10);
      if (value > 99) {
        value = value -10;
      }
      return +value;
    });

    var percentGroup = percent.group().reduceCount();

    completeBar
    .width(400)
    .height(160)
    .dimension(percent)
    .group(percentGroup)
    .x(d3.scale.linear().domain([0,100]))
    .xUnits(function(){return 10;})
    //.yAxisLabel("No. of profiles")
    .gap(0.1)
    .brushOn(true);

    completeBar.margins().left = 40;

    var timeMax = 0;
    var time = ndx.dimension(function(d) {
    	raw = d.session_time.split(":");
    	hours = parseInt(raw[0]);
    	mins = parseInt(raw[1]);
    	secs = parseInt(raw[2]);
    	extra = Math.round(secs / 60);
    	total = (hours * 60) + mins + extra;
    	if (!isInt(total)) {
    		total = 0;
    	}
    	if (total > 20) {
    		total = 20;
    	}
      if (total > timeMax) {timeMax = total;}
      return +total;
    });

    var timeGroup = time.group().reduceCount();

    timeBar
    .width(400)
    .height(160)
    .dimension(time)
    .group(timeGroup)
    .x(d3.scale.linear().domain([0,timeMax]))
    .gap(0.1)
    .brushOn(true);

    timeBar.margins().left = 40;

    var id = ndx.dimension(function(d) {
    	return d.id;
    });


    var percent = ndx.dimension(function(d) {
    	value = Math.round(d.completion * 10);
        return +value;
    });



    for (var key in questions) {
      for(var part in questions[key]) {
        lkey = key+"_"+part;
        docid = "question_"+lkey;

        if (part == "isCorrect") {
          questionIDs[lkey] = dc.numberDisplay('#' + docid);
        } else {
          questionIDs[lkey] = dc.rowChart('#' + docid);
        }

        var dimension = ndx.dimension(function(d) {
          if (d[lkey] == "" || typeof d[lkey] == 'undefined') {
            return "";
          } else {
            return d[lkey];
          }
        });
        var group = dimension.group();
        var group2 = remove_empty_bins(group);

        if (part == "isCorrect") {
          questionIDs[lkey]
            .group(group2)
            .formatNumber(d3.format(".3s"))
        } else {
        questionIDs[lkey]
          .width(320)
          .height(40)
          .dimension(dimension)
          .group(group2)
          .margins({top: 0, left: 0, right: 10, bottom: -1})
          .renderLabel(true)
          .x(d3.scale.linear().range([0,270]).domain([0,range[lkey.substr(0,lkey.lastIndexOf("_"))]]))
          .label(function (d) {
            return d.value;
          })
          .elasticX(false)
          .xAxis().ticks(0)
        }
      }
    }

    var count = function() {
    	number = complete.top(Number.POSITIVE_INFINITY).length;
    	document.getElementById('total_records').innerHTML = number;
    }
    setInterval(function() { count(); },1000); 

    dc.renderAll();
});

