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
$lang = "en";
if ($_GET["lang"]) {
	$lang = $_GET["lang"];
}

$data = getCourseData($url);

function getCourseData($url) {
	global $lang;

	$courseUrl = $url . "/course/" . $lang . "/course.json";
	$data = file_get_contents($courseUrl);
	$data = json_decode($data,true);
	$courseTitle = @$data["title"];
	$courseUrl = $url . "/course/config.json";
	$data = file_get_contents($courseUrl);
	$data = json_decode($data,true);
	$courseTrackingID = @$data["_trackingHub"]["_courseID"];
	$courseID = $data["_id"];
	$data["title"] = $courseTitle;
	echo storeDataWithID($data["_id"],$data,"courses");

	$dataUrl = $url . "/course/".$lang."/contentObjects.json";
	$data2 = file_get_contents($dataUrl);
	setAdapt2Data($data2,$url,$courseID);
	importAdapt2Components($url);
}

function importAdapt2Course($data,$url) {
	$data = json_decode($data,true);
	$data["_id"] = $url;

}

function setAdapt2Data($data,$url,$courseID) {
	$data = json_decode($data,true);
	for($i=0;$i<count($data);$i++) {
		$module = $data[$i];
		importAdapt2Module($module,$url,$courseID);
	}
}

function importAdapt2Module($module,$url,$courseID) {
	if ($module["_classes"] == "hidden") {
		return;
	}
	unset($module["_pageLevelProgress"]);
	unset($module["linkText"]);
	unset($module["_type"]);
	unset($module["_isLockedBy"]);
	$module["_parentId"] = $courseID;
	$module["topMenu"] = $url;
	if (substr($url,-1) == "/") {
		$module["url"] = $url . "#/id/" . $module["_id"];
	} else {
		$module["url"] = $url . "/#/id/" . $module["_id"];
	}
	echo storeDataWithID($module["_id"],$module,"modules");
}

function importAdapt2Components($url) {
	global $lang;
	$articles = json_decode(file_get_contents($url . "/course/".$lang."/articles.json"),true);
	for ($i=0;$i<count($articles);$i++) {
		$article = $articles[$i];
		$arts[$article["_id"]] = $article["_parentId"];
	}
	$blocks = json_decode(file_get_contents($url . "/course/".$lang."/blocks.json"),true);
	for ($i=0;$i<count($blocks);$i++) {
		$block = $blocks[$i];
		$blks[$block["_id"]] = $arts[$block["_parentId"]];
	}
	$components = json_decode(file_get_contents($url . "/course/".$lang."/components.json"),true);;
	for ($i=0;$i<count($components);$i++) {
		$component = $components[$i];
		$component["_moduleId"] = $blks[$component["_parentId"]];
		$component["_articleId"] = $blks[$component["_parentId"]];
		storeDataWithID($component["_id"],$component,"adapt2Components");
	}

}

?>
