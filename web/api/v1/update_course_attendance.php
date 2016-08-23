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

$data = getCourseAttendanceData($id);

$all = prepareData($data,$id);

$query = array('source' => $id);
$ret = removeByQuery($query,"courseAttendance");
$ret_keys = "";
$ret_keys[] = "First Name";
$ret_keys[] = "Surname";
$ret_keys[] = "Course";
$ret = storeDatasets($all,"courseAttendance",$ret_keys);

echo $ret;

function prepareData($lines,$id) {
  $headers = str_getcsv($lines[0]);
  for($i=1;$i<count($lines);$i++) {
    $line = str_getcsv($lines[$i]);
	  $record = "";
    $record["source"] = $id;
	  for($j=0;$j<count($headers);$j++) {
		  $record[$headers[$j]] = $line[$j];
	  }
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

function getCourseAttendanceData($id) {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php ' . $id,$output);
  return $output;
}

?>
