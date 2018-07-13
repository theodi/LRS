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

function getCourseID($url) {
  global $courseIDs;
  if ($courseIDs[$url]) {
    return $courseIDs[$url];
  }
  if (substr($url, 0,4) != "http") {
    $geturl = "http://" . $url . "/course/config.json";
  } else {
    $geturl = $url . "/course/config.json";
  }

  $contents = file_get_contents($geturl);
  $json = json_decode($contents,true);
  if ($json["_id"]) {
    $courseIDs[$url] = $json["_id"];
    return $json["_id"];
  } else {
    $courseIDs[$url] = str_replace(".","_",$url);
    return str_replace(".","_",$url);
  }
}

function handleLegacy($data) {

  $data["progress"]["_trackingHubID"] = $data["progress"]["courseID"];
  $data["progress"]["courseID"] = getCourseID($data["progress"]["_trackingHubID"]);

  $data["progress"]["_isComplete"] = true;
  $count = 0;
  $overall = 0;
  foreach($data["progress"] as $id => $moduleData) {
    if(is_array($moduleData)) {
      if ($moduleData["progress"] > 99 || $moduleData["_isComplete"] == true || $moduleData["answers"]["_assessmentState"] == "Passed" || $moduleData["answers"]["_assessmentState"] == "Failed") {
        $overall += 100;
      } elseif(is_numeric($moduleData["progress"])) {
        $overall += $moduleData["progress"];
        $data["progress"]["_isComplete"] = false;
      } else {
        $data["progress"]["_isComplete"] = false;
      }
      $count++;
      if (!$data["progress"]["lang"] && $moduleData["lang"]) {
        $data["progress"]["lang"] = $moduleData["lang"];
      }
      unset($moduleData["lang"]);
      if (!$data["progress"]["theme"] && $moduleData["theme"]) {
        $data["progress"]["theme"] = $moduleData["theme"];
      }
      unset($moduleData["theme"]);
      $data["progress"][$id] = $moduleData;
    }
  }
  $data["progress"]["progress"] = round($overall / $count);
  syslog(100, "overall : " . $overall . " / " . $count);
  $count = 0;
  if ($data["progress"]["_isComplete"] || $data["progress"]["answers"]["_assessmentState"]) {
    $assessmentData["isComplete"] = true;
    $assessmentData["isPercentageBased"] = true;
    $assessmentData["isPass"] = true;
    foreach($data["progress"] as $id => $moduleData) {
      if (@is_array($moduleData["answers"])) {
        $localData = [];
        $localData["id"] = "assessment" . $count;
        $localData["isComplete"] = true;
        $localData["attempts"] = 1;
        $localData["isPass"] = false;
        if ($data["progress"]["answers"]["_assessmentState"] == "Passed") {
          $localData["isPass"] = true;
        }
        $assessmentData["assessments"]++;
        $answerData = $moduleData["answers"];
        if ($answerData["_assessmentState"]) {
          $assessmentData["assessmentsComplete"]++;
          if ($answerData["_assessmentState"] != "Passed") {
            $assessmentData["isPass"] = false;
          }
        }
        foreach ($answerData as $key => $value) {
          if(is_array($answerData[$key])) {
            $localData["maxScore"]++;
            $assessmentData["maxScore"]++;
            if ($answerData[$key]["_isCorrect"]) {
              $localData["score"]++;
              $assessmentData["score"]++;
            }
          }
        }
        $localData["scoreAsPercent"] = round(($localData["score"] / $localData["maxScore"]) * 100);
        if ($moduleData["answers"]["_assessmentState"]) {
          $localData["_assessmentState"] = $moduleData["answers"]["_assessmentState"];
          unset($data["progress"][$id]["answers"]["_assessmentState"]);
        }
        $data["progress"][$id]["assessments"]["assessment" . $count] = $localData;
        $count++;
      }
    }
    $assessmentData["scoreAsPercent"] = round(($assessmentData["score"] / $assessmentData["maxScore"]) * 100);
    $data["assessments"] = $assessmentData;
  }
  return $data;
}

function store($data,$courseID) {

	 global $connection_url, $db_name;
   $collection = "adapt2";
   $id = $data["user"]["id"];
   $overall = 0; $count = 0;
   //$data["progress"]["_isComplete"] = true;
   foreach($data["progress"] as $module => $progress) {
   	 if (!is_array($progress)) {
   	 	continue;
   	 }
   	 //if ($progress["_isComplete"] == false) {
		 // $data["progress"]["_isComplete"] = false;
	   //}
   	 $overall = $overall + $progress["progress"]; 
   	 $count = $count + 1;
   	 if (!$courseID || $courseID == "") {
     	$courseID = $progress["courseID"];
     }
   }
   
   if (!$data["progress"]["courseID"]) {
    $data["progress"]["courseID"] = $courseID;
   }
   if($data["progress"]["trackingHubID"]) {
    $courseID = $data["progress"]["courseID"];
   } else {
    $courseID = getCourseID($courseID);
   }

   //Handle legacy trackingHub
   if (!$data["progress"]["theme"]) {
      $data = handleLegacy($data);
    }

    

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

#	if (!getMailLock(2)) {
#		findEmailsCollection("adapt2",2);
#	}

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
