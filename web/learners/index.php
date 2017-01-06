<?php
    $access = "viewer";
	$location = "/learners/index.php";
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
                <th>Theme</th>
                <th>Badges</th>
        <th class="none">Location</th>
        <th class="none">F2F courses complete</th>
		<th class="none">eLearning modules complete</th>
        <th class="none">eLearning modules active</th>
            </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
</table>

<script>
/*
TODO: Convert this to use more of the courses data and provide links.
*/
$(document).ready(function() {
  countries = {};
  $.getJSON("../api/v1/countries.php", function(data) {
    countries = data;
  });
  $.getJSON( "../api/v1/courses.php", function( data ) {
  	data = data["data"];
  	var courses = {};
  	$.each(data, function(key,val) {
  		courses[val['ID']] = val['title'];
  	});
	var table = $('#learners').DataTable({
		"responsive": true,
		"ajax": "../api/v1/generate_user_summary.php",
       	"columns": [
            { "data": "First Name" },
	        { "data": "Surname" },
            { "data": "Email" },
            { "data": function(d) {
                try {
					return Object.keys(d["courses"]["complete"]).length;
                } catch (err) {
                    return 0;
                }
            } },
            { "data": function(d) {
                try {
					return Object.keys(d["eLearning"]["complete"]).length;
				} catch(err) {
					return 0;
				}
            } },
            { "data": "totalCredits" },
            { "data" : function(d) {
                try { if (d["eLearning"]["theme"]) { return d["eLearning"]["theme"]; } } catch(err) {}
                return "-";
            }},
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
            { "data" : function(d) {
                try { 
                    if (d["Country"] && d["Region"]) {
                        img = '<img src="../images/blank.gif" class="flag ' + d["Country"] + ' fnone"> '; 
                        return img + countries[d["Country"]]["name"] + " (" + d["Region"] + ")"; 
                    } else if (d["Country"]) {
                        img = '<img src="../images/blank.gif" class="flag ' + d["Country"] + ' fnone"> '; 
                        return img + countries[d["Country"]]["name"];
                    }
                } catch(err) {}
                
                return "Unknown";
            }},
	    { "data": function(d) {
    		ret = "<ul>";
		    try {
    			$.each(d["courses"]["complete"], function(item,data) {
    				if (courses[data["id"]]) {
    					ret += "<li>" + courses[data["id"]] + "</li>";
    				} else {
    					ret += "<li>" + value + "</li>";
    				}
    			});
    		} catch (err) {}
    		ret +="</ul>";
		return ret;
	    }},
	    { "data": function(d) {
    		ret = "<ul>";
		    try {
			    $.each(d["eLearning"]["complete"], function(item,data) {
    				if (courses[data["id"]]) {
                        ret += "<li>" + courses[data["id"]] + "</li>";
                    } else {
                        ret += "<li>" + value + "</li>";
                    }
    			});
		    } catch (err) {}
    		ret +="</ul>";
	    	return ret;
	    }},
        { "data": function(d) {
            ret = "<ul>";
            try {
                $.each(d["eLearning"]["active"], function(item,data) {
                    if (courses[data["id"]]) {
                        ret += "<li>" + courses[data["id"]] + "</li>";
                    } else {
                        ret += "<li>" + value + "</li>";
                    }
                });
            } catch (err) {}
            ret +="</ul>";
            return ret;
        }}
	   ],
	   "pageLength": 50,
	   "order": [[ 5, "desc" ], [0, "asc"]],
       "dom": 'Bfrtip',
       "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
       ]
	});
	$('#loading').fadeOut();
	$('#learners').fadeIn("slow");
  });
});
</script>
<?php

	include('_includes/footer.html');
?>
