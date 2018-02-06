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

require_once('library/sendMail.php');

function store($data,$courseID) {
   global $connection_url, $db_name;
   $collection = "adapt2";
   $courseID = str_replace(".", "_", $courseID);
   $id = $data["user"]["id"];
   $overall = 0; $count = 0;
   foreach($data["progress"] as $module => $progress) {
   	 if (!is_array($progress)) {
   	 	continue;
   	 }
   	 $overall = $overall + $progress["progress"]; 
   	 $count = $count + 1;
   }
   $data["progress"]["_overall"] = round($overall / $count);
   try {
   	if ($courseID) {
   		$toSet[$courseID] = $data;
   		$toSet["user"] = $data["user"];
   		$toSet["_id"] = $data["user"]["id"];
   		unset($toSet["user"]["welcomeDone"]);
   		unset($toSet["user"]["email_sent"]);
   		unset($toSet[$courseID]["user"]["email_sent"]);
   	} else {
   		$toSet = $data;
   	}
   	if (!$id || $id == "" || $id == null) {
		return false;
	}
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => $id);
	$count = $col->count($query);
	if ($count > 0) {
		$equery = array('_id' => $id, 'LRS.' . $courseID => array('$exists' => true));
		$ecount = $col->count($equery);
		if ($ecount < 1) {
			$updateData = array('$set' => array("LRS." . $courseID . ".email_sent" => "false"));
			$col->update($query,$updateData);
			$toSet["email_sent"] = "false";
		}
		$newdata = array('$set' => $toSet);
		$col->update($query,$newdata);
	} else {
		$toSet["email_sent"] = "false";
		if ($courseID) {
			$toSet["LRS"][$courseID]["email_sent"] = "false";
		}
		$col->save($toSet);
	}

	$m->close();

	if (!getMailLock(2)) {
		findEmailsCollection("adapt2",2);
	}

	return true;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}

$data = $_POST["data"];
$courseID = $_POST["courseID"];
$json = json_decode($data,true);

store($json,$courseID);

?>
