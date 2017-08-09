<?php
	$connection_url = getenv("MONGOLAB_URI");
	$url = parse_url($connection_url);
	$db_name = preg_replace('/\/(.*)/', '$1', $url['path']);
	$collection = getenv("MONGOLAB_COLLECTION");
	$mandrill_key = getenv("MANDRILL_KEY");
	$eLearning_prefix = getenv("ELEARNING_PREFIX");
    $client_id = getenv("GOOGLE_OAUTH_ID");
 	$client_secret = getenv("GOOGLE_OAUTH_SECRET");
 	$redirect_uri = getenv("GOOGLE_REDIRECT_URI");
 	$drive_client_id = getenv("ODI_DRIVE_CLIENTID");
 	$drive_client_secret = getenv("ODI_DRIVE_CLIENTSECRET");
 	$drive_token = getenv("ODI_DRIVE_TOKEN");
 	$collection = "elearning";
 	$courses_collection = "courses";
	$instances_collection = "instances";
	$exec_path = "~";
?>
