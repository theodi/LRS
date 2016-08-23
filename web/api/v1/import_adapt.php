<?php

$access = "admin";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$url = $_GET["url"];
if (!$url || $url == "") {
  echo "GO AWAY";
  exit();
}

$data = getCourseData($url);
$data["_id"] = getModuleId($url);
$data["id"] = $data["_id"];
if ($data["title"] == "" || $data["id"] == "") {
	echo "No data to import! Wrong URL?";
	exit();
} else {
	echo storeDataWithID($data["id"],$data,$courses_collection);
}

function getCourseData($url) {
	$dataUrl = $url . "/course/en/course.json";
	$data = file_get_contents($dataUrl);
	$data = json_decode($data,true);
	unset($data["_resources"]);
	unset($data["_buttons"]);
	return $data;
}

function getModuleId($url) {
	$dataUrl = $url . "/course/en/config.json";
	$data = file_get_contents($dataUrl);
	$data = json_decode($data,true);
	return $data["_moduleId"];
}

?>
