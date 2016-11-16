<?php

error_reporting(E_ALL ^ E_NOTICE);
$access = "viewer";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');
if (!$userData["isAdmin"]) {
  if (!$userData["externalAccess"]["courses"]) {
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
  $users = filterUsers($users,$filter,$client,$theme);
} else {
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
function filterUsers($users,$filter,$client,$theme) {
  foreach($users as $email => $data) {
    $data["courses"]["complete"] = filterCourseClient($data["courses"]["complete"],$client);
    if (strtolower($data["eLearning"]["theme"]) == strtolower($theme)) {
      $data["eLearning"]["complete"] = filterCourseUser($data["eLearning"]["complete"],$filter,$email);
      $data["eLearning"]["in_progress"] = filterCourseUser($data["eLearning"]["in_progress"],$filter,$email);
      $data["eLearning"]["active"] = filterCourseUser($data["eLearning"]["active"],$filter,$email);
    } else {
      $data["eLearning"]["active"] = [];
      $data["eLearning"]["complete"] = [];
      $data["eLearning"]["in_progress"] = [];
    }
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

function filterCourseUser($courses,$filter,$email) {
  $ret = "";
  for($i=0;$i<count($courses);$i++) {
    $id = $courses[$i];
    if (is_array($id)) {
      $id = $id["id"];
    }
    if ($filter[$id][0] == "ALL" || in_array($id, $filter)) {
      $ret[] = $id;
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
      $total += $courses[$complete[$i]]["totalCredits"];
      if (!is_array($users[$email]["credits"])) {
        $users[$email]["credits"] = array();
      }
      $a1 = $users[$email]["credits"];
      $a2 = $courses[$complete[$i]]["credits"];
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
