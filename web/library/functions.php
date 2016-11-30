<?php

/*
 *	FUNCTIONS ON GOOGLE DOCS DATA
 *
 */

/*
 * getTheme($host)
 * To style and filter. Get the theme/client from the hostname in the URL
 * 
 * Called by: api/v1/header.php
 * 			  _includes/header.php
 */
function getTheme($host) {
	$host = str_replace(".","_",$host);
    $courseIdentifiers = getDataFromCollection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["hosts"];
   		foreach ($doc as $key => $value) {
   			if ($key == $host) {
   				return $value[0];
   			}
   		}
   	}
   	return false;
}

/*
 * getExternalAccess($email,$theme)
 * See if user logging in has any non-viewer rights over the LRS endpoint. 
 * This is used to populate a user profile in the headers. 
 *
 * Called by: _includes/header.php
 * 			  
 */
function getExternalAccess($email,$theme) {
	global $connection_url, $db_name;
	$collection = "externalAccess";
	$query = array('email' => $email, 'theme' => $theme);
	$res = executeQuery($connection_url,$db_name,$collection,$query);
	foreach ($res as $doc) {
		return $doc;
	}
}

/*
 * GENERAL MONGO FUNCTIONS
 *
 */

function executeQuery($connection_url,$db_name,$collection,$query) {
	try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$res = $col->find($query);
		$m->close();
		return $res;
	} catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
		return false;
   	}
   	return false;
}

function getDataFromCollection($collection) {
    global $connection_url, $db_name;
    try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$cursor = $col->find();
		return $cursor;
		$m->close();
    } catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
		return false;
    }
    return false;
}

function getNumberOfRecords($collection) {
    global $connection_url, $db_name;
    try {
   		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$count = $col->count();
		$m->close();
		return $count;
    } catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
		return false;
    }
    return false;
}

/*
 * storeDataWithID($id,$data,$collection)
 * Update or insert data with fixed id into collection
 *
 * Called by: api/v1/import_adapt.php
 */
function storeDataWithID($id,$data,$collection) {
  $ret = "";
  global $connection_url, $db_name;
  try {
    $m = new MongoClient($connection_url);
    $col = $m->selectDB($db_name)->selectCollection($collection);
	
    $query = array('id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		  $newdata = array('$set' => $data);
		  $col->update($query,$newdata);
		  $ret .= "Updated " . $data["title"] . "<br/>";
    } else {
		  $col->save($data);
		  $ret .= "Imported " . $data["title"] . "<br/>";
    }
    $m->close();
    return $ret;
  } catch ( Exception $e ) {
    $ret .= "SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
    syslog(LOG_ERR,'Error: ' . $e->getMessage());
    return $ret;
  }
  return $ret;
}

/*
 * removeByQuery($query,$collection)
 * Remove a document or set of documents as found by the query
 *
 * Called by:
 */ 
function removeByQuery($query,$collection) {
  global $connection_url, $db_name;
  try {
    $m = new MongoClient($connection_url);
    $col = $m->selectDB($db_name)->selectCollection($collection);
    $cursor = $col->find($query);
    foreach ($cursor as $doc) {
      $col->remove($doc);
    }
  } catch ( Exception $e ) {
    syslog(LOG_ERR,'Error: ' . $e->getMessage());
  }
}

/*
 * storeDatasets($datasets,$collection,$ret_keys)
 * Remove a document or set of documents as found by the query
 * The returned debug tells the user what has happened, defined by ret_keys
 *
 * Called by:
 */ 
function storeDatasets($datasets,$collection,$ret_keys) {
  global $connection_url, $db_name;
  $ret = "";
  try {
  // create the mongo connection object
    $m = new MongoClient($connection_url);
    // use the database we connected to
    $col = $m->selectDB($db_name)->selectCollection($collection);
    for($i=0;$i<count($datasets);$i++) {
      $record = $datasets[$i];
      $col->save($datasets[$i]);
      $ret .= "Imported record for ";
      for ($j=0;$j<count($ret_keys);$j++) {
      	$ret .= $datasets[$i][$ret_keys[$j]] . " ";
      }
      $ret .= "<br/>";
    }
    $m->close();
    return $ret;
  } catch ( Exception $e ) {
    syslog(LOG_ERR,'Error: ' . $e->getMessage());
  }
}

/*
 * OTHER FUNCTIONS - Both Adapt and GDocs 
 *
 */

/*
 * getCoursesData()
 * Get an array of all the courses. This is re-sorted by the primary ID of the course as defined in a Google Sheet
 * The array returns a array containing the learning outcomes for a course and it's credit value
 *						  
 * Called by: api/v1/generate_user_summary.php
 * 			  api/v1/courses.php
 * 			  api/v1/module_stats.php
 * 			  api/v1/trained_stats.php
 * 			  profile/index.php
 */
function getCoursesData() {
	global $courses_collection;
	$cursor = getDataFromCollection($courses_collection);
	$tracking = getCourseIdentifiers();
	$courses = "";
	foreach ($cursor as $doc) {
   	if ($doc["slug"]) {
			$id = $doc["slug"];
		} elseif ($doc["id"]) {
			$id = $doc["id"];
		} else {
      $id = $doc["_id"];
    }
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if (@$courses[$id] != "") {
			$courses[$id] = array_merge($courses[$id],$doc);
			$courses[$id]["id"] = $id;
		} else {
			$courses[$id] = $doc;
		}
		$los = $courses[$id]["_learningOutcomes"];
    //Adapt 2 backport
    if (!$los) {
      $los = $courses[$id]["_skillsFramework"]["_skills"];
      for ($i=0;$i<count($los);$i++) {
        $lo = $los[$i];
        $los[$i]["badge"] = $lo["level"];
      }
      $courses[$id]["_learningOutcomes"] = $los;
    }
		$badge = "";
		$total = 0;
		for ($i=0;$i<count($los);$i++) {
			$lo = $los[$i];
			$badge[$lo["badge"]] += $lo["credits"];
			$total += $lo["credits"];
		}
    if (!$courses[$id]["format"]) {
      $courses[$id]["format"] = "eLearning";
    }
		$courses[$id]["credits"] = $badge;
		$courses[$id]["totalCredits"] = $total;
	}
	return $courses;
}
/*
 * getCourseIdentifiers()
 * This function simply returns all the courseIdentifiers that have already been imported from a google sheet into a the database
 *
 * Called by: api/v1/generate_user_summary.php
 *            api/v1/module_stats.php
 *            library/functions.php
 *            profile/index.php
 */
function getCourseIdentifiers() {
    $courseIdentifiers = getDataFromCollection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["identifiers"];
   		foreach ($doc as $key => $value) {
   			for($i=0;$i<count($value);$i++) {
	 	  		$tracking[$value[$i]] = $key;
   			}
   		}
   	}
   	return $tracking;
}

/*
 * filterUsers($users,$filter,$client) 
 * Filter to user profiles that a relevant to the client/theme
 *
 * Called by: api/v1/generate_user_summary.php
 *
 */
function filterUsers($users,$filter,$client,$theme,$courses) {
  foreach($users as $email => $data) {
    $data["courses"]["complete"] = filterCourseClient($data["courses"]["complete"],$client);
    $data["eLearning"]["complete"] = filterCourseUser($data["eLearning"]["complete"],$filter,$theme,$email,$courses);
    $data["eLearning"]["in_progress"] = filterCourseUser($data["eLearning"]["in_progress"],$filter,$theme,$email,$courses);
    $data["eLearning"]["active"] = filterCourseUser($data["eLearning"]["active"],$filter,$theme,$email,$courses);
    $users[$email] = $data;
  }
  return $users;
}

/*
 * filterCourses($courses,$filter)
 * Filter the courses data to only that relevant to the user
 * 
 * Called by: api/v1/generate_user_summary.php
 * 			  api/v1/courses.php
 * 			  api/v1/module_stats.php
 * 			  api/v1/trained_stats.php
 */
function filterCourses($courses,$filter) {
  $ret = "";
  foreach ($courses as $id => $data) {
    for($i=0;$i<count($filter);$i++) {
      if (strtolower($filter[$i]) == strtolower($id) || strtolower($data["topMenu"]) == strtolower($filter[$i])) {
        $ret[$id] = $data;
      }
    }
  }
  return $ret;
}

/*
 * filterCourseUser($userdata,$filter,$theme,$email,$courses)
 * Takes the user data and filters the courses to only those a client is permitted to see
 *
 * Called by: api/v1/trained_stats.php
 *            api/v1/generate_user_summary.php
 */
function filterCourseUser($userdata,$filter,$theme,$email,$courses) {
  $ret = "";
  for($i=0;$i<count($userdata);$i++) {
    $out = $userdata[$i];
    $id = $userdata[$i]["id"];
    if (is_array($id)) {
      $id = $id["id"];
    }
    // Adapt 2
    if (in_array($courses[$id]["topMenu"],$filter)) {
      $ret[] = $out;
    } elseif (($filter[$id][0] == "ALL" || in_array($id, $filter)) && (strtolower($out["theme"]) == $theme)) {
      $ret[] = $out;
    }
  }
  return $ret;
}

/*
 * filterCourseClient($courses,$filter)
 * Filter to a single course 
 *
 * Called by: api/v1/trained_stats.php
 *			  api/v1/generate_user_summary.php
 */
function filterCourseClient($courses,$filter) {
  foreach($courses as $id => $data) {
    $client = $data["client"];
    if (strtolower($client) == strtolower($filter)) {
      $ret[] = $id;
    }
  }
  return $ret;
}
 
/*
 * getClientMapping($theme)
 * Get a list of courses that a client can see, you can use filter to filter the whole list.
 * 
 * Called by: api/v1/generate_user_summary.php
 * 			  api/v1/module_stats.php
 * 			  api/v1/trained_stats.php
 */
function getClientMapping($theme) {
    $courseIdentifiers = getDataFromCollection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["mapping"];
   		foreach ($doc as $key => $value) {
   			if ($key == $theme) {
   				return $value;
   			}
    	}
   	}
   	return false;
}

/*
 * getLMSProfile($theme)
 * Get users who have access to admin themes 
 *
 * Called by: api/v1/generate_user_summary.php
 * 			  api/v1/trained_stats.php
 */
function getLMSProfile($theme) {
	global $connection_url, $db_name;
	$collection = "externalAccess";
	$query = array('theme' => new MongoRegex('/^' .  $theme . '$/i'));
	$res = executeQuery($connection_url,$db_name,$collection,$query);
	foreach ($res as $doc) {
		return $doc;
	}
}

/*
 * USER PROFILES SECTION
 */

/* getUser($email) 
 * 
 * Get the profile for a single user and translate it back ready for the profile screen to use
 * 
 */
function getUser($email,$user) {
  global $connection_url, $db_name, $courses;
  $courses = getCoursesData();
  $collection = "elearning";
  $a1email = str_replace('.','．',$email);
  $query = array('email' => $a1email);
  $cursor = executeQuery($connection_url,$db_name,$collection,$query);
  foreach ($cursor as $doc) {
      $user = processUser($collection,$user,$doc,$email);
  }
  $query = array('Email' => $a1email);
  $cursor = executeQuery($connection_url,$db_name,$collection,$query);
  foreach ($cursor as $doc) {
      $user = processUser($collection,$user,$doc,$email);
  }
  $collection = "adapt2";
  $query = array('user.email' => $email);
  $cursor = executeQuery($connection_url,$db_name,$collection,$query);
  foreach ($cursor as $doc) {
      $user = processAdapt2User($user,$doc,$email);
  }
  return $user;
}


/*
 * getUsers($collection,$users)
 * Get active users for the learners page. Searches for users who have a known email.
 * This is based upon multiple collections so they can be loaded from all tables that contain users
 * 
 * Called by: api/v1/generate_user_summary.php: $users = getUsers("elearning",$users);
 *			  api/v1/generate_user_summary.php: $users = getUsers("externalBadges",$users);
 * 			  api/v1/generate_user_summary.php: $users = getUsers("courseAttendance",$users); 
 * 			  api/v1/trained_stats.php:		    $users = getUsers("courseAttendance","");
 */
function getUsers($collection,$users) {
	global $connection_url, $db_name;
  	$query = array('email' => array('$ne' => null));
  	$cursor = executeQuery($connection_url,$db_name,$collection,$query);
  	foreach ($cursor as $doc) {
    if ($doc["email"]) {
        $email = $doc["email"];
        $email = str_replace("．",".",$email);
        if (strpos($email,"@") > 0) {
          $users = processUser($collection,$users,$doc,$email);
        }
      }
    }
    $query = array('Email' => array('$ne' => null));
    $cursor = executeQuery($connection_url,$db_name,$collection,$query);
    foreach ($cursor as $doc) {
      if ($doc["Email"]) {
        $email = $doc["Email"];
        $email = str_replace("．",".",$email);
        if (strpos($email,"@") > 0) {
          $users = processUser($collection,$users,$doc,$email);
        }
      }
    }

    // ADAPT 2
    if ($collection == "elearning") {
      $collection = "adapt2";
      $query = array('user.email' => array('$ne' => null));
      $cursor = executeQuery($connection_url,$db_name,$collection,$query);
      foreach ($cursor as $doc) {
          $email = $doc["user"]["email"];
          $users = processAdapt2User($users,$doc,$email);
      }
    }
  	return $users;
}

/*
 * processAdapt2User($users,$doc,$email)
 * Process an individual user and return a profile from the adapt 2 data
 *
 * Called by: library/functions.php (above)
 */

function processAdapt2User($users,$doc,$email) {
  $details = $doc["user"];
  if (!$users[$email]["First Name"]) { $users[$email]["First Name"] = $details["First Name"]; } 
  if (!$users[$email]["Surname"]) { $users[$email]["Surname"] = $details["Surname"]; }
  foreach ($details as $key => $value) {
    if ($key != "email") {
      $users[$email][$key] = $value;
    }
  }
  $progress = $doc["progress"];
  foreach ($progress as $module => $data) {
    $data["id"] = $module;
    if ($data["progress"] > 99) {
        $users[$email]["eLearning"]["complete"][] = $data;
    } elseif ($data["progress"] > 50 && $data["sessionTime"] > 299) {
        $users[$email]["eLearning"]["active"][] = $data;
    } elseif ($data["progress"] > 0) {
        $users[$email]["eLearning"]["in_progress"][] = $data;
    }
  }
  $users[$email] = filterUnique($users[$email]);
  return $users;
}

/*
 * processUser($collection,$users,$doc,$email)
 * Process an individual user and return a profile
 *
 * Called by: library/functions.php (above)
 */
function processUser($collection,$users,$doc,$email) {
  global $courses,$tracking;
  $users[$email]["First Name"] = $doc["First Name"];
  $users[$email]["Surname"] = $doc["Surname"];
  if ($collection = "eLearning") {
    $users[$email]["eLearning"] = geteLearningCompletion($doc,$courses,$users[$email]["eLearning"]);
  }
  if ($collection = "courseAttendance") {
    $id = $doc["Course"];

    if ($tracking[$id]) { $id = $tracking[$id]; }
    if ($courses[$id])  { 
      $object = "";
      $object["id"] = $id;
      if ($doc["Date"]) {
        $object["date"] = $doc["Date"];
      }
      if ($doc["Client"]) {
        $object["client"] = $doc["Client"];
      }
      $users[$email]["courses"]["complete"][] = $object; 
    }
  }
  if ($collection = "externalBadges") {
    $badge = "";
    if ($doc["badge"]) {
      $badge["id"] = $doc["badge"];
      $badge["url"] = $doc["badge_url"];
      $users[$email]["badges"]["complete"][] = $badge;
    }
  }
  $users[$email] = filterUnique($users[$email]);
  return $users;
}


function filterUnique($user) {
  $user = filtereLearningUnique($user);
  $user = filterCoursesUnique($user);
  return $user;
}

// TODO Filter this on LAST SAVE FOR ACTIVE AND IN_PROGRESS! 
function filtereLearningUnique($user) {
  $el = $user["eLearning"];
  $complete = $el["complete"];
  $done = [];
  for($i=0;$i<count($complete);$i++) {
    if (!$done[$complete[$i]["id"]]) {
      $done[$complete[$i]["id"]] = true;
      $out[] = $complete[$i];
    }
  }
  $user["eLearning"]["complete"] = $out;
  
  $out = [];
  $active = $el["active"];
  for($i=0;$i<count($active);$i++) {
    if (!$done[$active[$i]["id"]]) {
      $done[$active[$i]["id"]] = true;
      $out[] = $active[$i];
    }
  }
  $user["eLearning"]["active"] = $out;

  $out = [];
  $in_progress = $el["in_progress"];
  for($i=0;$i<count($in_progress);$i++) {
    if (!$done[$in_progress[$i]["id"]]) {
      $done[$in_progress[$i]["id"]] = true;
      $out[] = $in_progress[$i];
    }
  }
  $user["eLearning"]["in_progress"] = $out;
  return $user;
}

function filterCoursesUnique($user) {
  $cs = $user["courses"]["complete"];
  $out = [];
    for($i=0;$i<count($cs);$i++) {
    if (!$done[$cs[$i]["id"]]) {
      $done[$cs[$i]["id"]] = true;
      $out[] = $cs[$i];
    }
  }
  $user["courses"]["complete"] = $out;
  return $user;
}

/*
 * geteLearningCompletion2($user,$courses,$ret)
 * Get complete, active and in_progress courses given a user
 *
 * Called by: api/v1/module_stats.php
 *        library/functions.php
 */
function geteLearningCompletion($user,$courses,$ret) {
  $theme = "";
  $theme = $user["theme"];
  foreach($user as $key => $data) {
    $key = str_replace("．",".",$key);
    if (strpos($key,"_cmi.suspend_data") !== false) {
      $course = substr($key,0,strpos($key,"_cmi"));
      $progress = $data;
      if ($courses[$course] && $courses[$course]["format"] == "eLearning") {
        $courses[$course]["progress"] = getProgress($courses[$course],$progress);
        $time = getTime($user[$course . "_cmi．core．session_time"]);

        $object = [];
        $object["id"] = $courses[$course]["id"];
        $object["progress"] = $courses[$course]["progress"];
        $object["time"] = $time;
        $object["lastSave"] = $user[$course . "_lastSave"];
        $object["theme"] = $theme;

        if ($courses[$course]["progress"] > 99) {
          $ret["complete"][] = $object;
        } else if ($time > 300) {
          $ret["active"][] = $object;
        } else {
          $ret["in_progress"][] = $object;
        }
      }
    }
  }
  return $ret;
}

/*
 * getTime($time)
 * Get the time in seconds from scorm crap
 *
 * Called by geteLearningCompletion (above)
 */
function getTime($time) {
	$time = str_replace("．",".",$time);
	$time = @substr($time,0,strpos($time,"."));
	$bits = explode(":",$time);
	$seconds = $bits[0] * 60 * 60;
	$seconds += $bits[1] * 60;
	$seconds += $bits[2];
	return $seconds;
}

/* 
 * getProgress($course,$progress)
 * Get the progress and badge 
 * 
 * NEEDS UPGRADING TO v2 API
 * 
 * Called by: api/v1/trained_stats.php
 * 			  library/functions.php
 * 			  profile/index.php
 */
function getProgress($course,$progress) {
	$spoor = json_decode($progress,true);
	$progress = $spoor["spoor"];
	if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
		$progress["completion"] = str_replace("0","1",$progress["completion"]);
		$badgeData = getModuleBadgeData($course);
		return 100;
	}
	$total = strlen($progress["completion"]);
	if ($total == 0) {
		return 0;
	}
	$sub = substr_count($progress["completion"],0);
	$complete = round(($sub / $total) * 100);
	return $complete;	
}

/* 
 * getModuleBadgeData($course)
 * Get the badge/skills and related credits object for a given course object
 *
 * YUCK DAVE 
 * 
 * Called by: library/functions.php
 *			  profile/index.php
 */
function getModuleBadgeData($course) {
	global $userBadgeCredits;
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
		$userBadgeCredits[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
}

/*
 * removeNullProfiles($users)
 * Remove profiles that have no data to show
 *
 * Called by: api/v1/generate_user_summary.php
 * 			  api/v1/trained_stats.php
 *			  library/functions.php
 */
function removeNullProfiles($users) {
  $ret = "";
  foreach ($users as $email => $data) {
    if ($data["courses"]["complete"] != null || $data["eLearning"]["complete"] !=null || $data["eLearning"]["in_progress"] !=null || $data["badges"]["complete"] != null || $data["eLearning"]["active"] !=null ) {
      $ret[$email] = $data;
    }
  }
  return $ret;
}

/*
 * removeNullProfilesBadges($users)
 * Remove profiles that have no data except badges
 *
 * Called by: api/v1/generate_user_summary.php
 *            api/v1/trained_stats.php
 *        
 */
function removeNullProfilesBadges($users) {
  $ret = "";
  foreach ($users as $email => $data) {
    if ($data["courses"]["complete"] != null || $data["eLearning"]["complete"] !=null || $data["eLearning"]["in_progress"] !=null || $data["eLearning"]["active"] !=null ) {
      $ret[$email] = $data;
    }
  }
  return $ret;
}

?>
