<?php

/* Called by the statistics page */

$access = "public";

$path = "../../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include_once('library/functions.php');
include_once('header.php');

$allStats = getCachedStats($theme,"trained","statisticsCache");
$out = "";
for($i=0;$i<count($allStats);$i++) {
	$out[$allStats[$i]["date"]] = $allStats[$i];
}
//$out[$all["date"]] = $all;
ksort($out);

$handle = fopen('php://output', 'w');
$headings = array("Date","Attended_Training","eLearning_Complete","eLearning_Active","eLearning_Modules_Complete");	
fputcsv($handle,$headings);
foreach ($out as $date => $values) {
	$line = "";
	$line[] = $date;
	$line[] = $values["trained"]["attendance"];
	$line[] = $values["trained"]["eLearning"];
	$line[] = $values["active"]["eLearning"];
	$line[] = $values["modules"]["eLearning"];
	$foo[0] = $line;
	fputcsv($handle,$foo[0]);
}
fclose($handle);

function getCachedStats($theme,$type,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$query = array('theme' => new MongoRegex('/^' .  $theme . '$/i'), 'type' => $type);
	$res = $col->find($query);
	$ret = "";
	foreach ($res as $doc) {
		$ret[] = $doc;
	}
	return $ret;
	$m->close();
	return $res;
   } catch ( MongoConnectionException $e ) {
//	return false;
	echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	echo "<br/><br/>\n\n";
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
	echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	echo "<br/><br/>\n\n";
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}
/*
function store($data,$collection) {
   global $connection_url, $db_name;
   try {
	 // create the mongo connection object
	$m = new MongoClient($connection_url);

	// use the database we connected to
	$col = $m->selectDB($db_name)->selectCollection($collection);
	
	$id = $data["id"];
	$query = array('id' => $id);
    $count = $col->count($query);
    if ($count > 0) {
		$newdata = array('$set' => $data);
		$col->update($query,$newdata);
	} else {
		$col->save($data);
	}

	$m->close();
	return true;
   } catch ( MongoConnectionException $e ) {
//	return false;
	echo "1) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>";
	syslog(LOG_ERR,'Error connecting to MongoDB server ' . $connection_url . ' - ' . $db_name . ' <br/> ' . $e->getMessage());
   } catch ( MongoException $e ) {
//	return false;
	echo "2) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
	syslog(LOG_ERR,'Mongo Error: ' . $e->getMessage());
   } catch ( Exception $e ) {
	echo "3) SOMETHING WENT WRONG" . $e->getMessage() . "<br/><br/>\n\n";
	print_r($data);
	echo "<br/><br/>\n\n";
//	return false;
	syslog(LOG_ERR,'Error: ' . $e->getMessage());
   }
}
*/
?>
