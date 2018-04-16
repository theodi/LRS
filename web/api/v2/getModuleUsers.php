<?php

error_reporting(E_ALL ^ E_NOTICE);
$access = "viewer";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');
if (!$userData["isAdmin"]) {
  if (!$userData["isViewer"]) {
    header('Location: /401.php');
    exit();
  }
}

$moduleID = $_GET["module"];
if (!$moduleID) {
  exit(0);
}

$parentID = getValueFromDoc($moduleID,"modules","_parentId");
$courseID = getValueFromDoc($parentID,"courses","_id");

$courses = getCoursesData();

if ($_GET["course"]) {
  $single_course[] = $_GET["course"];
  $courses = filterCourses($courses,$single_course);
}
if ($theme && $theme != "default") {
  $filter = getClientMapping($theme);
  $courses = filterCourses($courses,$filter);
}
/*
if (count($courses) < 1 || $courses == null || $courses == "" && !$course[$courseID]) {
	header('Location: /401.php');
    exit();
}
*/
//$courseID = getValueFromDoc($parentID,"courses","_trackingHub")["_courseID"];
//$courseID = str_replace(".", "_", $courseID);

$query = array($courseID.".progress.".$moduleID => array('$exists' => true));
$res = executeQuery($connection_url,$db_name,"adapt2",$query);

$output = "";

$count = 0;
foreach ($res as $doc) {
  $output[$count]["user"] = $doc["user"];
  $moduleData = $doc[$courseID]["progress"][$moduleID];
  if ($moduleData["progress"] > 99 || $moduleData["_isComplete"] == true || $moduleData["answers"]["_assessmentState"] == "Passed" || $moduleData["answers"]["_assessmentState"] == "Failed") {
      $moduleData["_isComplete"] = true;
  }
  $output[$count]["progress"] = $moduleData;
  $count++;
}
$ret["data"] = $output;

echo json_encode($ret);

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

?>