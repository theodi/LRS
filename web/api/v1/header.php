<?php
error_reporting(E_ALL & ~E_NOTICE);
session_start();
require_once '../../src/Google/autoload.php';
require_once '../../src/Google/Client.php';
require_once '../../src/Google/Service/Oauth2.php';
if ($_SERVER["HTTP_HOST"] == "localhost") {
  include_once('../../_includes/config-local.inc.php');
} else {
  include_once('../../_includes/config.inc.php');
}
$client = new Google_Client();
$client->setApplicationName("PHP Google OAuth Login Example");
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
//Send Client Request
$objOAuthService = new Google_Service_Oauth2($client);

//Set Access Token to make Request

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
}

$theme = "default";
$host = $_SERVER["HTTP_HOST"];

if (getTheme($host)) {
  $theme = getTheme($host);
} elseif ($_GET["theme"]) {
  $theme = $_GET["theme"];
} elseif ($_SESSION["theme"]) {
  $theme = $_SESSION["theme"];
}

$_SESSION["theme"] = $theme;

if ($client->getAccessToken()) {
  try {
	$userData = $objOAuthService->userinfo->get();
  } catch (Exception $e) {
	header('Location: ' . $redirect_path . '/?logout&theme=' . $theme);
	exit(1);
  }
  $email = $userData["email"];
  
  $userData["externalAccess"] = getExternalAccess($email,$theme);
  $suffix = substr($email,strrpos($email,"@")+1,strlen($email));
  if ($suffix == "theodi.org") {
	 $userData["isAdmin"] = true;
	 $_SESSION["isAdmin"] = true;
	 $userData["isViewer"] = true;
   $_SESSION["isViewer"] = true;
  }
  if ($userData["externalAccess"]["theme"] == $theme) {
  	$userData["isViewer"] = true;
  	$_SESSION["isViewer"] = true;
  }
  $_SESSION['userData'] = $userData;
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  $authUrl = $client->createAuthUrl();
}

if ($access == "public") {
} elseif ($access == "viewer" && $userData["isViewer"]) {
} elseif ($access == "admin" && $userData["isAdmin"]) {
} else {
	header('Location: /401.php');
	exit();
}
?>
