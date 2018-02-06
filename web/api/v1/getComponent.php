<?php
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

error_reporting(E_ALL ^ E_NOTICE);

$id = $_GET["id"];
if (!$id) {
	header("HTTP/1.0 400 Bad Request");
	echo "";
	exit(0);
}
$query = array('_id' => $id);
$collection = "adapt2Components";
$res = executeQuery($connection_url,$db_name,$collection,$query);
foreach ($res as $doc) {
	header('Content-Type: application/json');
	echo json_encode($doc);
	exit(0);
}
header("HTTP/1.0 404 Not Found");
echo "";
exit(0);

?>