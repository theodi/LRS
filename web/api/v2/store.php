<?php

error_reporting(E_ALL & ~E_NOTICE);

header("Access-Control-Allow-Origin: *");

$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
if ($_SERVER["HTTP_HOST"] == "localhost") {
  include_once('_includes/config-local.inc.php');
} else {
  include_once('_includes/config.inc.php');
}
require_once '../../../vendor/autoload.php';

function store($data) {#
   global $connection_url, $db_name;
   $collection = "adapt2";
   try {
   	$data["_id"] = $data["user"]["id"];	
	$id = $data["user"]["id"];
   	if (!$id || $id == "" || $id == null) {
		return false;
	}
	// create the mongo connection object
   	$m = new MongoDB\Client($connection_url);
	// use the database we connected to
	$col = $m->selectDatabase($db_name)->selectCollection($collection);
	$query = array('_id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->updateOne($query,$newdata);
	} else {
		$data["email_sent"] = "false";
		$col->insertOne($data);
	}
/*
    if (!getMailLock(2)) {
		findEmailsCollection("adapt2",2);
	}
*/
	return true;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

$data = $_POST["data"];
$json = json_decode($data,true);

store($json);

?>
