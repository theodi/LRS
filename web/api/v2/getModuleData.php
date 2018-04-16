<?php
error_reporting(E_ALL ^ E_WARNING);
// TRY THIS VERSION
$debug = false;
//$debugid = "q@q.com";
$debugid = "davetaz+lga@gmail.com";

$access = "public";
$path = "../../";

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once('library/functions.php');
include_once('header.php');

header("Access-Control-Allow-Origin: *");

if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}

$moduleID = $_GET["module"];
if (!$moduleID) {
	exit(0);
}

$csvData = [];

$parentID = getValueFromDoc($moduleID,"modules","_parentId");
$courseID = getValueFromDoc($parentID,"courses","_id");

$csvData = getUsersWithCourseModuleData($csvData,$courseID,$moduleID);

outputCSV($csvData);

function processUserLocal($user,$courseID,$moduleID,$csvData) {

	$output["email"] = "false";
	$output["platform"] = "web";
	$output["complete"] = "false";
	$output["completion"] = 0;

	$output["id"] = $user["_id"];

	$courseData = $user[$courseID];

	if ($courseData["user"]["email"]) { 
		$output["email"] = "true";
	}

	$moduleData = $user[$courseID]["progress"][$moduleID];
	$output["theme"] = $moduleData["theme"];
	$output["platform"] = $moduleData["platform"] ?: "web";
	$output["lang"] = $moduleData["lang"];

	if ($moduleData["_isComplete"]) {
		$output["complete"] = "true";
	}
	if ($moduleData["progress"]) {
		$output["completion"] = $moduleData["progress"]/100;
	}
	
	$output["session_time"] = gmdate("H:i:s", $moduleData["sessionTime"]);

	if ($moduleData["progress"] > 99 || $moduleData["_isComplete"] == true || $moduleData["answers"]["_assessmentState"] == "Passed" || $moduleData["answers"]["_assessmentState"] == "Failed") {
		$output["complete"] = "true";
	}

	$output["passed"] = $moduleData["answers"]["_assessmentState"] ?: "";
	unset($moduleData["answers"]["_assessmentState"]);

	foreach ($moduleData["answers"] as $aid => $adata) {

		$output[$aid."_isCorrect"] = $adata["_isCorrect"];
		$i=0;
		if (is_array($adata["_userAnswer"])) {
			foreach($adata["_userAnswer"] as $value) {
				$output[$aid."_".$i] = $value;
				$i++;
			}
		} else {
			$output[$aid."_0"] = $adata["_userAnswer"];
		}
	}
	$csvData[] = $output;
	return $csvData;
}

function outputCSV($summary) {
	//function outputCSV($summary, $lastModified) {
	$longest = 0;
	for($i=0;$i<count($summary);$i++) {
		if (count($summary[$i]) > $longest) {
			$longest = count($summary[$i]);
			$first = $summary[$i];
		}
	}
	$handle = fopen("php://output","w");

	header('Content-Type: text/csv');
	//header('Last-Modified: '.$lastModified.' GMT', true, 200);
	header('Content-Disposition: attachment; filename="data.csv"');

	foreach ($first as $key => $value) {
		$keys[] = $key;
		$values[] = $value;
	}
	fputcsv($handle,$keys);
	fputcsv($handle,$values);
	for($i=1;$i<count($summary);$i++) {
		$values = "";
		$line = $summary[$i];
		for($k=0;$k<count($keys);$k++) {
			$values[] = $line[$keys[$k]];
		}
		//foreach ($line as $key => $value) {
		//	$values[] = $value;
		//}
		fputcsv($handle,$values);
	}

	fclose($handle);
	
}

function getUsersWithCourseModuleData($csvData,$courseID,$moduleID) {
	global $connection_url, $db_name;
	$collection = "adapt2";
  	try {
		// create the mongo connection object
		$m = new MongoClient($connection_url);

		// use the database we connected to
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$courseID = str_replace(".", "_", $courseID);
		$query = array($courseID.".progress.".$moduleID => array('$exists' => true));
		$res = $col->find($query);
		$ret = "";
		foreach ($res as $doc) {
			$csvData = processUserLocal($doc,$courseID,$moduleID,$csvData);
		}
		$m->close();
		return $csvData;
   	} catch ( MongoConnectionException $e ) {
   	}
}

function getValueFromDoc($id,$collection,$value) {
	global $connection_url, $db_name;
  	try {
		// create the mongo connection object
		$m = new MongoClient($connection_url);

		// use the database we connected to
		$col = $m->selectDB($db_name)->selectCollection($collection);
		
		$query = array('_id' => $id);
		$res = $col->find($query);
		$ret = "";
		foreach ($res as $doc) {
			return $doc[$value];
		}
		$m->close();
		return false;
   	} catch ( MongoConnectionException $e ) {
   	}
}