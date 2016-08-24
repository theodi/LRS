<?php
header("Access-Control-Allow-Origin: *");
if ($_SERVER["HTTP_HOST"] == "localhost") {
  include_once('_includes/config-local.inc.php');
} else {
  include_once('_includes/config.inc.php');
}
function existsID($id) {
   global $connection_url, $db_name, $collection;
   try {
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => $id);
	$count = $col->count($query);
	if ($count > 0) {
		$m->close();
		return true;
	}
	$col = $m->selectDB($db_name)->selectCollection("adapt2");
	$query = array('_id' => $id);
	$count = $col->count($query);
	if ($count > 0) {
		$m->close();
		return true;
	}
	$m->close();
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
