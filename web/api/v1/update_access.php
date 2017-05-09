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
$emails = getColumn($data,0);

// YOU WERE HERE DAVE
$courseAttendance = getColumn($data,1);
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




function getColumn($lines,$index) {
  $array = "";
  for($i=5;$i<count($lines);$i++) {
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
