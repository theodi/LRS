<?php
//set_include_path(get_include_path() . PATH_SEPARATOR . $path);
$access = "admin";
$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$count = archive_empty_profiles();

if ($count === false) {
	echo "FAILED";
} else {
	echo "Complete<br/>" . $count . " archived.";
}

function archive_profile($doc) {
	foreach ($doc as $key=>$data) {
		if (strpos($key, "suspend") > 0) {
			return false;
		}
	}
	return true;
}

function archive_empty_profiles() {
   global $collection_url, $db_name;
   $collection = "elearning";
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$cursor = $col->find();
	
	$doneCount = 0;

	$col2 = $m->selectDB($db_name)->selectCollection("elearning-deleted");
	foreach ($cursor as $doc) {
		$archive = true;
		$id = $doc["_id"];
		$query = array('_id' => $id);
		if (archive_profile($doc)) {
			//echo "Archive " . $id . "<br/>";
			//echo "<br/>================</br>";
        	$count = $col2->count($query);
        	if ($count > 0) {
				$newdata = array('$set' => $doc);
				$col2->update($query,$newdata);
			} else {
				$col2->save($doc);
			}
			$col->remove($doc);
			$doneCount++;
		}
	}
		
	$m->close();

	return $doneCount;
   } catch ( MongoConnectionException $e ) {
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
	return false;
   } catch ( MongoException $e ) {
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
	return false;
   } catch ( Exception $e ) {
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
	return false;
   }
}


?>
 