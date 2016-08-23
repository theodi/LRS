<?php
$access = "admin";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$data = getCourseIdentifierData();
$mapping = getCourseMappingData();
$hosts = getHostsData();

$all["mapping"] = prepareData($mapping);
$all["identifiers"] = prepareData($data);
$all["hosts"] = prepareHostsData($hosts);
$all["id"] = "CourseIdentifiers";
$all["title"] = "Course Identifiers";
//print_r($all);

echo storeDataWithID($all["id"],$all,"courseIdentifiers");

function prepareData($data) {
  $master = "";
  for($i=1;$i<count($data);$i++) {
    $bits = str_getcsv($data[$i]);
    $master[$bits[0]][] = $bits[1];  
  }
  return $master;
}

function prepareHostsData($data) {
  $master = "";
  for($i=1;$i<count($data);$i++) {
    $bits = str_getcsv($data[$i]);
    $master[str_replace(".","_",$bits[0])][] = $bits[1];  
  }
  return $master;
}

function getHostsData() {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php 1MVEBNzmvQRUz0787NMBcp69GM4w9zwaIHt_WNVsxizQ',$output);
  return $output;
}

function getCourseMappingData() {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php 1s01ZBNyTsGScQZFPZNhBDfYACcwXvvaSMiT9yAFHIyc',$output);
  return $output;
}
function getCourseIdentifierData() {
  global $exec_path;
  $content = exec('php ' . $exec_path . '/getFile.php 17gtugoN05aYnWN07Exf6_RpdknlpCfi6a1WSTyJ_z7c',$output);
  return $output;
}

?>
