<?php
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	$module = $_GET["module"];
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	if ($_GET["format"] == "eLearning") {
		include('dashboard/board.html');	
	}
?>
	<section>
    <h3>Completed users table</h3>
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
                <th>Badges</th>
            </tr>
        </thead>
        <tbody id="tableBody">
        </tbody>
    </table>
    </section>	
    <!--<link rel='stylesheet prefetch' href='css/bootstrap.min.css'>-->
    <link rel='stylesheet prefetch' href='css/dc.css'>
    <link rel='stylesheet prefetch' href='css/style.css'/>
    <script src="js/jquery-2.1.4.min.js"></script>
    <script src='js/d3.min.js'></script>
    <script src='js/crossfilter.js'></script>
    <script src='js/dc.js'></script>
    <script src='js/colorbrewer.js'></script>
    <script src='js/eLearning.js'></script>
    <script type="text/javascript" src="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css"/>
<?php
	include('_includes/footer.html');
?>
<script>
$(document).ready(function() {
    var table = $('#learners').DataTable({
        "responsive": true,
        "ajax": "../api/v1/generate_user_summary.php?course=<?php echo $module; ?>",
        "columns": [
            { "data": "First Name" },
            { "data": "Surname" },
            { "data": "Email" },
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
            }}
       ],
       "pageLength": 50,
       "order": [[ 1, "asc" ], [0, "asc"]]
    });
    $('#loading').fadeOut();
	$('#learners').fadeIn("slow");
});
</script>