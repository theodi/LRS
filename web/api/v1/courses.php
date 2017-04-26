<?php
header("Access-Control-Allow-Origin: *");
$path = "../../";
$access = "public";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$format = $_GET["format"];

$courses = getCoursesData();

if ($theme && $theme != "default") {
	$filter = getClientMapping($theme);
	$courses = filterCourses($courses,$filter);
}

$output = array();
foreach ($courses as $id => $course) {
  $course["ID"] = $id;
  if ($course["format"] == $format || !$format) {
   	$output[] = $course;
  }
}

$out["data"] = $output;


echo json_encode($out);

?>
