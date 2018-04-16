<?php
    $access = "viewer";
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	$module = $_GET["module"];
    $date = $_GET["date"];
	$path = "../";
    if ($theme == "dlab") {
        $url = "dlab.php";
        $string = '<script type="text/javascript">';
        $string .= 'window.location = "' . $url . '?' . $_SERVER['QUERY_STRING'] . '"';
        $string .= '</script>';

        echo $string;
        exit(1);
    }
?>
	<script>
	var module = "<?php echo $module; ?>";
	var theme = "<?php echo $theme; ?>";
    var date = "<?php echo $date; ?>";
	</script>
<?php
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	if ($_GET["format"] == "eLearning") {
		include('dashboard/board.html');
        $title = "Active users table";
        $content = "<b>Note:</b> This page only shows profiles for learners with known email addresses. It does not show anonymous eLearning profile data.</p>";
	} else {
        $title ="Course attendance data";
        $content = "";
    }
?>
	<section style="width: 100%;">
    <h3><?php echo $title;?></h3>
    <p><?php echo $content;?></p>
    <div id="loading" align="center" style="margin: 2em;">
        <img src="../images/ajax-loader.gif" alt="Loading"/>
        <br/>
        <b style="font-size: 2em;">Loading</b>
    </div>
    <table id="learners" class="display" cellspacing="0" width="100%" style="display: none;">
        <thead>
            <tr>
                <th>email/id</th>
                <th>Complete</th>
                <th>First Name</th>
                <th>Surname</th>
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
    <script type='text/javascript' src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
    <script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
    <script type='text/javascript' src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
    <script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
    <script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
    <script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
    <script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css"/>
<?php
	include('_includes/footer.html');
?>
<script>
$(document).ready(function() {
    module = "<?php echo $module; ?>";
    format = "<?php echo $_GET['format']; ?>";
    $.getJSON( "../api/v2/getModule.php?module=<?php echo $module; ?>", function( data ) {
        $('header .container h1').html(data["title"]);
    });
    var table = $('#learners').DataTable({
        "responsive": true,
        "ajax": "../api/v2/getModuleUsers.php?module=<?php echo $module; ?>",
        "columns": [
            { "data": function(d) {
                if (d["user"]["email"]) {
                    return d["user"]["email"];
                } else {
                    return d["user"]["id"];
                }
            } },
            { "data": function(d) {
                try { if (d["progress"]["isComplete"] === true) { return '<progress max="100" value="100">100</progress>'; }} catch(err) {}
                try { if (d["progress"]["progress"]) { return '<progress max="100" value="'+d["progress"]["progress"]+'">'+d["progress"]["progress"]+'</progress>'; }} catch(err) {}
                return "-";
            }},
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
            } }
            
       ],
       "pageLength": 20,
       "order": [[ 1, "desc" ], [0, "asc"]],
       "dom": 'Bfrtip',
       "buttons": [
            'copy', 'csv', 'excel', 'pdf', 'print'
       ]
    });
    $('#loading').fadeOut();
	$('#learners').fadeIn("slow");
});
</script>