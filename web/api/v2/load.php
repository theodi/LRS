<?php 

error_reporting(E_ALL & ~E_NOTICE);

header("Access-Control-Allow-Origin: *");

$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
require_once '../../../vendor/autoload.php';

if ($_SERVER["HTTP_HOST"] == "localhost") {
  include_once('_includes/config-local.inc.php');
} else {
  include_once('_includes/config.inc.php');
}

function load($id) {
   global $connection_url, $db_name;
   $collection = "adapt2";
   try {
   	// create the mongo connection object
   	$m = new MongoDB\Client($connection_url);
    
	// use the database we connected to
	$col = $m->selectDatabase($db_name)->selectCollection($collection);

	$query = array('_id' => $id);
	
    $doc = $col->findOne($query);

    return json_encode($doc);

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
