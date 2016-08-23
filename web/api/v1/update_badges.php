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
$badge = str_getcsv($data[2])[1];
$badge_url = str_getcsv($data[3])[1];
$source = $id;
$all = prepareData($data,$badge,$badge_url,$source);

$query = array('source' => $id);
$ret = removeByQuery($query,"externalBadges");
$ret_keys = "";
$ret_keys[] = "First Name";
$ret_keys[] = "Surname";
$ret_keys[] = "Email";
$ret = storeDatasets($all,"externalBadges",$ret_keys);

echo $ret;

function prepareData($lines,$badge,$badge_url,$source) {
  $headers = str_getcsv($lines[4]);
  for($i=5;$i<count($lines);$i++) {
    $line = str_getcsv($lines[$i]);
	  $record = "";
    $record["source"] = $source;
    $record["badge"] = $badge;
    $record["badge_url"] = $badge_url;
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

function getRawData($id) {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php ' . $id,$output);
  return $output;
}

?>
