<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once('library/mandrill/Mandrill.php');
if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}

function getMailLock($version) {
   global $connection_url, $db_name;
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection("emailLocks");
	$query = array('Version' => $version);
	$count = $col->count($query);
	$m->close();
	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function setMailLock($state,$version) {
   global $connection_url, $db_name;
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection("emailLocks");
	$query = array("Version" => $version);
	$count = $col->count($query);
	if ($state && $count > 0) {
		return;
	}
	if($state) {
		$col->save($query);
	} else {
		$col->remove($query);
	}
	$m->close();
}

function findEmails() {
	global $collection;
	findEmailsCollection($collection,1);
	// Not required as adapt2 store calls this directly.
	//findEmailsCollection("adapt2",2);
}


function findEmailsCollection($collection,$version) {
	setMailLock(true,$version);
	global $connection_url, $db_name;
	try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$query = array('email_sent' => "false");
		$cursor = $col->find($query);
		$m->close();
	
		foreach ($cursor as $doc) {
			$doc = json_encode($doc);
			processEmail($doc,$version);
		}
	} catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
	}
	setMailLock(false,$version);
}

function markDone($collection,$id) {
	global $connection_url, $db_name;
	try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$newdata = array('$set' => array("email_sent" => "true"));
		$col->update(array("_id"=>$id),$newdata);
		$m->close();
	} catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
	}
}

function processEmail($data,$version) {
	if ($version == 1) {
		processEmailAdapt1($data);
	} elseif ($version == 2) {
		processEmailAdapt2($data);
	}
}

function processEmailAdapt1($data) {
	global $eLearning_prefix,$collection;
	$data = json_decode($data,true);
	$id = $data["_id"];
	$email = $data["email"];
	$sent = $data["email_sent"];
	$theme = $data["theme"];
	if ($theme == "default") {
		$theme = "ODI";
	}
	$lang = $data["lang"];
	$prefix = $eLearning_prefix;
	if ($theme == "DLAB") {
		$prefix = "https://dlab.learndata.info/";
	}
	if ($theme == "EU") {
		$prefix = "http://europeandataportal.eu/elearning/" . $lang . "/";
	}
	if ($theme == "ODI") {
		$prefix = "http://accelerate.theodi.org/" . $lang . "/";
	}
	if ($email && $sent == "false") {
		$email = str_replace("．",".",$email);
		sendEmail($id,$email,$prefix,$theme);
		markDone($collection,$id);
	}
}

function processEmailAdapt2($data) {
	$data = json_decode($data,true);
	$id = $data["_id"];
	if (!$data["user"]["email"] || !$id) {
		return;
	}
	$email = $data["user"]["email"];
	$modules = $data["progress"];
	$done = [];
	foreach($modules as $uid => $values) {
		$prefix = $values["courseID"];
		$theme = $values["theme"];
		$lang = "en";
		if (!$done[$prefix]) {
			if (sendEmail($id,$email,$prefix,$theme)) {
				markDone("adapt2",$id);			
			}
			$done[$prefix] = true;
		}
	}
}

function sendEmail($id,$email,$eLearning_prefix,$theme) {
	global $mandrill_key;
	$theme = strtoupper($theme);
	try {
		$mandrill = new Mandrill($mandrill_key);
		    $template_name = $theme . ' - eLearning resume email';
		    $template_content = array();

		$message = array(
			'subject' => 'Welcome to ' . $theme . ' eLearning',
			'from_email' => 'training@theodi.org',
			'from_name' => 'ODI eLearning',
			'to' => array(
				array(
					'email' => $email,
					'type' => 'to'
				     )
				),
			'headers' => array('Reply-To' => 'training@theodi.org'),
			'important' => false,
		        'merge_vars' => array(
		        array(
		          'rcpt' => $email,
		          'vars' => array(
		            array(
		              'name' => 'ELEARNING_PREFIX',
		              'content' => $eLearning_prefix
		            ),
		            array(
		              'name' => 'ELEARNING_RESUME_ID',
		              'content' => $id
		            )
		          )
		        )
		      )
		);
		$async = false;
		$result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async);
		if($result[0]["status"] == "sent") {
			return true;
		} else {
			return false;
		}
	} catch(Mandrill_Error $e) {
		error_log("Didn't send mail " . $e);
		// Mandrill errors are thrown as exceptions
		return false;
		// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
	}
}
?>
