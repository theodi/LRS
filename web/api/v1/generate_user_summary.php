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

$courses = getCoursesData();
if ($_GET["course"]) {
  $single_course[] = $_GET["course"];
  $courses = filterCourses($courses,$single_course);
}
if ($theme && $theme != "default") {
  $filter = getClientMapping($theme);
  $courses = filterCourses($courses,$filter);
}
$tracking = getCourseIdentifiers();

$users = "";
$users = getUsers("elearning",$users);
$users = getUsers("externalBadges",$users);
$users = getUsers("courseAttendance",$users);
$profile = getLMSProfile($theme);
$client = $profile["client"];

if ($profile != "") {
  $users = filterUsers($users,$filter,$client,$theme,$courses);
} elseif ($theme != "default") {
  $users = filterUsers($users,$filter,"",$theme,$courses);
} else {
  /*
  foreach($users as $email => $data) {
    $ctemp = $data["courses"]["complete"];
    $ids = "";
    if ($ctemp) {
      foreach($ctemp as $cid => $foo) {
        $ids[] = $cid;
      }
    }
    $data["courses"]["complete"] = $ids;
    $users[$email] = $data;
  }
  */
}

if ($single_course || $theme != "default") {
  $users = removeNullProfilesBadges($users);
} else {
  $users = removeNullProfiles($users);  
}
$users = getUserBadgeTotals($users);

foreach ($users as $email => $data) {
  $data["Email"] = $email;
  $output[] = $data;
}
$out["data"] = $output;

echo json_encode($out);

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

function removeNullProfilesBadges($users) {
  $ret = "";
  foreach ($users as $email => $data) {
    if ($data["courses"]["complete"] != null || $data["eLearning"]["complete"] !=null || $data["eLearning"]["in_progress"] !=null || $data["eLearning"]["active"] !=null ) {
      $ret[$email] = $data;
    }
  }
  return $ret;
}

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
    // Adapt 1
    } elseif (($filter[$id][0] == "ALL" || in_array($id, $filter)) && (strtolower($out["theme"]) == $theme)) {
      $ret[] = $out;
    }
  }
  return $ret;
}

function getUserBadgeTotals($users) {
  global $courses;
  foreach($users as $email => $data) {
    $total = 0;
    $complete = "";
    if (!is_array($data["eLearning"]["complete"])) {
      $data["eLearning"]["complete"] = [];
    }
    if (!is_array($data["courses"]["complete"])) {
      $data["courses"]["complete"] = [];
    }
    $complete = array_merge($data["eLearning"]["complete"],$data["courses"]["complete"]);
    for($i=0;$i<count($complete);$i++) {
      $total += $courses[$complete[$i]["id"]]["totalCredits"];
      if (!is_array($users[$email]["credits"])) {
        $users[$email]["credits"] = array();
      }
      $a1 = $users[$email]["credits"];
      $a2 = $courses[$complete[$i]["id"]]["credits"];
      if (!is_array($a2)) {
        $a2 = array();
      }
      $sums = array();
      foreach (array_keys($a1 + $a2) as $key) {
        $sums[$key] = @($a1[$key] + $a2[$key]);
      }
      $users[$email]["credits"] = $sums;
    }
    $users[$email]["totalCredits"] = $total;
  }
  return $users;
}

?>
