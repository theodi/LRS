<?php
	$location = "/profile/index.php";
    $path = "../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	include_once('library/functions.php');

	$userBadgeCredits["explorer"] = 0;
	$userBadgeCredits["strategist"] = 0;
	$userBadgeCredits["practitioner"] = 0;
	$userBadgeCredits["pioneer"] = 0;

	if ($userData["isAdmin"]) {
		enableSelectUser();
	}
	$user = getProfileData();
	drawProfile($user);
	include('_includes/footer.html');

function enableSelectUser() {
	global $userData;
	if ($_GET["sudo_user"]) {
		$userData["sudo_user"] = $_GET["sudo_user"];
	}
	echo '<form action="" method="get" style="text-align: right; position: relative; bottom: 5em; margin-bottom: -60px;">';
    echo '<input name="sudo_user" type="text" value="'.$userData["sudo_user"].'"></input>';
    echo '<input type="submit" value="Go" style="padding: 0.2em 1em; position: relative; bottom: 5px;"/>';
	echo '</form>';
}

function getProfileData() {
	global $userData;
	if ($userData["sudo_user"]) {
		$userid = $userData["sudo_user"];
	} else {
		$userid = $userData["email"];
	}
	$doc = load($userid);
	$doc = str_replace("．",".",$doc);
	$data = json_decode($doc,true);
	$user = getProfile($data);
	$user = getF2FCompletion($userid,$user);
	$user = getExternalBadges($userid,$user);
	return $user;
}

function drawProfile($user) {
	global $userBadgeCredits;
	echo outputUserBadges($user["externalBadges"]);
	echo outputUserCredits($userBadgeCredits);
	$complete = $user["complete"];
	$in_progress = $user["in_progress"];
	if (count($complete)>0) {
		echo '<h2 class="profile_h2">Completed courses</h2>';
		outputCourses($complete,"Complete");
	}
	if (count($in_progress)>0) {
		echo '<h2 class="profile_h2">Courses in progress</h2>';
		outputCourses($in_progress,"Progress");
	}
}

function getProfile($user) {
	$courses = getCoursesData();
	foreach($user as $key => $data) {
		$key = str_replace("．",".",$key);
		if (strpos($key,"_cmi.suspend_data") !== false) {
			$course = substr($key,0,strpos($key,"_cmi"));
			$progress = $data;
			if ($courses[$course]) {
				$courses[$course]["progress"] = getProgress($courses[$course],$progress);
				if ($courses[$course]["progress"] > 99) {
					$user["complete"][] = $courses[$course];
				} else {
					$user["in_progress"][] = $courses[$course];
				}
			}
		}
	}
	return $user;
}

function getF2FCompletion($userid,$user) {
	$courses = getCoursesData();
	$data = getF2FAttendance($userid);
	$tracking = getCourseIdentifiers();
	for($i=0;$i<count($data);$i++) {
		$id = $data[$i]["Course"];
		if ($tracking[$id]) {
			$id = $tracking[$id];
		}
		if ($courses[$id]) {
			$courses[$id]["progress"] = 100;
			$badgeData = getModuleBadgeData($courses[$id]);
			$user["complete"][] = $courses[$id];
		}
	}
	return $user;
}

function getF2FAttendance($userid) {
	global $connection_url, $db_name;
	$attendance = false;
	$collection = "courseAttendance";
	$query = array('Email' => $userid, "Attended" => "Yes");
	$res = executeQuery($connection_url,$db_name,$collection,$query);
	foreach ($res as $doc) {
		$attendance[] = $doc;
	}
   	return $attendance;
}

function getExternalBadges($userid,$user) {
	$user["externalBadges"] = getExternalBadgeData($userid);
	return $user;
}

/*
 * getExternalBadgeData($userid)
 * Get badges that a user has been awarded from the Google Sheets place
 * 
 * Called by: here only
 */
function getExternalBadgeData($userid) {
   global $connection_url, $db_name;
   $attendance = false;
   $collection = "externalBadges";
   $query = array('Email' => $userid);
   $res = executeQuery($connection_url,$db_name,$collection,$query);
   foreach ($res as $doc) {
		$badges[] = $doc;
   }
   return $badges;
}

function outputUserBadges($badges) {
	$output = '<div align="right" style="margin-bottom:10px;">';
	for($i=0;$i<count($badges);$i++) {
		$url = $badges[$i]["badge_url"];
		$name = $badges[$i]["badge"];
		$output .= '<img class="awardedBadge" src="'.$url.'" alt="'.$name.'"/>';
	}
	$output .= '</div>';
	return $output;
}

function outputCourses($courses,$heading) {
	echo '<table style="width: 100%;">';
        echo '<tr><th width="50%"></th><th style="width:150px;">Credits</th><th width="20%">Type</th><th width="20%">'.$heading.'</th></tr>';
	foreach ($courses as $course) {
	        echo outputCourse($course,$course["progress"]);
	}
	echo '</table>';
}

function outputCourse($doc,$progress) {
	$output = "";
   	if ($doc["web_url"]) {
		$output .= '<tr><td id="course_name"><a target="_blank" href="'.$doc["web_url"].'">' . $doc["title"] . '</a></td>';
	} else {
		$output .= '<tr><td id="course_name">' . $doc["title"] . '</td>';
	}
     	$output .= '<td style="text-align: center;">';
	$output .= outputCredits($doc);
	$output .= '</td>';
	$output .= '<td style="text-align: center;"><img style="max-height: 40px;" src="/images/';
	$output .= $doc["format"]; 
	$output .= '.png"></img></td>';
	$output .= '<td style="text-align: center;">';
	if ($progress == "") {
		if (substr($doc["id"],0,4) == "ODI_") {
			$dashId = str_replace("ODI_","",$doc["id"]);
			$output .= '<a href="/dashboard/index.php?module=' . $dashId . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} elseif ($tracking[$doc["slug"]]) {
			$output .= '<a href="/dashboard/index.php?module=' . $tracking[$doc["slug"]] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} 
	} elseif ($progress == 100) {
		$output .= '<span id="tick">&#10004;</span>';
	} elseif (is_numeric($progress)) {
		$output .= '<progress max="100" value="'.$progress.'"></progress>';
	}
	$output .= '</td>';
	$output .= '</tr>';
        return $output;
}

function outputUserCredits($data) {
	$box = '<div align="right"><table id="user_credits"><tr>';
	foreach ($data as $key => $value) {
		$total += $value;
		$box .= '<td id="user_badge_cell"><svg id="user_badge" width="80" height="60">
  					<image xlink:href="/images/badges/'.$key.'.svg" src="/images/badges'.$key.'.png" width="80" height="60" />
				</svg><br/>'.ucwords($key).'</td>';
		$box .= '<td id="user_credits_score">' . $value . '</td>';
	}
	$box .= '</tr></table></div>';
	return $box;
}

function outputCredits($course) {
	$data = getCourseCreditsByBadge($course);
	return outputCreditsTable($data);
}

/*
 * getCourseCreditsByBadge($id)
 * Get the credits for a given course id
 *
 * Caleed by profile/index.php
 */
function getCourseCreditsByBadge($course) {
	$badge["explorer"] = 0;
	$badge["strategist"] = 0;
	$badge["practitioner"] = 0;
	$badge["pioneer"] = 0;
	$los = $course["_learningOutcomes"];
	for ($i=0;$i<count($los);$i++) {
		$lo = $los[$i];
		$badge[$lo["badge"]] += $lo["credits"];
	}
	return $badge;
}

function outputCreditsTable($data) {
	$rows = "";
	foreach ($data as $key => $value) {
		$total += $value;
		$rows .= "<tr><td>" . $key . "</td><td>" . $value . '</td></tr>';
	}
	$box = '<div id="course_credits_box"><score>' . $total .' </score><table id="course_credits_table">';
	$box .= $rows;
	$box .= '</table></div>';
	return $box;
}
?>
