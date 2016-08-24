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

function load($id) {
   global $connection_url, $db_name;
   $collection = "adapt2";
   try {
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => $id);
	$res = $col->find($query);	
	$m->close();
	foreach ($res as $doc) {
 	   return json_encode($doc);
	}
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

$id = $_GET["id"]; //Fetching all posts
if (!$id) exit(0);

$data = load($id);
header('Content-Type: application/json');
echo $data;

?>
