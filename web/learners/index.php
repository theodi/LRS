<?php
	$location = "/learners/index.php";
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css"/>
<p><b>Note:</b> This page only shows profiles for learners with known email addresses. It does not show anonymous eLearning profile data.</p><br/>
<div id="loading" align="center" style="margin: 2em;">
	<img src="../images/ajax-loader.gif" alt="Loading"/>
	<br/>
	<b style="font-size: 2em;">Loading</b>
</div>
<table id="learners" class="display" cellspacing="0" width="100%" style="display: none;">
	<thead>
            <tr>
                <th>First Name</th>
                <th>Surname</th>
                <th>email</th>
                <th>Completed F2F courses</th>
                <th>Completed online modules</th>
                <th>Credits</th>
                <th>Badges</th>
		<th class="none">F2F courses complete</th>
		<th class="none">eLearning modules complete</th>
            </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
</table>

<script>
$(document).ready(function() {
  $.getJSON( "../api/v1/courses.php", function( data ) {
  	data = data["data"];
  	var courses = {};
  	$.each(data, function(key,val) {
  		courses[val['id']] = val['title'];
  	});
	var table = $('#learners').DataTable({
		"responsive": true,
		"ajax": "../api/v1/generate_user_summary.php",
       	"columns": [
            { "data": "First Name" },
	        { "data": "Surname" },
            { "data": "Email" },
            { "data": function(d) {
            	if (d["courses"]["complete"]) {
					return Object.keys(d["courses"]["complete"]).length;
				} else {
					return 0;
				}
            } },
            { "data": function(d) {
            	if (d["eLearning"]["complete"]) {
					return Object.keys(d["eLearning"]["complete"]).length;
				} else {
					return 0;
				}
            } },
            { "data": "totalCredits" },
            { "data": function(d) {
            	badgesComplete = "";
            	if (typeof(d["badges"]) != "undefined") {
			badges = d["badges"]["complete"];
			for(i=0;i<badges.length;i++) {
				badgesComplete += "<img src='"+badges[i]['url']+"' alt='"+badges[i]['id']+"'/>";
			}
			return badgesComplete;
		}
		return "";
            }},
	    { "data": function(d) {
    		ret = "<ul>";
		if (d["courses"]["complete"]) {
    			$.each(d["courses"]["complete"], function(key,value) {
    				if (courses[value]) {
    					ret += "<li>" + courses[value] + "</li>";
    				} else {
    					ret += "<li>" + value + "</li>";
    				}
    			});
    		}
    		ret +="</ul>";
		return ret;
	    }},
	    { "data": function(d) {
    		ret = "<ul>";
		if (d["eLearning"]["complete"]) {
			$.each(d["eLearning"]["complete"], function(key,value) {
    				ret += "<li>" + value + "</li>";
    			});
		}
    		ret +="</ul>";
	    	return ret;
	    }}
	   ],
	   "pageLength": 50,
	   "order": [[ 5, "desc" ], [0, "asc"]]
	});
	$('#loading').fadeOut();
	$('#learners').fadeIn("slow");
  });
});
</script>
<?php

	include('_includes/footer.html');
?>
