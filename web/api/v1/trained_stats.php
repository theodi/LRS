<?php
// Some replicated code but works without passing lots of variables around. 

$access = "public";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

if ($_GET["theme"]) {
   	$theme = $_GET["theme"];
}
if ($theme == "") {
	$theme = "default";
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

	if ($theme != "default") {
  		$users = removeNullProfilesBadges($users);
	} else {
  		$users = removeNullProfiles($users);  
	}

    foreach ($users as $id => $data) {
    	$complete_modules = 0;
		$active_modules = 0;
		$complete_courses = 0;
		if (is_array($data["eLearning"]["complete"])) {
			$complete_modules = count($data["eLearning"]["complete"]);
		}
		if (is_array($data["eLearning"]["active"])) {
			$active_modules = count($data["eLearning"]["active"]);
		}
		if ($complete_modules > 0) {
			$people_trained++;
			$complete[$complete_modules]++;
			$module_completions+=$complete_modules;
		} elseif ($active_modules > 0) {
			$active++;
		}
		if ($complete_courses > 0) {
			$attended_training++;
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

	if ($theme != "default") {
  		$users = removeNullProfilesBadges($users);
	} else {
  		$users = removeNullProfiles($users);  
	}

    foreach ($users as $id => $data) {
    	$complete_modules = 0;
		$active_modules = 0;
		$complete_courses = 0;
		if (is_array($data["eLearning"]["complete"])) {
			$complete_modules = count($data["eLearning"]["complete"]);
		}
		if (is_array($data["eLearning"]["active"])) {
			$active_modules = count($data["eLearning"]["active"]);
		}
		if ($complete_modules > 0) {
			$people_trained++;
			$complete[$complete_modules]++;
			$module_completions+=$complete_modules;
		} elseif ($active_modules > 0) {
			$active++;
		}
		if ($complete_courses > 0) {
			$attended_training++;
		}
    }
}

$users = "";
$users = getUsers("courseAttendance",$users);

$attended_training = 0;

foreach($users as $email => $data) {
	$complete_courses = 0;
	if (is_array($data["courses"]["complete"])) {
		$complete_courses = count($data["courses"]["complete"]);
	}
	if ($complete_courses > 0) {
		$attended_training++;
	}
}

$all["trained"]["attendance"] = $attended_training;
$all["trained"]["eLearning"] = $people_trained;
$all["modules"]["eLearning"] = $module_completions;
$all["active"]["eLearning"] = $active;
$all["modules"]["completeCount"] = $complete;
$all["id"] = "trained-" . $theme . "-" . date("Y-m-d");
$all["date"] = date("Y-m-d");
$all["type"] = "trained";
$all["theme"] = $theme;

$allStats = getCachedStats($theme,"trained","statisticsCache");
store($all,"statisticsCache");
$out = "";
for($i=0;$i<count($allStats);$i++) {
	$out[$allStats[$i]["date"]] = $allStats[$i];
}
$out[$all["date"]] = $all;
ksort($out);

$handle = fopen('php://output', 'w');
$headings = array("Date","Attended_Training","eLearning_Complete","eLearning_Active","eLearning_Modules_Complete");	
fputcsv($handle,$headings);
foreach ($out as $date => $values) {
	$line = "";
	$line[] = $date;
	$line[] = $values["trained"]["attendance"];
	$line[] = $values["trained"]["eLearning"];
	$line[] = $values["active"]["eLearning"];
	$line[] = $values["modules"]["eLearning"];
	$foo[0] = $line;
	fputcsv($handle,$foo[0]);
}
fclose($handle);

function getCachedStats($theme,$type,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('theme' => $theme, 'type' => $type);
	$res = $col->find($query);
	$ret = "";
	foreach ($res as $doc) {
		$ret[] = $doc;
	}
	return $ret;
	$m->close();
	return $res;
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

function store($data,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$id = $data["id"];
	$query = array('id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->update($query,$newdata);
	} else {
		$col->save($data);
	}

	$m->close();
	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
	echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

?>
