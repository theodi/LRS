<?php
header("Access-Control-Allow-Origin: *");
require_once '../vendor/autoload.php';
if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}

function load($id) {
   global $connection_url, $db_name, $collection;
   try {

	 // create the mongo connection object
   	$m = new MongoDB\Client($connection_url);
    
	// use the database we connected to
	$col = $m->selectDatabase($db_name)->selectCollection($collection);
	
	$query = array('_id' => $id);

    $doc = $col->findOne($query);

    return json_encode($doc);
	
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

$id = $_GET["id"]; //Fetching all posts
if (!$id) exit(0);

$data = load($id);
header('Content-Type: application/json');
echo str_replace("\uff0e",".",$data);

?>
