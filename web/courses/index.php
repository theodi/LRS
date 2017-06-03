<?php
	$access = "public";
	$location = "/courses/index.php";
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.js"></script>
<script type='text/javascript' src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script type='text/javascript' src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css"/>
<style>
table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
	margin-top: 21px;
}
.dtr-data {
	display: inline-block;
}
</style>
<div id="loading" align="center" style="margin: 2em;">
	<img src="../images/ajax-loader.gif" alt="Loading"/>
	<br/>
	<b style="font-size: 2em;">Loading</b>
</div>
<table id="courses" class="display" cellspacing="0" width="100%" style="display: none;">
	<thead>
            <tr>
                <th>Course name</th>
                <th>Credits</th>
                <th>Type</th>
                <th>Dashboard</th>
                <th class="none">ID</th>
                <th class="none instances">Instances</th>
                <th class="none">Description</th>
            </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
</table>

<script>
function renderCredits(course) {
	if (course["credits"] == "") {
		course["credits"] = new Array();
		course["credits"]["explorer"] = "-";
		course["credits"]["strategist"] = "-";
		course["credits"]["practitioner"] = "-";
		course["credits"]["pioneer"] = "-";
	}
	if (!course["credits"]["explorer"]) { course["credits"]["explorer"] = 0; }
	if (!course["credits"]["strategist"]) { course["credits"]["strategist"] = 0; }
	if (!course["credits"]["practitioner"]) { course["credits"]["practitioner"] = 0; }
	if (!course["credits"]["pioneer"]) { course["credits"]["pioneer"] = 0; }
	
	ret = '<div id="course_credits_box">';
	ret += '<score>'+course["totalCredits"]+'</score>';
	ret += '<div id="course_credits_table">';
	ret += '<div>explorer<span class="credits">'+course["credits"]["explorer"]+'</span></div>';
	ret += '<div>strategist<span class="credits">'+course["credits"]["strategist"]+'</span></div>';
	ret += '<div>practitioner<span class="credits">'+course["credits"]["practitioner"]+'</span></div>';
	ret += '<div>pioneer<span class="credits">'+course["credits"]["pioneer"]+'</span></div>';
	ret += '</div>';
	ret += '</div>';
	return ret;
}
var instancesDone = [];
$(document).ready(function() {
	var table = $('#courses').DataTable({
		"responsive": true,
		"ajax": "../api/v1/courses.php",
       	"columns": [
            	{ "data": function(d) {
            		if (d["url"]) {
            			return "<a href='"+d["url"]+"' target='_blank'>" + d["title"] + "</a>"
            		}
            		return d["title"];
            	}},
            	{ "data": function(d) { return renderCredits(d); } },
            	{ "data": function(d) {
					output = '<span style="display: none;">'+d["format"]+'</span><img style="max-height: 40px;" src="/images/';
        			output += d["format"];
        			output += '.png"';
					output += ' title="';
					if (d["format"] == "course") {
						output += 'Face to face course';
					} else {
						output += d["format"];
					}
					output += '"></img>';
						return output;
            	}},
            	{ "data": function(d) {
					id = d["id"];
					format = d["format"];
                    return '<a class="dt-button" href="../dashboard/index.php?module=' + d["ID"] + '&format='+format+'">View Dashboard</a>';
	    		}},
	    		{ "data": "ID" },
	    		{ "data": function(d) {
	    			if (d["format"] == "course") {
	    				//getDataForCourse(d["ID"],d["format"]);
	    				return "<span id='instances" + d["ID"] + "'>Hidden</span>";
	    			} else {
	    				return "eLearning";
	    			}
	    		}},
	    		{ "data": function(d) {
	    			if (d["body"]) {return d["body"];} else {return "";}
	    		}}
	   	],
	   	"pageLength": 25,
	   	"order": [[ 2, "asc" ], [1,"desc"], [0, "asc"]],
	   	"dom": 'Bfrtip',
       	"buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
       	]
	});
	$('#loading').fadeOut();
	$('#courses').fadeIn("slow");
	$('#courses tbody').on('click', 'td', function () {
    	var data = table.row($(this).parents('tr')).data();
    	$('li #instances' + id).html("Please wait...");
    	getDataForCourse(data["ID"],data["format"]);
	});
});

function getDataForCourse(id,format) {
	$.getJSON("../api/v1/generate_user_summary.php?course=" + id, function(instances) {
		console.log(instances);
	    dates = [];
	    output = "";
	    instances = instances.data;
	    for (i=0;i<instances.length;i++) {
	    	complete = instances[i]["courses"]["complete"];
	    	for (j=0;j<complete.length;j++) {
	    		indate = complete[j]["date"].trim();
	    		var split = indate.split('/');
					var date = new Date(split[2], split[1] - 1, split[0]); //Y M D
					dates[date] = true;
					Object.keys(dates).sort();
				}
			}
			toSort = [];
			for (var key in dates) {
				obj = {};
				obj.date = key;
				toSort.push(obj);
			}
			toSort.sort(function(a, b){
    			var keyA = new Date(a.date),
        		keyB = new Date(b.date);
    			// Compare the 2 dates
    			if(keyA < keyB) return -1;
    			if(keyA > keyB) return 1;
    			return 0;
			});
			for(i=0;i<toSort.length;i++) {
				date = new Date(toSort[i].date);
				dateString = ("0" + date.getDate()).slice(-2) + "/" + ("0" + (date.getMonth() + 1)).slice(-2) + "/" + date.getFullYear();
				output += "<a href='../dashboard/index.php?module=" + id + "&format=" +format+"&date="+dateString+"'>" + dateString + "</a><br/>";
				$('li #instances' + id).html(output);
			}
	});
}
</script>
<?php

	include('_includes/footer.html');
?>
