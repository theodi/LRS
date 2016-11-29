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
	if (strpos($data,"title") !== false && strpos($data,"_globals") === false) {
		$data = getAdapt1Data($data);
		$data["url"] = $url;
	} elseif (strpos($data,"_globals") > 0) {
		$dataUrl = $url . "/course/en/contentObjects.json";
		$data = file_get_contents($dataUrl);
		$data = setAdapt2Data($data,$url);
		$data = importAdapt2Components($url);
		exit(1);
	}
	return $data;
}

function getAdapt1Data($data) {
	$data = json_decode($data,true);
	unset($data["_resources"]);
	unset($data["_buttons"]);
	return $data;
}

function setAdapt2Data($data,$url) {
	$data = json_decode($data,true);
	for($i=0;$i<count($data);$i++) {
		$module = $data[$i];
		importAdapt2Module($module,$url);
	}
}

function importAdapt2Module($module,$url) {
	global $courses_collection;
	unset($module["_pageLevelProgress"]);
	unset($module["linkText"]);
	unset($module["_type"]);
	unset($module["_parentID"]);
	unset($module["_isLockedBy"]);
	if (substr($url,-1) == "/") {
		$module["url"] = $url . "#/id/" . $module["_id"];
	} else {
		$module["url"] = $url . "/#/id/" . $module["_id"];
	}
	echo storeDataWithID($module["_id"],$module,$courses_collection);
}

function getModuleId($url) {
	$dataUrl = $url . "/course/en/config.json";
	$data = file_get_contents($dataUrl);
	$data = json_decode($data,true);
	return $data["_moduleId"];
}

function importAdapt2Components($url) {
	$articles = json_decode(file_get_contents($url . "/course/en/articles.json"),true);
	for ($i=0;$i<count($articles);$i++) {
		$article = $articles[$i];
		$arts[$article["_id"]] = $article["_parentId"];
	}
	$blocks = json_decode(file_get_contents($url . "/course/en/blocks.json"),true);
	for ($i=0;$i<count($blocks);$i++) {
		$block = $blocks[$i];
		$blks[$block["_id"]] = $arts[$block["_parentId"]];
	}
	$components = json_decode(file_get_contents($url . "/course/en/components.json"),true);;
	for ($i=0;$i<count($components);$i++) {
		$component = $components[$i];
		$component["_moduleId"] = $blks[$component["_parentId"]];
		$component["_articleId"] = $blks[$component["_parentId"]];
		storeDataWithID($component["_id"],$component,"adapt2Components");
	}

}

?>
