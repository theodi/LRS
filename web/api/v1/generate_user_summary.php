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
