
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
var completePie = dc.pieChart('#complete-pie');
var passedPie = dc.pieChart('#passed-pie');
var emailPie = dc.pieChart('#email-pie');
var platformPie = dc.pieChart('#platform-pie');
var themeLine = dc.rowChart('#theme-line');
var langLine = dc.rowChart('#lang-line');
var completeBar = dc.barChart('#complete-bar');
var timeBar = dc.barChart('#time-bar');
var questionIDs = [];

d3.csv('../api/v1/data2.php?module='+module+'&theme='+theme, function (data) {
    var ndx = crossfilter(data);
    var all = ndx.groupAll();

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
    console.log(range);


    questionsHTML = "";
    for (var key in questions) {
      questionid = "question_"+key;
      questionsHTML = "";
      questionsHTML += "<subsection id='"+questionid+"' class='question'><table id='table_"+key+"'></table></subsection>";
      $('#questions').append(questionsHTML);
      for(var part in questions[key]) {
        lkey = key+"_"+part;
        docid = "question_"+lkey;
        labelid = "label_"+lkey;
        questionsHTML = "";
        questionsHTML += "<tr><td class='label-cell'><div class='label' id='" + labelid + "'>Question answer</div></td>";
        questionsHTML += "<td><div id='" + docid + "' class='dc-chart question-chart'></div></td></tr>";
        $('#table_'+key).append(questionsHTML);
      }
      $.getJSON( "../api/v1/getComponent.php?id="+key, function( data ) {
        questionData[data["_id"]] = data;
        console.log(data);
        title = "<h3>" + data["title"] +"</h3><div class='questionText'>" + data["body"] + "</div>";
        $('#question_'+data["_id"]).prepend(title);
        items = data["_items"];
        for(i=0;i<items.length;i++) {
          console.log('#label_'+data["_id"]+'_'+i);
          console.log(items[i]["text"]);
          $('#label_'+data["_id"]+'_'+i).html(items[i]["text"]);
        }
        $('#label_'+data["_id"]+'_isCorrect').html('Correct users')
      });
    }
    
    var complete = ndx.dimension(function(d) {
        return d.complete;
    });
    
    var completeGroup = complete.group();

    completePie
    .width(160)
    .height(160)
    .radius(80)
    .dimension(complete)
    .group(completeGroup)
//        .ordinalColors(['green', 'red')
    .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
    .label(function (d) {
        var label = d.key;
        return label;
    });

    var percent = ndx.dimension(function(d) {
      value = Math.round(d.completion * 10);
      return +value;
    });

    var percentGroup = percent.group();

    completeBar
    .width(360)
    .height(160)
    .dimension(percent)
    .group(percentGroup)
    .x(d3.scale.linear().domain([0,11]))
    .gap(0.1)
    .brushOn(true);

    var passed = ndx.dimension(function(d) {
        return d.passed;
    });

    var passedGroup = passed.group();

    passedPie
    .width(160)
    .height(160)
    .radius(80)
    .dimension(passed)
    .group(passedGroup)
    //        .ordinalColors(['green', 'red')
    .colors(d3.scale.ordinal().domain(["true","false","not attempted"]).range(['blue','black','gray']))
    .label(function (d) {
        var label = d.key;
        return label;
    });

    var email = ndx.dimension(function(d) {
        return d.email;
    });

    var emailGroup = email.group();

    emailPie
    .width(160)
    .height(160)
    .radius(80)
    .dimension(email)
    .group(emailGroup)
    //        .ordinalColors(['green', 'red')
    .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
    .label(function (d) {
        var label = d.key;
        return label;
    });

    var platform = ndx.dimension(function(d) {
        return d.platform;
    });

    var platformGroup = platform.group();

    platformPie
    .width(160)
    .height(160)
    .radius(80)
    .dimension(platform)
    .group(platformGroup)
    //        .ordinalColors(['green', 'red')
    .label(function (d) {
        var label = d.key;
        return label;
    });

    var theme = ndx.dimension(function(d) {
        return d.theme;
    });

    var themeGroup = theme.group();

    themeLine
    .width(320)
    .height(160)
    .dimension(theme)
    .group(themeGroup)
    .label(function (d) {
        var label = d.key;
        return label;
    });

    var lang = ndx.dimension(function(d) {
        return d.lang;
    });

    var langGroup = lang.group();

    langLine
    .width(320)
    .height(160)
    .dimension(lang)
    .group(langGroup)
    //        .ordinalColors(['green', 'red')
    //        .colors(d3.scale.ordinal().domain(["true","false"]).range(['blue','gray']))
    .label(function (d) {
        var label = d.key;
        return label;
    });

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
    	if (total > 10) {
    		total = 10;
    	}
        return +total;
    });

    var timeGroup = time.group();

    timeBar
    .width(360)
    .height(160)
    .dimension(time)
    .group(timeGroup)
    .x(d3.scale.linear().domain([0,11]))
    .gap(0.1)
    .brushOn(true);

    var id = ndx.dimension(function(d) {
    	return d.id;
    });


    var percent = ndx.dimension(function(d) {
    	value = Math.round(d.completion * 10);
        return +value;
    });

    function remove_empty_bins(source_group) {
        return {
            all:function () {
                return source_group.all().filter(function(d) {
                    return d.key != "no answer";
                });
            }
        };
    }

    for (var key in questions) {
      for(var part in questions[key]) {
        lkey = key+"_"+part;
        docid = "question_"+lkey;
        questionIDs[lkey] = dc.rowChart('#' + docid);
      
        var dimension = ndx.dimension(function(d) {
          if (d[lkey] == "") {
            return "no answer";
          } else {
            return d[lkey];
          }
        });
        var group = dimension.group();
        var group2 = remove_empty_bins(group);
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
          .labelOffsetX(function(d) {
            return d.value;
          })
          .elasticX(false)
          .xAxis().ticks(0)

      }
    }

    var count = function() {
    	number = complete.top(Number.POSITIVE_INFINITY).length;
    	document.getElementById('total').innerHTML = number;
    }
    setInterval(function() { count(); },1000); 

    dc.renderAll();
});

