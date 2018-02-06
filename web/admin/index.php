<?php
	$access = "admin";
	$requireAdmin = true;
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
  	include_once('library/functions.php');
?>
<script src="../js/jquery-2.1.4.min.js"></script>

<h2>Add adapt course/module</h2>
<section id="add_adapt_course">
	Please enter the url of the course homepage in the box below, it must be publically accessible. (e.g. http://accelerate.theodi.org/en/module1 or http://training.theodi.org/inaday).<br/><br/>
	<input type="text" id="course_url"></input></br>
	<input type="text" id="course_lang" value="en" style="width:20px;"></input></br/>
	<button id="import_adapt_course">Import adapt course</button>
</section>

<h2>Update third party access data</h2>
<section id="update_access">

</section>

<script>

function import_adapt() {
	url = $('#course_url').val();
	lang = $('#course_lang').val();
	rewrite = $('#course_rewrite').val();
	client = $('#course_client').val();
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
	$('#import_adapt_course').on('click',function() {
		import_adapt();
	});
}

$(document).ready(function() {
	addListeners();
});

</script>

<?php
	include('_includes/footer.html');
?>