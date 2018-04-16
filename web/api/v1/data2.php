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

error_reporting(E_ALL ^ E_NOTICE);

$force = false;
if ($_GET["theme"]) {
   	$theme = $_GET["theme"];
}
$module = $_GET["module"];
if (!$module) {
	exit(0);
}
if ($_GET["force"] == "true") {
	$force = true;
}

$single_course[] = $module;

$componentItems = getComponents($module);

$courses = getCoursesData();
if ($theme && $theme != "default") {
  $filter = getClientMapping($theme);
  $courses = filterCourses($courses,$filter);
}


$date = date("Y-m-d");
$statsid = "Dashboard_" . $module . "_" . $theme;

if (!$force) {
	$stats = getCachedStats($statsid,$date);
	if ($stats) {
		$lastModified = ($stats["lastModified"]);
		unset($stats["id"]);
		unset($stats["date"]);
		unset($stats["lastModified"]);
		outputCSV($stats, $lastModified);
		exit();
	}
}

$tracking = getCourseIdentifiers();

// IMPORTANT, THIS ALGORITHM HAS TO WORK FROM THE RAW DATA TO REMAIN FAST! 

// TAKE 2

$profile = getLMSProfile($theme);
$client = $profile["client"];
/*
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
    if ($profile != "") {
  		$users = filterUsers($users,$filter,$client,$theme,$courses);
	} elseif ($theme != "default") {
  		$users = filterUsers($users,$filter,"",$theme,$courses);
	}
	if ($single_course) {
  		$users = filterUsersNotTheme($users,$single_course);
	}
	if ($theme != "default") {
  		$users = removeNullProfilesBadges($users);
	} else {
  		$users = removeNullProfiles($users);  
	}
    foreach ($users as $id => $data) {
		$data = addUserAnswer($data);
    	if ($id == $debugid && $debug == true) {
			print_r($data);
		}
		$output[] = rotate($id,$data);
    }
}
if ($output) {
	$adapt1_set = true;
}
// ADAPT 2
*/
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
    	$adapt2_set = true;
    	$output[] = rotate2($id,$data);
    	if ($id == $debugid && $debug == true) {
			print_r($output);
		}
    }
}

//$complicated = false;
//if ($adapt1_set && $adapt2_set) {
//	$output = rotate3($output);
//}
$output["id"] = $statsid;
$output["date"] = $date;
$output["lastModified"] = gmdate('D, d M Y H:i:s');
storeDataWithID($statsid,$output,"statisticsCache");
outputCSV($output,gmdate('D, d M Y H:i:s'));

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
	
	foreach ($data["answers"] as $aid => $adata) {
		$line[$aid."_isCorrect"] = $adata["correct"];
		$i=0;
		foreach($adata["userAnswer"] as $value) {
			$line[$aid."_".$i] = $value;
			$i++;
		}
		//$line[$aid] = json_encode($data["userAnswer"]);
	}

	return $line;
}

// Adapt 2

function rotate2($id,$indata) {
	global $debug,$debugid;
	global $connection_url, $db_name;
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

	$collection = "adapt2Components";
	$adapt2Components = getDataFromCollection("adapt2Components");
	foreach ($adapt2Components as $doc) {
		if ($doc["_items"] && $doc["_component"] == "mcq") {
			$comps[$doc["_id"]] = $doc;	
		}
	}
	foreach ($data["answers"] as $aid => $data) {
		$items = $comps[$aid]["_items"];
		if (is_array($data["_userAnswer"])) {
			if ($items) {
				if ($data["_isCorrect"]) {
					$line[$aid."_isCorrect"] = $data["_isCorrect"];
				} elseif (count($data["_userAnswer"]) > 0) {
					$line[$aid."_isCorrect"] = "0";
				} else {
					$line[$aid."_isCorrect"] = "";
				}
			}
			for ($i=0;$i<count($items);$i++) {
				$line[$aid."_".$i] = $data["_userAnswer"][$i] || "0";
			}
		}
	}
	return $line;
}

function rotate3($output) {
	for($i=0;$i<count($output);$i++) {
		$current = $output[$i];
		foreach($current as $key => $value) {
			$headings[$key] = "true";
		}
	}
	$ret = [];
	for($i=0;$i<count($output);$i++) {
		$new = [];
		$current = $output[$i];
		foreach($headings as $key => $bool) {
			$new[$key] = $current[$key];
		}
		$ret[] = $new;
	}
	return($ret);
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


function outputCSV($summary,$lastModified) {
	$longest = 0;
	for($i=0;$i<count($summary);$i++) {
		if (count($summary[$i]) > $longest) {
			$longest = count($summary[$i]);
			$first = $summary[$i];
		}
	}
	$handle = fopen("php://output","w");

	header('Content-Type: text/csv');
	header('Last-Modified: '.$lastModified.' GMT', true, 200);
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

$componentItems = null;

function getComponents($module) {
	global $connection_url,$db_name;
	$collection = "adaptComponents";
	$query = array("_moduleId" => $module);
	$res = executeQuery($connection_url,$db_name,$collection,$query);
	foreach ($res as $doc) {
		$ret[$doc["_componentId"]][] = $doc;
	}
	return $ret;
}

function addUserAnswer($data) {
  	$data["eLearning"]["complete"] = addUserAnswers($data["eLearning"]["complete"]);
  	$data["eLearning"]["in_progress"] = addUserAnswers($data["eLearning"]["in_progress"]);
  	$data["eLearning"]["active"] = addUserAnswers($data["eLearning"]["active"]);
  	return $data;
}

function addUserAnswers($courses) {
	global $componentItems,$debug;
	for($i=0;$i<count($courses);$i++) {
		$answers = $courses[$i]["answers"];
		foreach($answers as $id => $data) {
	      $componentId = $id;
          $item_sources = $componentItems[$componentId];
          $min = 10000;
          $pointer = "";
          for($ci=0;$ci<count($item_sources);$ci++) {
          	$items = $item_sources[$ci]["_items"];
          	$data["items"][$ci] = $items;
	        for($s=0;$s<count($data["selectedItems"]);$s++) {
	        	$selected = $data["selectedItems"][$s]["text"];
	            for($n=0;$n<count($items);$n++) {
	               $distance = levenshtein($selected, $items[$n]["text"]);
	          	   //echo "Comparing " . $selected  . " to " . $items[$n]["text"] . " = " . $distance . "\n";
	               if ($distance < $min) {
	                $min = $distance;
	                //echo "setting pointer " . $s . " to " .$n . "\n";
	                $pointer[$s] = $n;
	               }
	            }
	        }
	      }
          unset($data["userAnswer"]);
          $new = [];
          for($n=0;$n<count($items);$n++) {
            if (in_array($n, $pointer)) {
              $new[$n] = 1;
            } else {
              $new[$n] = null;
            }
          }
          $data["userAnswer"] = $new;
          if ($item_sources[0]["_adapt2id"]) {
          	unset($courses[$i]["answers"][$id]);
          	$id = $item_sources[0]["_adapt2id"];
          }
          $courses[$i]["answers"][$id] = $data;
        }
    }
    return $courses;
}

function getCachedStats($id,$date) {
   $collection = "statisticsCache";
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('id' => $id, 'date' => $date);
	$res = $col->find($query);
	$ret = "";
	foreach ($res as $doc) {
		return $doc;
	}
	$m->close();
	return false;
   } catch ( MongoConnectionException $e ) {
//	return false;
	echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	echo "<br/><br/>\n\n";
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
	echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	echo "<br/><br/>\n\n";
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

?>
