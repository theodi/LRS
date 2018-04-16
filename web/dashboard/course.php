<?php
    $access = "viewer";
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include_once('library/functions.php');
	include_once('_includes/header.php');
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
<script>
$(document).ready( function () {
    $('#courseTable').DataTable({
		"dom": 'Bfrtip',
        "buttons": [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "searching": true,
        "responsive": true,
        "pageLength": 20
    });
} );
</script>
<style>
.mod_name {
	font-size: 0.8em;
	word-wrap: break-word;
}
.container {
	width: 90%;
}
progress {
	width: 5em;
}
progress#progressBar:hover:after {
    display: block;
    content: attr(value);
}
tbody td {
	text-align: center;
}
</style>

<?php
	$course = $_GET["course"];
	$courseData = getCourseData($course);

	echo '<h2>' . $courseData['title'] . '</h2>';

	echo '<table id="courseTable"><thead><tr><th>Learner</th>';

	$modules = $courseData["modules"];
	for($i=0;$i<count($modules);$i++) {
		echo '<th class="mod_name"><span class="mod_title">' . $modules[$i]["title"] . '<span></th>';
	}
	echo '<th>Overall</th>';
	echo '</tr></thead>';

	echo '<tbody>';

	$learnerData = getLearnerData($course);
	outputLearnerData($learnerData,$course,$modules);

	if ($courseData["_trackingHub"]["_courseID"]) {
		$course = str_replace(".","_",$courseData["_trackingHub"]["_courseID"]);
		$learnerData = getLearnerData($course);
		outputLearnerData($learnerData,$course,$modules);
	}
	
	echo '</tbody></table>';

	include_once('_includes/footer.html');

function outputLearnerData($learnerData,$course,$modules) {

	for($i=0;$i<count($learnerData);$i++) {
		$learner = $learnerData[$i];
		echo '<tr><td>';
		if ($learner["user"]["email"]) {
			echo $learner["user"]["email"];
		} else {
			echo $learner["user"]["id"];
		}
		echo '</td>';
		$progress = $learner[$course]["progress"];
		$overall = 0;
		for ($j=0;$j<count($modules);$j++) {
			$id = $modules[$j]["_id"];
			$data = $progress[$id];
			echo '<td>';
			if ($data["progress"] > 99 || $data["answers"]["_assessmentState"] == "Passed" || $data["answers"]["_assessmentState"] == "Failed") {
				echo '<progress max="100" value="100">100</progress>';
            	//echo '<span id="tick">&#10004;</span>';
            	$overall += 100;
            } elseif ($data["progress"] > 99) {
            	echo '<progress max="100" value="'.$data["progress"].'">'.$data["progress"].'</progress>';
            	//echo '<span id="tick">&#10004;</span>';
            	$overall += 100;
        	} else {
	            echo '<progress max="100" value="'.$data["progress"].'">'.$data["progress"].'</progress>';
	            $overall += $data["progress"];
        	}
			echo '</td>';
		}
		echo '<td>';
		$total = $overall / count($modules);
		echo '<progress max="100" value="'.$total.'">'.$total.'</progress>';
		echo '</td>';
		echo '</tr>';
	}
} 

?>