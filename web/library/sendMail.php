<?php
require_once('library/mandrill/Mandrill.php');
if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php')
} else {
	include_once('_includes/config.inc.php');
}

function getMailLock() {
   global $connection_url, $db_name, $collection;
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => "email_process");
	$count = $col->count($query);
	$m->close();
	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function setMailLock($state) {
   global $connection_url, $db_name, $collection;
	$m = new MongoClient($connection_url);
	$col = $m->selectDB($db_name)->selectCollection($collection);
	$query = array('_id' => "email_process");
	if($state) {
		$col->save($query);
	} else {
		$col->remove($query);
	}
	$m->close();
}

function findEmails() {
	global $connection_url, $db_name, $collection;
	setMailLock(true);
	try {
		$m = new MongoClient($connection_url);
		$col = $m->selectDB($db_name)->selectCollection($collection);
		$query = array('email_sent' => "false");
		$cursor = $col->find($query);
		$m->close();
	
		foreach ($cursor as $doc) {
			$doc = json_encode($doc);
			processEmail($doc);
		}
	} catch ( Exception $e ) {
		syslog(LOG_ERR,'Error: ' . $e->getMessage());
	}
	setMailLock(false);
}

function markDone($id) {
	global $connection_url, $db_name, $collection;
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

function processEmail($data) {
	global $eLearning_prefix;
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
	if ($theme == "TZ") {
		$prefix = "https://tanzania.learndata.info/eLearning/";
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
		markDone($id);
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
		// Mandrill errors are thrown as exceptions
		return false;
		// A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
	}
}
?>
