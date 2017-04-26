<?php
$access = "admin";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

getInstances();
getCourses();

function getInstances() {
	global $instances_collection;
	$instances_url = "http://contentapi.theodi.org/with_tag.json?type=course_instances&summary=true&sort=date";
	$data = file_get_contents($instances_url);
	$data = str_replace("+00:00","Z",$data);
	$data = str_replace("+01:00","Z",$data);
	$json = json_decode($data,true);
	$results = $json["results"];
	for ($i=0;$i<count($results);$i++) {
		echo storeDataWithID($results[$i]["id"],$results[$i],$instances_collection);
	}
}

function getCourses() {
	global $courses_collection; 
	$courses_url = "http://contentapi.theodi.org/with_tag.json?type=course&summary=true";
	$data = file_get_contents($courses_url);
	$json = json_decode($data,true);
	$results = $json["results"];
	for ($i=0;$i<count($results);$i++) {
                store($results[$i],$courses_collection);
        }
}

?>
