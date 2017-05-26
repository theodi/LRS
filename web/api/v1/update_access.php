<?php
$access = "admin";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$id = $_GET['id'];
if (!$id || $id == "") {
  return "GO AWAY";
}

$data = getRawData($id);

$outer = [];
$profile = str_getcsv($data[0])[1];
$theme = str_getcsv($data[1])[1];
$client = str_getcsv($data[2])[1];
$source = $id;
$emails = getColumn($data,0,5);

// YOU WERE HERE DAVE
$courseAttendance = getColumn($data,1,5);
for ($i=0;$i<count($courseAttendance);$i++) {
  importCourseAttendance($courseAttendance[$i],$client,$theme);
}

function importCourseAttendance($id,$client,$theme) {
  global $courses_collection;
  echo "Need to import " . $id . " for client " . $client . "<br/>";
  $data = getRawData($id);
  $courses = array_unique(getColumn($data,0,1));
  $course_object = "";
  $mapping_object = "";
  $mapping_object["id"] = $id;
  foreach ($courses as $item => $course) {
    if ($course != "") {
      $courseId = str_replace(" ", "-", $course);
      $courseId = preg_replace('/[^A-Za-z0-9\-]/', '', $courseId);
      $courseId = $theme . "_" . $courseId;
      
      $mapping_object["mapping"][$theme][] = $courseId;
    
      $course_object = "";
      $course_object["_id"] = $courseId;
      $course_object["id"] = $courseId;
      $course_object["title"] = $course;
      $course_object["displayTitle"] = $course;
      $course_object["_type"] = "course";
      $course_object["format"] = "course";

      echo storeDataWithID($course_object["id"],$course_object,"courses");
    }
  }
  echo storeDataWithID($mapping_object["id"],$mapping_object,"courseIdentifiers");

  $all = prepareAttendanceData($data,$id,$client,$theme);

  $query = array('source' => $id);
  $ret = removeByQuery($query,"courseAttendance");
  $ret_keys = "";
  $ret_keys[] = "First Name";
  $ret_keys[] = "Surname";
  $ret_keys[] = "Course";

  $ret = storeDatasets($all,"courseAttendance",$ret_keys);

  echo $ret;

}

function prepareAttendanceData($lines,$id,$client,$theme) {
  $headers = str_getcsv($lines[0]);
  for($i=1;$i<count($lines);$i++) {
    $line = str_getcsv($lines[$i]);
    $record = "";
    $record["source"] = $id;
    for($j=0;$j<count($headers);$j++) {
      if ($headers[$j] != "") {
        $record[$headers[$j]] = $line[$j];
      }
    }
    $courseId = str_replace(" ", "-", $record["Course"]);
    $courseId = preg_replace('/[^A-Za-z0-9\-]/', '', $courseId);
    $courseId = $theme . "_" . $courseId;
    $record["Course"] = $courseId;
    $record["Client"] = $client;
    if ($record["Email"] == "" && $record["First Name"] == "") {
    } else {
      if ($record["Email"]) {
          $record["Email"] = strtolower($record["Email"]);
      }
      $out[] = $record;
    }
  }
  return $out;
}

// Need to import course names into courses table and create identifiers from just one column, always linked to client
// Need to import course attendance for this course, again linked only to client

// With this done, if there is a specific client set the attendance and courses list is filtered to not show this data in the default all view. This includes for the dashboards and statistics! 


$all = "";
for($i=0;$i<count($emails);$i++) {
  $record = "";
  $record["source"] = $source;
  $record["profile"] = $profile;
  $record["theme"] = $theme;
  $record["email"] = $emails[$i];
  $record["client"] = $client;
  $all[] = $record;
}

$query = array('source' => $id);
$ret = removeByQuery($query,"externalAccess");
$ret_keys = "";
$ret_keys[] = "email";
$ret_keys[] = "theme";
$ret = storeDatasets($all,"externalAccess",$ret_keys);

echo $ret;


function getColumn($lines,$index,$start) {
  $array = "";
  for($i=$start;$i<count($lines);$i++) {
      $line = str_getcsv($lines[$i]);
      $value = $line[$index];
      if ($value) {
          $array[] = $value;
      }
  }
  return $array;
}

function getRawData($id) {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php ' . $id,$output);
  return $output;
}

?>
