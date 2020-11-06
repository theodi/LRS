<?php

header("Access-Control-Allow-Origin: *");

if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}

require_once '../vendor/autoload.php';

function store($data) {
   global $connection_url, $db_name, $collection;
   try {
	 // create the mongo connection object
   	$m = new MongoDB\Client($connection_url);
    
	// use the database we connected to
	$col = $m->selectDatabase($db_name)->selectCollection($collection);
	
	$id = $data["_id"];
	$query = array('_id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->updateOne($query,$newdata);
	} else {
		$data["email_sent"] = "false";
		$col->insertOne($data);
	}
	
	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

$data = $_POST["data"]; //Fetching all posts
$data = str_replace(".","\uff0e",$data);
$json = json_decode($data,true);

store($json);

?>
