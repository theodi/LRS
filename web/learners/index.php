<?php
    $access = "viewer";
    $location = "/learners/index.php";
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');

    if ($theme == "dlab") {
        $url = "dlab.php";
        $string = '<script type="text/javascript">';
        $string .= 'window.location = "' . $url . '"';
        $string .= '</script>';

        echo $string;
        exit(1);
    }
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
                <th>Completed Courses</th>
                <th>Courses in Progress</th>
                <th>eLearning modules completed/active</th>
                <th>Location</th>
                <th>Badges</th>
        <th class="none">Credits</th>
        <th class="none">Theme</th>
        <th class="none">Completed courses</th>
        <th class="none">Courses in progress</th>
        </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
</table>

<script>
/*
TODO: Convert this to use more of the courses data and provide links.
*/
function getModuleTable(d,data,modules) {
    append = "";
    $.each(data, function (modid,moddata) {
        if (typeof moddata === "object") {
            append += '<tr><td>';
            try {
                append += modules[modid]["displayTitle"];
            } catch(err) {
                append += modid;
            }
            append += '</td><td>';
            try {
                done = false;
                if (typeof moddata["answers"] !== "undefined") {
                    if (moddata["progress"] > 99 || moddata["answers"]["_assessmentState"] == "Passed" || moddata["answers"]["_assessmentState"] == "Failed") {
                        append += '<span id="tick">&#10004;</span>';
                        done = true;
                    }
                }
                if (moddata["progress"] > 99 && !done) {
                        append += '<span id="tick">&#10004;</span>';
                } else if (!done) {
                    append += '<progress max="100" value="'+moddata["progress"]+'"></progress>';
                }
            } catch(err) {
                console.log(err);
                append += '<progress max="100" value="0"></progress>';
            }
            append += '</td></tr>';
        }
    });
    return append;
}

$(document).ready(function() {
  countries = {};
  modules = {};
  $.getJSON("../api/v1/countries.php", function(data) {
    countries = data;
  });
  modules = {};
  $.getJSON( "../api/v1/modules.php", function( data ) {
    modules = data["data"];
  });
  $.getJSON( "../api/v1/courses.php", function( data ) {
  	data = data["data"];
  	var courses = {};
  	$.each(data, function(key,val) {
  		courses[val['ID']] = val;
        try {
            courses[val['_trackingHub']['_courseID'].replace(/\./g,'_')] = val;
        } catch(err) {
        }
  	});
	var table = $('#learners').DataTable({
		"responsive": true,
		"ajax": "../api/v1/generate_user_summary.php",
       	"columns": [
            { "data": function(d) {
                if (d["user"]["firstname"]) {
                    return d["user"]["firstname"];
                } 
                return "";
            } },
            { "data": function(d) {
                if (d["user"]["lastname"]) {
                    return d["user"]["lastname"];
                } 
                return "";
            } },
            { "data": function(d) {
                try {
                    return d["user"]["email"];
                } catch (err) {
                    return "";
                }
                return "";
            } },
            { "data": function(d) {
                try {
					return Object.keys(d["courses"]["complete"]).length + Object.keys(d["eLearning_courses"]["complete"]).length;
                } catch (err) {
                    return 0;
                }
            } },
            { "data": function(d) {
                try {
					return Object.keys(d["eLearning_courses"]["active"]).length;
				} catch(err) {
					return 0;
				}
            } },
            { "data": function(d) {
                completed = 0;
                active = 0;
                in_progress = 0;
                try {
                    completed = Object.keys(d["eLearning"]["complete"]).length;
                } catch(err) {}
                try {
                    active = Object.keys(d["eLearning"]["active"]).length;
                } catch(err) {}
                try {
                    in_progress = Object.keys(d["eLearning"]["in_progress"]).length;
                } catch(err) {}
                return completed + " / " + (active + in_progress);
            } },
            { "data" : function(d) {
                try { 
                    if (d["user"]["country"] != "" && d["user"]["region"]) {
                        img = '<img src="../images/blank.gif" class="flag ' + d["user"]["country"] + ' fnone"><br/>'; 
                        return img + countries[d["user"]["country"]]["name"] + " (" + d["user"]["region"] + ")"; 
                    } else if (d["user"]["country"]) {
                        img = '<img src="../images/blank.gif" class="flag ' + d["user"]["country"] + ' fnone"><br/>'; 
                        return img + countries[d["user"]["country"]]["name"];
                    }
                } catch(err) {
                    return "";
                }
                
                return "";
            }},
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
            { "data" : function(d) {
                try { if (d["eLearning"]["theme"]) { return d["eLearning"]["theme"]; } } catch(err) {}
                return "-";
            }},
    	    { "data": function(d) {
        		ret = "<ul>";
    		    try {
        			$.each(d["courses"]["complete"], function(item,data) {
        				if (courses[data["id"]]) {
        					ret += "<li>" + courses[data["id"]]['title'] + "</li>";
        				} else {
        					ret += "<li>" + value + "</li>";
        				}
        			});
        		} catch (err) {}
                try {
                    ret = "<ul>";
                    append = "";
                    $.each(d["eLearning_courses"]["complete"], function(item,data) {
                        if (courses[data["courseID"]]) {
                            ret += "<li>" + courses[data["courseID"]]["title"];
                            ret += "<br/><table><tr><th>Module</th><th>Progress</th></tr>";
                            ret += getModuleTable(d,data,modules);
                            ret += "</table>";                        
                            ret += "</li>";
                        } else {
                            ret += "<li>" + data["courseID"].replace(/_/g,'.') + "</li>";
                        }
                    });
                } catch (err) {}
        		ret +="</ul>";
    		return ret;
    	    }},
            { "data": function(d) {
                ret = "<ul>";
                append = "";
                try {
                    $.each(d["eLearning_courses"]["active"], function(item,data) {
                        if (courses[data["courseID"]]) {
                            ret += "<li>" + courses[data["courseID"]]["title"];
                        } else {
                            ret += "<li>" + data["courseID"].replace(/_/g,'.');
                        }
                        ret += "<br/><table><tr><th>Module</th><th>Progress</th></tr>";
                        ret += getModuleTable(d,data,modules);
                        ret += "</table>";                        
                        ret += "</li>";
                    });
                } catch (err) {
                }
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
