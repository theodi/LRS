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
if ($theme && $theme != "default") {
  $filter = getClientMapping($theme);
  $courses = filterCourses($courses,$filter);
}

$tracking = getCourseIdentifiers();

$users = "";
$users = getUsers("elearning",$users);
$users = getUsers("externalBadges",$users);
$users = getUsers("courseAttendance",$users); 
$users = removeNullProfiles($users);
$profile = getLMSProfile($theme);
$client = $profile["client"];
if ($profile != "") {
  $users = filterUsers($users,$filter,$client);
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
function filterUsers($users,$filter,$client) {
  foreach($users as $email => $data) {
    $data["courses"]["complete"] = filterCourseClient($data["courses"]["complete"],$client);
    $data["eLearning"]["complete"] = filterCourseUser($data["eLearning"]["complete"],$filter,$email);
    $data["eLearning"]["in_progress"] = filterCourseUser($data["eLearning"]["in_progress"],$filter,$email);
    $users[$email] = $data;
  }
  return removeNullProfiles($users);
}
function filterCourseUser($courses,$filter,$email) {
  $ret = "";
  for($i=0;$i<count($courses);$i++) {
    $id = $courses[$i];
    if (is_array($id)) {
      $id = $id["id"];
    }
    if ($filter[$id][0] == "ALL" || in_array($email, $filter[$id])) {
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
