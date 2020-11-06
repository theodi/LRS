<?php
header("Access-Control-Allow-Origin: *");
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once '../../../vendor/autoload.php';

if ($_SERVER["HTTP_HOST"] == "localhost") {
  include_once('_includes/config-local.inc.php');
} else {
  include_once('_includes/config.inc.php');
}
function existsID($id) {
   global $connection_url, $db_name, $collection;
   try {
   	// create the mongo connection object
   	$m = new MongoDB\Client($connection_url);
    
	// use the database we connected to
	$col = $m->selectDatabase($db_name)->selectCollection($collection);
	
	$query = array('_id' => $id);
	
	$count = $col->count($query);
	
	if ($count > 0) {
		return true;
	}
	$col = $m->selectDatabase($db_name)->selectCollection("adapt2");

	$query = array('_id' => $id);

	$count = $col->count($query);

	if ($count > 0) {
		return true;
	}

	return false;
	
   } catch ( Exception $e ) {
		return false;
   }
   return false;
}

function GUID()
{
    if (function_exists('com_create_guid') === true)
    {
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}

$guid = GUID();
while (existsID($guid)) {
	$guid = GUID();
}

echo $guid;
?>
