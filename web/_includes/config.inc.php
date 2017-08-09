<?php
//	$connection_url = "mongodb://admin:OpenData2012@ds029778.mlab.com:29778/elearning-test";
	$connection_url = "mongodb://heroku_0vr2zs59:ctgtfnb5kgrc9gbl0a6etdk9o8@ds055792.mongolab.com:55792/heroku_0vr2zs59";
	$url = parse_url($connection_url);
	//$db_name = 'elearning-test';
	$db_name = 'heroku_0vr2zs59';
	$collection = "elearning";
	$courses_collection = "courses";
	$instances_collection = "instances";
        $mandrill_key = "S20afJPHdCR6p0kjrEw9JA";
	$client_id = "112022861718-3dojeucmk642cd4hgtnojq9r76dpajg1.apps.googleusercontent.com";
 	$client_secret = "J4diuIIPi0fcSgYe3SRWg5Fw";
 	$drive_client_id = "683890023722-4h3trn3d6o3705nq6d9duoo78r546pmj.apps.googleusercontent.com";
 	$drive_client_secret = "GzpYCGE18i2aflsSSYoQyFRW";
 	$drive_token = '{"access_token":"ya29.bwLc9w_V5t2t9eifOO5zPZSsah-ppR0aePGlkDAKZ70uSxFYCdT9wbI_ReCIXI3rS-xc","token_type":"Bearer","expires_in":3600,"refresh_token":"1\/06zDE_OQV6LWXJmrlqbGWwV8rPymGXyjskjpD2uA3k1IgOrJDtdun6zK6XiATCKT","created":1453287197}';
 	$redirect_path = "";
 	$exec_path = "/var/www/";
?>
