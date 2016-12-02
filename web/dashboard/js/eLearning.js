
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
var expansionDone = false;

d3.csv('../api/v1/data2.php?module='+module+'&theme='+theme, function (data) {
    var expansion = [];
    var ndx = crossfilter(data);
    var all = ndx.groupAll();
    
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

var rowcount = 0; 
data.forEach(function(row) {
	rowcount++;
 if (!expansionDone) {
  titles = [];
  keys = Object.keys(row);
  questionsHTML = "";
  keys.forEach(function(key) {
   if (key.substring(0,2) == "c-" && key.indexOf(":") > 0) {
    re = /^c-\d*[_]/i;
    multi = key.match(re);
    id_key = key.replace(":","");
    id_key = id_key.replace(/ /g,"-");
    id_key = id_key.replace(/\./g,"");
    id_key = id_key.replace("?","");
    title = key.substring(key.indexOf(":")+1,key.length);
    if (!titles[title]) {
     mscount = 0;
     questionsHTML += "</subsection>";
     questionsHTML += "<subsection><h3>" + title + "</h3><div id='" + id_key + "' class='dc-chart";
     questionsHTML += "'></div>";
     titles[title] = true;
     mscount++;
 } else {
     questionsHTML += "<div id='" + id_key + "' class='dc-chart";
     if (multi) {
      questionsHTML += " multiSelection ms" + mscount + " ";
  }
  questionsHTML += "'></div>";
  mscount++;
}
expansion[id_key] = key;
}
});
  questionsHTML += "</subsection>";
  $('#questions').append(questionsHTML);
  expansionDone = true;
}
});

for (var key in expansion) {
	questionIDs[key] = dc.rowChart('#' + key);
	column_title = expansion[key];
	var dimension = ndx.dimension(function(d) {
		if (d[column_title] == "") {
			return "no answer";
		} else {
			return d[column_title];
		}
	});
	var group = dimension.group();
	questionIDs[key]
  .width(320)
  .height(160)
  .dimension(dimension)
  .group(group)
  .margins({top: 0, left: 10, right: 10, bottom: 40})
  .x(d3.scale.linear().range([0,270]).domain([0,rowcount]))
  .label(function (d) {
   var label = d.key;
   return label;
});
}

var count = function() {
	number = complete.top(Number.POSITIVE_INFINITY).length;
	document.getElementById('total').innerHTML = number;
}
setInterval(function() { count(); },1000); 

dc.renderAll();
});

