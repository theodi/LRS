<?php
error_reporting(E_ALL ^ E_WARNING);
$debug = false;
$access = "public";
$path = "../../";

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

include_once('library/functions.php');
include_once('header.php');

header("Access-Control-Allow-Origin: *");

if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}

$moduleID = $_GET["module"];
if (!$moduleID) {
	exit(0);
}

$query = array("_id" => $moduleID);
$res = executeQuery($connection_url,$db_name,"modules",$query);
foreach($res as $doc) {
	echo json_encode($doc);
	exit(1);
}