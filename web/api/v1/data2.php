<?php
// TRY THIS VERSION
$debug = false;
$debugid = "q@q.com";
//$debugid = "DFECD1A5-8180-4390-9A35-340063AA0971";
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

error_reporting(E_ALL ^ E_NOTICE);

if ($_GET["theme"]) {
   	$theme = $_GET["theme"];
}

$module = $_GET["module"];
$single_course[] = $module;
if (!$module) {
	exit(0);
}

$courses = getCoursesData();
if ($theme && $theme != "default") {
  $filter = getClientMapping($theme);
  $courses = filterCourses($courses,$filter);
}
$tracking = getCourseIdentifiers();

// IMPORTANT, THIS ALGORITHM HAS TO WORK FROM THE RAW DATA TO REMAIN FAST! 

// TAKE 2

$profile = getLMSProfile($theme);
$client = $profile["client"];

$collection = "elearning";
$cursor = getDataFromCollection($collection); 
foreach ($cursor as $doc) {
	$users = "";
    if ($doc["email"]) {
        $email = $doc["email"];
      } elseif ($doc["Email"]) {
        $email = $doc["Email"];
      } else {
        $email = $doc["_id"];
      }
    $email = str_replace("．",".",$email);
    $users = processUser($collection,$users,$doc,$email);
    foreach ($users as $id => $data) {
    	$userid = $id;
    }
    if ($userid == $debugid && $debug == true) {
		print_r($users);
	}
    if ($profile != "") {
  		$users = filterUsers($users,$filter,$client,$theme,$courses);
	} elseif ($theme != "default") {
  		$users = filterUsers($users,$filter,"",$theme,$courses);
	}
	if ($userid == $debugid && $debug == true) {
		print_r($users);
	}
	if ($single_course) {
  		$users = filterUsersNotTheme($users,$single_course);
	}
	if ($userid == $debugid && $debug == true) {
		print_r($users);
	}
	if ($theme != "default") {
  		$users = removeNullProfilesBadges($users);
	} else {
  		$users = removeNullProfiles($users);  
	}
	if ($userid == $debugid && $debug == true) {
		print_r($users);
	}

    foreach ($users as $id => $data) {
    	$output[] = rotate($id,$data);
    	if ($userid == $debugid && $debug == true) {
			print_r($output);
		}
    }
}
// ADAPT 2

$collection = "adapt2";
$cursor = getDataFromCollection($collection); 
foreach ($cursor as $doc) {
	$users = "";
    if ($doc["user"]["email"]) {
    	$email = $doc["user"]["email"];
    } else {
    	$email = $doc["_id"];
    }
    $users = processAdapt2User($users,$doc,$email);

    if ($profile != "") {
  		$users = filterUsers($users,$filter,$client,$theme,$courses);
	} elseif ($theme != "default") {
  		$users = filterUsers($users,$filter,"",$theme,$courses);
	}
	if ($single_course){
  		$users = filterUsersNotTheme($users,$single_course);
	}

	if ($theme != "default") {
  		$users = removeNullProfilesBadges($users);
	} else {
  		$users = removeNullProfiles($users);  
	}

    foreach ($users as $id => $data) {
    	$output[] = rotate2($id,$data);
    	if ($userid == $debugid && $debug == true) {
			print_r($output);
		}
    }
}

outputCSV($output);

// Adapt

function rotate($id,$indata) {
	global $debug,$debugid;
	$line = [];

	$line["id"] = $id;
	$line["email"] = "false";
	if (strpos($id, "@")) {
		$line["id"] = $indata["id"];
		$line["email"] = "true";
	}
	$data = $indata["eLearning"]["complete"][0];
	if (!is_array($data)) {
		$data = $indata["eLearning"]["in_progress"][0];
	}
	if (!is_array($data)) {
		$data = $indata["eLearning"]["active"][0];
	}
	$line["theme"] = $data["theme"];
	$line["platform"] = $data["platform"];
	$line["lang"] = $data["lang"];
	$line["complete"] = "false";
	if ($data["progress"] > 99 || $data["_isComplete"] == true) {
		$line["complete"] = "true";
	}
	// Need to mark the assessment (do I need the assement module?)
	$line["passed"] = $data["assessmentPassed"];

	$line["completion"] = $data["progress"] / 100;
	$line["session_time"] = gmdate("H:i:s", $data["time"]);
	foreach ($data["answers"] as $aid => $data) {
		$line[$aid] = json_encode($data["userAnswer"]);
	}

	return $line;
}

// Adapt 2

function rotate2($id,$indata) {
	global $debug,$debugid;
	$line = [];

	$line["id"] = $id;
	$line["email"] = "false";
	if (strpos($id, "@")) {
		$line["id"] = $indata["id"];
		$line["email"] = "true";
	}
	$data = $indata["eLearning"]["complete"][0];
	if (!is_array($data)) {
		$data = $indata["eLearning"]["in_progress"][0];
	}
	if (!is_array($data)) {
		$data = $indata["eLearning"]["active"][0];
	}
	$line["theme"] = $data["theme"];
	if (!$data["platform"]) {
		$data["platform"] = "web";
	}
	$line["platform"] = $data["platform"];
	$line["lang"] = $data["lang"];
	$line["complete"] = "false";
	if ($data["progress"] > 99 || $data["_isComplete"] == true || $data["answers"]["_assessmentState"] == "Passed" || $data["answers"]["_assessmentState"] == "Failed") {
		$line["complete"] = "true";
	}
	// Need to mark the assessment (do I need the assement module?)
	$line["passed"] = $data["answers"]["_assessmentState"];

	$line["completion"] = $data["progress"] / 100;
	$line["session_time"] = gmdate("H:i:s", $data["sessionTime"]);
	foreach ($data["answers"] as $aid => $data) {
		if (is_array($data["_userAnswer"])) {
			$line[$aid] = json_encode($data["_userAnswer"]);
		}
	}

	return $line;
}

// FIXME DUPLICATED IN GENERATE_USER_SUMMARY
/* Single course filter functions */
/*
 * filterCourseUserNotTheme($userdata,$filter,$theme,$email,$courses)
 * For single course filtering after the theme filtering has been done!
 *
 * Called by: api/v1/trained_stats.php
 *            api/v1/generate_user_summary.php
 */
function filterCourseUserNotTheme($userdata,$filter) {
  $ret = "";
  for($i=0;$i<count($userdata);$i++) {
    $out = $userdata[$i];
    $id = $userdata[$i]["id"];
    if (is_array($id)) {
      $id = $id["id"];
    }
    // Adapt 2
    if (in_array($id, $filter)) {
      $ret[] = $out;
    }
  }
  return $ret;
}

function filterUsersNotTheme($users,$filter) {
  foreach($users as $email => $data) {
    $data["eLearning"]["complete"] = filterCourseUserNotTheme($data["eLearning"]["complete"],$filter);
    $data["eLearning"]["in_progress"] = filterCourseUserNotTheme($data["eLearning"]["in_progress"],$filter);
    $data["eLearning"]["active"] = filterCourseUserNotTheme($data["eLearning"]["active"],$filter);
    $users[$email] = $data;
  }
  return $users;
}


function outputCSV($summary) {
	$longest = 0;
	for($i=0;$i<count($summary);$i++) {
		if (count($summary[$i]) > $longest) {
			$longest = count($summary[$i]);
			$first = $summary[$i];

		}
	}
	$handle = fopen("php://output","w");

	header('Content-Type: text/csv');
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
		foreach ($line as $key => $value) {
			$values[] = $value;
		}
		fputcsv($handle,$values);
	}

	fclose($handle);
	
}

?>
