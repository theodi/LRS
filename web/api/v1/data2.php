<?php
// TRY THIS VERSION
   
$access = "public";
$path = "../../"
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

$theme = $_GET["theme"];

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
    	$output[] = rotate($id,$data);
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
    		$output[] = rotate($id,$data);
    }
}

function rotate($id,$data) {
	$line = [];
	$line["email"] = "false";
	if (strpos($id, "@") > ) {
		$line["email"] = "true";
	}
	$line["id"] = $data["id"];
	$line["theme"] = $data["theme"];
	$line["platform"] = $data["platform"];
	$line["lang"] = $data["lang"];
	$line["complete"] = "false";
	$line["passed"] = "not attempted";
	if ($data["progress"] > 99 || $data["_isComplete"] == true) {
		$line["complete"] = "true";
	}
	// Need to mark the assessment (do I need the assement module?)
	$line["passed"] = "UNKNOWN";
	$line["completion"] = $data["progress"];
	$line["session_time"] = $data["sessiontime"];

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


/*
 * Adapt 1
 */
/*
$summary = [];

$module_data_url_prefix = "http://theodi.github.io/ODI-eLearning/en/";
$module_data_suffix = "/course/en/components.json";

if (substr($module,0,4) == "ODI_") {
	$module = substr($module,4);
}

if (is_numeric($module)) {
	$module_data_url = $module_data_url_prefix . "module" . $module . $module_data_suffix;
} else {
	$module_data_url = $module_data_url_prefix . $module . $module_data_suffix;
}

$data = file_get_contents($module_data_url);

if ($data) {
	$assessmentData = getAssessmentData($data);
}

function getAssessmentData($data) {
	$data = json_decode($data,true);
	for($i=0;$i<count($data);$i++) {
		$current = $data[$i];
		$type = $current["_component"];
		if (strpos($type,"mcq") !== false) {
			$id = $current["_id"];
			$mcq[$id]["question"] = $current["title"];
			$mcq[$id] = getOptions($current,$mcq[$id]);
		}
	}
	return $mcq;
}

function getOptions($current,$mcq) {
	$items = $current["_items"];
	$selectedCount = 0;
	for ($i=0;$i<count($items);$i++) {
		$item = $items[$i];
		if ($item["_shouldBeSelected"] == 1) {
			$selectedCount++;
			$item["text"] = strtoupper($item["text"]);
		}
		$options[] = $item["text"];
	}
	$mcq["options"] = $options;
	if ($selectedCount > 1) {
		$mcq["multiSelection"] = true;
	}
	return $mcq;
}
//$summary[] = offline();
*/
//query();
//outputCSV($summary);

function offline() {
	$data = file_get_contents("test.json");
	$data = json_decode($data,true);
	return processRecord($data);
}

function query() {
   global $connection_url, $db_name, $collection, $summary;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();

	foreach ($cursor as $doc) {
		$ret = processRecord($doc);
		if ($ret) {
			$summary[] = $ret;
		}
	}
		
	$m->close();

	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
   }
}

function processRecord($doc) {
	global $module,$theme;
	$output = false;
	$search = "ODI_" . $module . "_";
	foreach ($doc as $key => $value) {
		if (substr($key,0,strlen($search)) == $search) {
			$outkey = str_replace($search,"",$key);
			$outkey = str_replace("．",".",$outkey);
			$output[$outkey] = $doc[$key];
		}
	}
	if ($theme && strtolower($theme) != strtolower($doc["theme"]) && $theme != "default") {
			return;
	}
	if ($output) {
		$output["id"] = $doc["_id"];
		$output["lang"] = $doc["lang"];
		$output["theme"] = $doc["theme"];
		$output["platform"] = $doc["platform"];
		if ($doc["email"]) {
			$output["email"] = "true";
		} else {
			$output["email"] = "false";
		}
	} else {
		return;
	}
	return processOutput($output);
}

function processOutput($output) {
	global $assessmentData;
	if ($output["cmi.suspend_data"] == "undefined") {
		return false;
	}
	$line = [];
	$line["id"] = $output["id"];
	$line["email"] = $output["email"];
	$line["theme"] = $output["theme"];
	$line["platform"] = $output["platform"];
	$line["lang"] = $output["lang"];

	$progress = $output["cmi.suspend_data"];
	$data = json_decode($progress,"true");
	$line["complete"] = "false";
	$line["passed"] = "not attempted";
	if ($data["spoor"]["_isCourseComplete"] == 1) {
		$line["complete"] = "true";
	}
	if ($data["spoor"]["_isAssessmentPassed"] == 1) {
		$line["passed"] = "true";
	}
	$completion = $data["spoor"]["completion"];
	$total = strlen($completion);
	$done = substr_count($completion,"1");
	$line["completion"] = $done / $total;
	$time = str_replace("．",".",$output["cmi.core.session_time"]);
	$time = substr($time,0,strpos($time,"."));
	$line["session_time"] = $time;

	$assess_data = $output["cmi.answers"];
	$data = json_decode($assess_data,"true");
	foreach($assessmentData as $key => $values) {
		$question = $values["question"];
		$selectedItems = $data[$key]["selectedItems"];
		if ($values["multiSelection"]) {
			$options = $values["options"];
			for($o=0;$o<count($options);$o++) {
				$line[$key . "_".$o.": " . $question] = "";
				$selected = false;
				for ($i=0;$i<count($selectedItems);$i++) {
					$item = $selectedItems[$i];
					$userAnswer = getUserAnswer($item,$options[$o]);
					if ($line["passed"] == "not attempted" && $userAnswer != "") {
						$line["passed"] = "false";
					}
					if (!$selected && $userAnswer != null) {
						$selected = $userAnswer;
						$line[$key . "_".$o.": " . $question] = $userAnswer;
					}
				}
			}
		} else {
			$item = $selectedItems[0];
			$userAnswer =  getUserAnswer($item,null);
			if ($line["passed"] == "not attempted" && $userAnswer != "") {
				$line["passed"] = "false";
			}
			$line[$key . ": " . $question] = $userAnswer;
		}
	}
	return $line;
}

function getUserAnswer($item,$option) {
	$userAnswer = $item["text"];
	$userAnswer = str_replace("．",".",$userAnswer);
//	$userAnswer = substr($userAnswer,0,-3);
	$userCorrect = $item["correct"];
	if ($userCorrect == 1) {
		$userAnswer = strToUpper($userAnswer);
	}
	if ($option==null || $option == $userAnswer) {
		return $userAnswer;
	}
	return "";
}

function outputCSV($summary) {
	
	$handle = fopen("php://output","w");
	$first = $summary[0];

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
