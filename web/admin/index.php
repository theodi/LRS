<?php
	$access = "admin";
	$requireAdmin = true;
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
  	include_once('library/functions.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>

<h2>Archive empty eLearning profiles</h2>

<section id="archive_elearning">
	Click the button below to archive empty eLearning profiles.<br/><br/>
	<button id="archive_elearning_button">Archive empty profiles</button>
</section>

<h2>Update courses from publisher</h2>

<section id="update_courses">
	Click below to update the list of courses from the publisher (theodi.org) data.<br/><br/>
	<button id="update_courses_button">Update courses from publisher</button>
</section>

<h2>Add adapt course/module</h2>
<section id="add_adapt_course">
	Please enter the url of the course homepage in the box below, it must be publically accessible. (e.g. http://accelerate.theodi.org/en/module1 or http://training.theodi.org/inaday).<br/><br/>
	<input type="text" id="course_url"></input></br>
	<input type="text" id="course_lang" value="en" style="width:20px;"></input></br/>
	<button id="import_adapt_course">Import adapt course</button>
</section>

<h2>Update course identifiers</h2>
<section id="update_identifiers">
	Click the button below to update the mapping of all the different course identifiers, including client mapping.<br/><br/>
	<button id="update_identifiers_button">Update course identifiers</button><br/><br/>
	The course identifier mapping can be edited <a href="https://docs.google.com/spreadsheets/d/17gtugoN05aYnWN07Exf6_RpdknlpCfi6a1WSTyJ_z7c/edit#gid=0" target="_blank">here</a><br/><br/>
	Course mapping to clients can be edited <a href="https://docs.google.com/spreadsheets/d/1s01ZBNyTsGScQZFPZNhBDfYACcwXvvaSMiT9yAFHIyc/edit#gid=0" target="_blank">here</a><br/><br/>
	Hosts mapping can be updated <a href="https://docs.google.com/spreadsheets/d/1MVEBNzmvQRUz0787NMBcp69GM4w9zwaIHt_WNVsxizQ/edit#gid=0" target="_blank">here</a><br/><br/>
</section>

<h2>Update website course listings</h2>
<section id="update_course_listings">
        Update course listings data for the main ODI website.<br/><br/>
        <button id="update_listings_button">Update course listings</button>
</section>

<h2>Update course attendance</h2>
<section id="update_attendance">
	Update data for face-to-face course attendance from the source spreadsheets by clicking the relevant button.<br/><br/>
	<?php
		$sources = getSources("136yxCA_wWi8oLVsLlXD5kM_gvOCM-n05LSC4TSrU-gk");
		for($i=0;$i<count($sources);$i++) {
			echo '<button class="update_attendance_button" id="'.$sources[$i]["id"].'">'.$sources[$i]["key"].'</button>&nbsp;&nbsp;';
		}
	?>
	<br/><br/>
	If your expected source is not showing in the list above then you can add it <a href="https://docs.google.com/spreadsheets/d/136yxCA_wWi8oLVsLlXD5kM_gvOCM-n05LSC4TSrU-gk/edit#gid=0" target="_blank">here</a><br/>
</section>

<h2>Update badge data</h2>
<section id="update_badges">
	Update data for awarding of badges.<br/><br/>
	<?php
		$sources = getSources("1WfhNh1zRw9jsdGwJ3wx_19ybOh6ELBRGUM6KxKyPMVE");
		for($i=0;$i<count($sources);$i++) {
			echo '<button class="update_badges_button" id="'.$sources[$i]["id"].'">'.$sources[$i]["key"].'</button>&nbsp;&nbsp;';
		}
	?>
	<br/><br/>
	If your expected source is not showing in the list above then you can add it <a href="https://docs.google.com/spreadsheets/d/1WfhNh1zRw9jsdGwJ3wx_19ybOh6ELBRGUM6KxKyPMVE/edit#gid=0" target="_blank">here</a><br/>
</section>

<h2>Update third party access data</h2>
<section id="update_access">
	Update access profiles for clients access the LMS.<br/><br/>
	<?php
		$sources = getSources("15ekLmDvvl09lwnz-uhoHZS-cWOqbvZ6fZ1XFMCIL42I");
		for($i=0;$i<count($sources);$i++) {
			echo '<button class="update_access_button" id="'.$sources[$i]["id"].'">'.$sources[$i]["key"].'</button>&nbsp;&nbsp;';
		}
	?>
	<br/><br/>
	If your expected source is not showing in the list above then you can add it <a href="https://docs.google.com/spreadsheets/d/15ekLmDvvl09lwnz-uhoHZS-cWOqbvZ6fZ1XFMCIL42I/edit#gid=0" target="_blank">here</a><br/>
</section>

<script>

function archive_elearning() {
	$('#archive_elearning').html('Please wait');
	$.get('../api/v1/archive.php',function(data) {
		$('#archive_elearning').html(data);
	});
}

function update_courses() {
	$('#update_courses').html('Please wait');
	$.get('../api/v1/update_courses.php',function(data) {
		$('#update_courses').html(data);
	});
}

function update_course_listings() {
        $('#update_course_listings').html('Please wait');
        $.get('//odi-courses-data.herokuapp.com/update.php',function(data) {
                $('#update_course_listings').html(data);
        });
}

function update_course_attendance(id) {
	$('#update_attendance').html('Please wait');
	$.get('../api/v1/update_course_attendance.php?id='+id,function(data) {
		$('#update_attendance').html(data);
	});
}

function update_badges(id) {
	$('#update_badges').html('Please wait');
	$.get('../api/v1/update_badges.php?id='+id,function(data) {
		$('#update_badges').html(data);
	});
}

function update_access(id) {
	$('#update_access').html('Please wait');
	$.get('../api/v1/update_access.php?id='+id,function(data) {
		$('#update_access').html(data);
	});
}

function update_identifiers() {
	$('#update_identifiers').html('Please wait');
	$.get('../api/v1/update_course_identifiers.php',function(data) {
		$('#update_identifiers').html(data);
	});
}

function import_adapt() {
	url = $('#course_url').val();
	lang = $('#course_lang').val();
	if (url == "") {
		alert("please enter a course url!");
		return;
	} 
	$('#add_adapt_course').html('Please wait');
	$.get('../api/v1/import_adapt.php?url=' + encodeURI(url) + '&lang=' + lang, function(data) {
		$('#add_adapt_course').html(data);
	});
}

function addListeners() {
	$('#archive_elearning_button').on('click',function() {
		archive_elearning();
	});
	$('#update_courses_button').on('click',function() {
		update_courses();
	});
	$('#update_identifiers_button').on('click',function() {
		update_identifiers();
	});
	$('#update_listings_button').on('click',function() {
		update_course_listings();
	});
	$('.update_attendance_button').on('click',function() {
		id = this.id;
		update_course_attendance(id);
	});
	$('#import_adapt_course').on('click',function() {
		import_adapt();
	});
	$('.update_badges_button').on('click',function() {
		id = this.id;
		update_badges(id);
	});
	$('.update_access_button').on('click',function() {
		id = this.id;
		update_access(id);
	});
}

$(document).ready(function() {
	addListeners();
});

</script>

<?php
	include('_includes/footer.html');
?>

<?php

/* 
 * ADMIN SCREEN
 * 
 */
function getSources($doc_id) {
	global $exec_path;
	$content = exec('php ' . $exec_path . '/getFile.php ' . $doc_id,$lines);
  	$headers = str_getcsv($lines[0]);
	for($i=1;$i<count($lines);$i++) {
		$line = str_getcsv($lines[$i]);
		$record["key"] = $line[0];
		$record["id"] = $line[1];
		$ret[] = $record;
	}
	return($ret);
}

?>