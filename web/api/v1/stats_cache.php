<?php
$access = "public";
//$location = "/api/view_data.php";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');
/*
$data = getDataFromCollection($collection);
$courses = getCoursesData();

if ($theme && $theme != "default") {
   $filter = getClientMapping($theme);
   $courses = filterCourses($courses,$filter);
}
$tracking = getCourseIdentifiers();

$all = "";
$complete = "";
$collection = "elearning";
$count = getNumberOfRecords($collection);
    $data = getDataFromCollection($collection);
    $position = 0;
    foreach ($data as $user) {
		$complete_modules = getCompleteModuleCount($user,$courses);
		if ($complete_modules > 0) {
			$people_trained++;
			$complete[$complete_modules]++;
			$module_completions+=$complete_modules;
		} else {
	        if (isUserActive($user,$courses)) {
            	$active++;
        	}
    	}
    	$position++;
    }

$theme = "default";
$users = getUsers("courseAttendance","");
$users = removeNullProfiles($users);
$profile = getLMSProfile($theme);
if ($profile != "") {
	$users = filterClient($users,$profile["client"]);
}
$all["trained"]["attendance"] = count($users);
$all["trained"]["eLearning"] = $people_trained;
$all["modules"]["eLearning"] = $module_completions;
$all["active"]["eLearning"] = $active;
$all["modules"]["completeCount"] = $complete;
$all["id"] = "trained-" . $theme . "-" . date("Y-m-d");
$all["date"] = date("Y-m-d");
$all["type"] = "trained";
$all["theme"] = $theme;
store($all,"statisticsCache");
*/
$allStats = getCachedStats($theme,"trained","statisticsCache");
$out = "";
for($i=0;$i<count($allStats);$i++) {
	$out[$allStats[$i]["date"]] = $allStats[$i];
}
//$out[$all["date"]] = $all;
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

function filterClient($users,$filter) {
  foreach($users as $email => $data) {
    $data["courses"]["complete"] = filterCourseClient($data["courses"]["complete"],$filter);
    $users[$email] = $data;
  }
  return removeNullProfiles($users);
}

function isUserActive($user,$courses) {
    foreach($user as $key => $data) {
        $key = str_replace("．",".",$key);
        if (strpos($key,"_cmi.suspend_data") !== false) {
            $time = $user[$course . "_cmi．core．session_time"];
            $course = substr($key,0,strpos($key,"_cmi"));
            $progress = $data;
            if ($courses[$course] && $courses[$course]["format"] == "eLearning") {
                $course_id = $courses[$course]["id"];
                $course_id = substr($course_id,4);
                $courses[$course]["progress"] = getProgress($courses[$course],$progress);
                if ($courses[$course]["progress"] > 49 && getTime($time) > 300) {
                    return true;
                }
            }
        }
    }
    return false;
}

function getCompleteModuleCount($user,$courses) {
	$complete = 0;
	foreach($user as $key => $data) {
                $key = str_replace("．",".",$key);
                if (strpos($key,"_cmi.suspend_data") !== false) {
                        $course = substr($key,0,strpos($key,"_cmi"));
                        $progress = $data;
                        if ($courses[$course] && $courses[$course]["format"] == "eLearning") {
				            $course_id = $courses[$course]["id"];
				            $course_id = substr($course_id,4);
	           //			if (is_numeric($course_id) && $course_id < 14) {
                             	$courses[$course]["progress"] = getProgress($courses[$course],$progress);
                               	if ($courses[$course]["progress"] > 99) {
                                       	$complete++;
                               	}
	           //			}
                        }
                }
        }
	return $complete;
}

function getCachedStats($theme,$type,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('theme' => new MongoRegex('/^' .  $theme . '$/i'), 'type' => $type);
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
