<?php
	$access = 'public';
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
	$courses = getCoursesData();

?>
<script src="../js/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.js"></script>
<script type='text/javascript' src="https://cdn.datatables.net/buttons/1.2.2/js/dataTables.buttons.min.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.flash.min.js"></script>
<script type='text/javascript' src="//cdnjs.cloudflare.com/ajax/libs/jszip/2.5.0/jszip.min.js"></script>
<script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/pdfmake.min.js"></script>
<script type='text/javascript' src="//cdn.rawgit.com/bpampuch/pdfmake/0.1.18/build/vfs_fonts.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.html5.min.js"></script>
<script type='text/javascript' src="//cdn.datatables.net/buttons/1.2.2/js/buttons.print.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.2.2/css/buttons.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/t/dt/dt-1.10.11,r-2.0.2/datatables.min.css"/>
<script>
$(document).ready( function () {
    $('#profile_table').DataTable({
		"dom": 'Bfrtip',
        "buttons": [
          'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        "searching": true,
        "responsive": true
    });
} );
</script>
<style>
table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before, table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
	top: 1.3em;
	text-indent: 2px;
    line-height: 18px;
}
</style>
<?php

	drawProfile($user,$courses);
	include('_includes/footer.html');

function getModulesData() {
	$modules = getDataFromCollection("modules");
	$courses = getCoursesData();
	$output = array();
	foreach ($modules as $id => $module) {
  		if (is_array($courses[$module["_parentId"]])) {
	  		$output[$id] = $module;
  		}
	}
	return $output;
}

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
	$data = getUser($userid);
	$user = $data[$userid]["eLearning_courses"];
	$user = getF2FCompletion($userid,$user);
	$user = getExternalBadges($userid,$user);
	return $user;
}

function drawProfile($user,$courses) {
	global $userBadgeCredits;
	echo outputUserBadges($user["externalBadges"]);
	echo outputUserCredits($userBadgeCredits);
	$complete = $user["complete"];
	for ($i=0;$i<count($user["in_progress"]);$i++) {
		$in_progress[] = $user["in_progress"][$i];
	}
	for ($i=0;$i<count($user["active"]);$i++) {
		$in_progress[] = $user["active"][$i];
	}
	if (count($complete)>0) {
		echo '<h2 class="profile_h2">Completed courses</h2>';
		outputCourses($complete,"Complete",$courses);
	}
	if (count($in_progress)>0 || count($active)>0) {
		echo '<h2 class="profile_h2">Courses in progress</h2>';
		outputCourses($in_progress,"Progress",$courses);
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
				$courses[$course]["progress"] = getProgress($courses[$course],$progress)["progress"];
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
		$object["id"] = $id;
		$object["date"] = $data[$i]["Date"];
    	$object["progress"] = 100;
		if ($courses[$id]) {
			$badgeData = getModuleBadgeData($courses[$id]);
		}
		if ($id) {
			$user["complete"][] = $object;
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

function outputCourses($data,$heading,$courses) {
	echo '<table id="profile_table" style="width: 100%;">';
    echo '<thead><tr><th width="50%">Course</th><th style="width:150px;">Credits</th><th width="20%">Type</th><th width="20%">'.$heading.'</th><th class="none">Modules</th></tr></thead><tbody>';
	foreach ($data as $course) {
	        echo outputCourse($course,$course["progress"],$courses);
	}
	echo '</tbody></table>';
}

function outputCourse($doc,$progress,$courses) {
	$output = "";
	$course = $courses[$doc["id"]];
	if (!$course) {
		$course = $courses[$doc["courseID"]];
	}
	if (!$course) {
		$doc["courseID"] = str_replace("_", ".", $doc["courseID"]);
		$course = $courses[$doc["courseID"]];
		if (!$course) {
			foreach($courses as $id => $data) {
				if ($data["_trackingHub"]["_courseID"] == $doc["courseID"]) {
					$course = $data;
				}
			}
		}
	}
	if(!$course && $doc["courseID"]) {
		$course["title"] = $doc["courseID"];
		$course["format"] = "eLearning";
	}

	if ($course["web_url"]) { $url = $course["web_url"]; }
	if ($course["url"]) { $url = $course["url"]; }
   	if ($url) {
		$output .= '<tr><td id="course_name"><a target="_blank" href="'. $url .'">' . $course["title"] . '</a></td>';
	} else {
		$output .= '<tr><td id="course_name">' . $course["title"] . '</td>';
	}
    $output .= '<td style="text-align: center;">';
	$output .= outputCredits($course);
	$output .= '</td>';
	$output .= '<td style="text-align: center;"><img style="max-height: 40px;" src="/images/';
	$output .= $course["format"]; 
	$output .= '.png"></img></td>';
	$output .= '<td style="text-align: center;">';
	if (!is_numeric($progress)) {
		if (substr($course["id"],0,4) == "ODI_") {
			$dashId = str_replace("ODI_","",$course["id"]);
			$output .= '<a href="/dashboard/index.php?module=' . $dashId . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} elseif ($tracking[$course["slug"]]) {
			$output .= '<a href="/dashboard/index.php?module=' . $tracking[$course["slug"]] . '"><img src="/images/dashboard.png" width="30px"/></a>';
		} 
	} elseif ($progress == 100) {
		$output .= '<span id="tick">&#10004;</span>';
	} elseif (is_numeric($progress)) {
		$output .= '<progress max="100" value="'.$progress.'"></progress>';
	}
	$output .= '</td>';
	$output .= outputModules($doc,$progress,$courses);
    return $output;
}

function outputModules($doc,$progress,$courses) {
	$modules = getModulesData();
	$ret = '<td><table width="100%" style="margin-left: 1em;">';
	foreach($doc as $id => $data) {
		if (!is_array($data)) {
			continue;
		}
		$ret .= '<tr style="line-height: 3em;">';
		$ret .= '<td style="font-size: 1.2em;">';
		if ($modules[$id]['title']) {
			$ret .= $modules[$id]['title'];
		} else {
			$ret .= $id;
		}
		$ret .= '</td>';
		$ret .= '<td width="20%">';
		if ($data["progress"] > 99 || $data["answers"]["_assessmentState"] == "Passed" || $data["answers"]["_assessmentState"] == "Failed") {
            $ret .= '<span id="tick">&#10004;</span>';
        } elseif ($data["progress"] > 99) {
            $ret .= '<span id="tick">&#10004;</span>';
        } else {
            $ret .= '<progress max="100" value="'.$data["progress"].'"></progress>';
        }
		$ret .= '</td>';
		$ret .= '</tr>';
	}
	$ret .= '</td></tr></table></td>';
	return $ret;
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
