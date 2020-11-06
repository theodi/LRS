<?php
error_reporting(E_ALL & ~E_NOTICE);

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
      if ($doc) {
   		 foreach ($doc as $key => $value) {
   		 	if ($key == $host) {
   				return $value[0];
   			}
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
    $m = new MongoDB\Client($connection_url);
    $col = $m->selectDatabase($db_name)->selectCollection($collection);
    $cursor = $col->find($query);
		return $cursor;
	} catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
		return false;
   	}
   	return false;
}

function getDataFromCollection($collection) {
    global $connection_url, $db_name;
    try {
      $m = new MongoDB\Client($connection_url);
      $col = $m->selectDatabase($db_name)->selectCollection($collection);
      $cursor = $col->find();
		  return $cursor;
    } catch ( Exception $e ) {
		  syslog(LOG_ERR,'Error: ' . $e->getMessage());
		  return false;
    }
    return false;
}

function getNumberOfRecords($collection) {
    global $connection_url, $db_name;
    try {
   		$m = new MongoDB\Client($connection_url);
      $col = $m->selectDatabase($db_name)->selectCollection($collection);
      $cursor = $col->find();
		  $count = $col->count();
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
    $m = new MongoDB\Client($connection_url);
    $col = $m->selectDatabase($db_name)->selectCollection($collection);
    $query = array('id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		  $newdata = array('$set' => $data);
		  $col->updateOne($query,$newdata);
		  $ret .= "Updated " . $data["title"] . "<br/>";
    } else {
		  $col->insertOne($data);
		  $ret .= "Imported " . $data["title"] . "<br/>";
    }
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
    $m = new MongoDB\Client($connection_url);
    $col = $m->selectDatabase($db_name)->selectCollection($collection);
    $cursor = $col->find($query);
    foreach ($cursor as $doc) {
      $col->deleteOne($doc);
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
    $m = new MongoDB\Client($connection_url);
    $col = $m->selectDatabase($db_name)->selectCollection($collection);
    for($i=0;$i<count($datasets);$i++) {
      $record = $datasets[$i];
      $col->insertOne($datasets[$i]);
      $ret .= "Imported record for ";
      for ($j=0;$j<count($ret_keys);$j++) {
      	$ret .= $datasets[$i][$ret_keys[$j]] . " ";
      }
      $ret .= "<br/>";
    }
    return $ret;
  } catch ( Exception $e ) {
    return "CAUGHT AN ERROR! " . $e->getMessage();
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
	global $courses_collection,$theme;
  $cursor = getDataFromCollection($courses_collection);
	$tracking = getCourseIdentifiers();
	$courses = array();
	foreach ($cursor as $doc) {
    $doc = json_decode(json_encode($doc),true);
   	if ($doc["slug"]) {
			$id = $doc["slug"];
    } elseif ($doc["_trackingHub"]["_pageID"]) {
      $id = $doc["_trackingHub"]["_pageID"];
    } elseif ($doc["id"]) {
      $id = $doc["id"];
		} else {
      $id = $doc["_id"];
    }
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if (@$courses[$id] != "") {
      if ($courses[$id]["_trackingHub"]["_pageID"]) {
        $tmpurl = $courses[$id]["url"];
      }
			$courses[$id] = array_merge($courses[$id],$doc);
			$courses[$id]["id"] = $id;
      if ($courses[$id]["url"] == "") {
        $courses[$id]["url"] = $tmpurl;
      } 
      if ($doc["theme"] == $theme) {
        $courses[$id]["url"] = $doc["url"];
      }
		} else {
			$courses[$id] = $doc;
		}
		$los = $courses[$id]["_learningOutcomes"];
    //Adapt 2 backport
    if (!$los) {
      $los = $courses[$id]["_skillsFramework"]["_skills"];
      if ($los) {
        for ($i=0;$i<count($los);$i++) {
          $lo = $los[$i];
          $los[$i]["badge"] = $lo["level"];
        }
        $courses[$id]["_learningOutcomes"] = $los;
      }
    }
		$badge = array();
		$total = 0;
    if ($los) {
		  for ($i=0;$i<count($los);$i++) {
  			$lo = $los[$i];
			 $badge[$lo["badge"]] += $lo["credits"];
			 $total += $lo["credits"];
		  }
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
      if ($doc) {
   		 foreach ($doc as $key => $value) {
   			  for($i=0;$i<count($value);$i++) {
  	 	  		$tracking[$value[$i]] = $key;
   			  }
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
    } elseif (($filter[$id][0] == "ALL" || in_array($id, $filter)) && (strtolower($out["theme"]) == $theme || $out["theme"] == $theme)) {
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
      $ret[] = $data;
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
    $ret = [];
    $courseIdentifiers = getDataFromCollection("courseIdentifiers");
    foreach ($courseIdentifiers as $doc) {
   		$doc = $doc["mapping"];
   		foreach ($doc as $key => $value) {
   			if ($key == $theme) {
          $ret = array_merge($ret,$value);
   			}
    	}
   	}
    if ($ret) {
      return $ret;
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
	$query = array('theme' => new MongoDB\BSON\Regex('^'.$theme.'$', 'i'));
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
  $users[$email]["user"] = $details;
  if (!$users[$email]["user"]["location"]) { 
    $users[$email]["user"]["location"] = $details["country"]; 
    if ($details["region"] != "" && $details["region"] != "null") {
      $users[$email]["user"]["location"] .= ' (' . $details["region"] . ')';
    }
  }

  foreach ($details as $key => $value) {
    if ($key != "email") {
      $users[$email][$key] = $value;
    }
  }
  $progress = $doc["progress"];
  foreach ($progress as $module => $data) {
    $data["id"] = $module;
    if ($data["progress"] > 99 || $data["answers"]["_assessmentState"] == "Passed" || $data["answers"]["_assessmentState"] == "Failed") {
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

  $users[$email]["user"]["firstname"] = $doc["First Name"];
  $users[$email]["user"]["lastname"] = $doc["Surname"];
  $gender = $doc["gender"]; if($doc["Gender"]) $gender = $doc["Gender"];
  $users[$email]["user"]["gender"] = $gender;
  $age = $doc["age"]; if($doc["Age"]) $age = $doc["Age"]; if (!$age || $age == null) $age = "";
  $users[$email]["user"]["age"] = $age;
  $sector = $doc["sector"]; if($doc["Sector"]) $sector = $doc["Sector"]; if (!$sector || $sector == null) $sector = "";
  $users[$email]["user"]["sector"] = $sector;
  $users[$email]["user"]["email"] = $email;
  $users[$email]["user"]["lastSave"] = $doc["ODI_lastSave"];
  $users[$email]["user"]["id"] = $doc["_id"];
  $users[$email]["id"] = $doc["_id"];

  $country = $doc["country"]; 
  if($doc["Country"]) {
      $country = $doc["Country"];
  }
  
  if ($country != "") {
    $users[$email]["user"]["location"] = $country;
    $users[$email]["user"]["country"] = $country;
  }

  $region = $doc["region"]; 
  if($doc["Region"]) {
      $region = $doc["Region"];
  }

  if ($region != "" && $region != "null") {
      $users[$email]["user"]["region"] = $region;
      $users[$email]["user"]["location"] .= ' (' . $region . ')';
  }
  if ($collection = "eLearning") {
    $users[$email]["eLearning"] = geteLearningCompletion($doc,$courses,$users[$email]["eLearning"]);
    if ($users[$email]["user"]["firstname"] == "") {
	    $users[$email]["user"]["firstname"] = $doc["firstname"];
    	$users[$email]["user"]["lastname"] = $doc["lastname"];
    }
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
  if($complete) {
    for($i=0;$i<count($complete);$i++) {
      if (!$done[$complete[$i]["id"]]) {
        $done[$complete[$i]["id"]] = true;
        $out[] = $complete[$i];
      }
    }
    $user["eLearning"]["complete"] = $out;
  }
  
  $out = [];
  $active = $el["active"];
  if($active) {
    for($i=0;$i<count($active);$i++) {
      if (!$done[$active[$i]["id"]]) {
        $done[$active[$i]["id"]] = true;
        $out[] = $active[$i];
      }
    }
    $user["eLearning"]["active"] = $out;
  }

  $out = [];
  $in_progress = $el["in_progress"];
  if ($in_progress) {
    for($i=0;$i<count($in_progress);$i++) {
      if (!$done[$in_progress[$i]["id"]]) {
        $done[$in_progress[$i]["id"]] = true;
        $out[] = $in_progress[$i];
      }
    }
    $user["eLearning"]["in_progress"] = $out;
  }
  return $user;
}

function filterCoursesUnique($user) {
  $cs = $user["courses"]["complete"];
  $out = [];
  if ($cs) {
    for($i=0;$i<count($cs);$i++) {
      if (!$done[$cs[$i]["id"]]) {
        $done[$cs[$i]["id"]] = true;
        $out[] = $cs[$i];
      }
    }
    $user["courses"]["complete"] = $out;
  }
  return $user;
}

function getAdaptComponents() {
  $cursor = getDataFromCollection("adaptComponents");
  foreach($cursor as $doc) {
    $componentItems[$doc["_id"]] = $doc;
    //print_r($componentItems);
  }
  return $componentItems;
}
/*
 * geteLearningCompletion($user,$courses,$ret)
 * Get complete, active and in_progress courses given a user
 *
 * ADAPT 1
 *
 * Called by: api/v1/module_stats.php
 *        library/functions.php
 */
function geteLearningCompletion($user,$courses,$ret) {
  //global $componentItems;
  //if (!$collectionItems) {
  //  $componentItems = getAdaptComponents();
  //}
  $theme = "";
  $theme = $user["theme"];
  $lang = $user["lang"];
  $platform = $user["platform"];
  foreach($user as $key => $data) {
    $key = str_replace("．",".",$key);
    if (strpos($key,"_cmi.suspend_data") !== false) {
      $course = substr($key,0,strpos($key,"_cmi"));
      $progress = $data;
      if ($courses[$course] && $courses[$course]["format"] == "eLearning") {
        $spoor = getProgress($courses[$course],$progress);
        $courses[$course]["progress"] = $spoor["progress"];
        $courses[$course]["assessmentPassed"] = "false";
        if ($spoor["_isAssessmentPassed"]) {
          $courses[$course]["assessmentPassed"] = "true";
        }
        $time = getTime($user[$course . "_cmi．core．session_time"]);

        $answers = $user[$course . "_cmi．answers"];
        $answers = json_decode($answers,true);
        if (!is_array($answers)) {
          $courses[$course]["assessmentPassed"] = "not attempted";
        }
        $out = [];
        if ($answers) {
          foreach ($answers as $id => $data) {
            unset($data["userAnswer"]);
            $out[$id] = $data;
          }
        }
        $object = [];
        $object["id"] = $courses[$course]["id"];
        $object["progress"] = $courses[$course]["progress"];
        $object["assessmentPassed"] = $courses[$course]["assessmentPassed"];
        $object["time"] = $time;
        $object["lastSave"] = $user[$course . "_lastSave"];
        $object["theme"] = $theme;
        $object["lang"] = $lang;
        $object["platform"] = $platform;
        $object["answers"] = $out;

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
  if (!is_numeric($time)) {
    return 0;
  }
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
 *  ADAPT 1
 * 
 * Called by: 
 * 			  library/functions.php
 * 			  profile/index.php
 */
function getProgress($course,$progress) {
	$spoor = json_decode($progress,true);
	$progress = $spoor["spoor"];
	if ($progress["_isAssessmentPassed"] > 0 || $progress["_isCourseComplete"] > 0) {
		$progress["completion"] = str_replace("0","1",$progress["completion"]);
		$badgeData = getModuleBadgeData($course);
		$progress["progress"] = 100;
	} else {
	 $total = strlen($progress["completion"]);
	 if ($total == 0) {
		  $progress["progress"] = 0;
	 } else {
    $sub = substr_count($progress["completion"],0);
	  $complete = round(($sub / $total) * 100);
    $progress["progress"] = $complete;
   }
  }
  return $progress;
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
  if ($los) {
  	for ($i=0;$i<count($los);$i++) {
  		$lo = $los[$i];
  		$badge[$lo["badge"]] += $lo["credits"];
  		$userBadgeCredits[$lo["badge"]] += $lo["credits"];
  	}
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
  $ret = array();
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
