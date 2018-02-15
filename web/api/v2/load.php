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

function load($id,$courseID) {
   global $connection_url, $db_name;
   $collection = "adapt2";
   $courseID = str_replace(".", "_", $courseID);
   try {
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => $id);
	$res = $col->find($query);	
	$m->close();
	foreach ($res as $doc) {
		if ($courseID) {
			error_log("returning by courseID");
 	   		return json_encode($doc[$courseID]);
 	   	} elseif ($doc[$_SERVER["HTTP_REFERER"]]) {
 	   		error_log("returning by referer");
			$courseID = str_replace(".","_",$_SERVER["HTTP_REFERER"]);
 	   		return json_encode($doc[$courseID]);
 	   	} else {
 	   		foreach ($doc as $module => $parts) {
 	   			if (isset($parts["components"])) {
 	   				error_log("returning by object");
 	   				return json_encode($doc[$module]);
 	   			}
 	   		}
 	   		error_log("returning whole doc");
 	   		return json_encode($doc);
 	 	}
	}
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

$id = $_GET["id"];
$courseID = $_GET["courseID"];
if (!$id) exit(0);

$data = load($id,$courseID);
header('Content-Type: application/json');
echo $data;

?>
