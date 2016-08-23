<?php
header("Access-Control-Allow-Origin: *");
$path = "../../";
$access = "public";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$courses = getCoursesData();

if ($theme && $theme != "default") {
	$filter = getClientMapping($theme);
	$courses = filterCourses($courses,$filter);
}

$output = array();
foreach ($courses as $id => $course) {
  $course["ID"] = $id;
  $output[] = $course;
}

$out["data"] = $output;

echo json_encode($out);

?>
