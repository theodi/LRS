<?php
error_reporting(E_ALL & ~E_NOTICE);

if($_SERVER['HTTP_X_FORWARDED_PROTO'] != "https" && $_SERVER["HTTP_HOST"] != "localhost")  {
    $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $redirect);
    exit();
}

session_start();
if ($_SERVER["HTTP_HOST"] == "localhost") {
	include_once('_includes/config-local.inc.php');
} else {
	include_once('_includes/config.inc.php');
}
//Google API PHP Library includes
require_once 'src/Google/autoload.php';
require_once 'src/Google/Client.php';
require_once 'src/Google/Service/Oauth2.php';
include_once('library/functions.php');
include_once('library/navigation.php');

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

$redirect_uri = "https://" . $_SERVER["HTTP_HOST"] . $redirect_path . "/index.php";
if ($_SERVER["HTTP_HOST"] == "localhost") {
	$redirect_uri = "http://" . $_SERVER["HTTP_HOST"] . $redirect_path . "/index.php";
}

//Create Client Request to access Google API
$client = new Google_Client();
$client->setApplicationName("PHP Google OAuth Login Example");
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/userinfo.email");
//Send Client Request
$objOAuthService = new Google_Service_Oauth2($client);

//Logout
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
  unset($_SESSION['userData']);
  unset($userData);
  $client->revokeToken();
  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL)); //redirect user back to page
}

//Authenticate code from Google OAuth Flow
//Add Access Token to Session
try {
	if (isset($_GET['code'])) {
  		$client->authenticate($_GET['code']);
	  	$_SESSION['access_token'] = $client->getAccessToken();
	  	header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
	}
} catch (Google_Auth_Exception $e) {
    unset($_SESSION['token']); //unset the session token
    print_r($e);
    echo "Token now invalid, please revalidate. <br>";
}

//Set Access Token to make Request
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
}

//Get User Data from Google Plus
//If New, Insert to Database
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


$site_title = "ODI Learning Management System";
$pages = getMenuPages($redirect_path);
$pages = getUserStatusLink($redirect_path,$pages);

for($i=0;$i<count($pages);$i++) {
	if ($pages[$i]['url'] == $location) {
		$current = $pages[$i];
	}
}
if (!$userData && $current['loggedIn']) {
	header('Location: ' . $redirect_path . '/401.php?loggedIn=true');
	exit();
}
if (($current['viewer'] && !$userData["isViewer"]) && ($current['admin'] && !$userData["isAdmin"])) {
	header('Location: ' . $redirect_path . '/401.php?viewer=true');
	exit();
} 

if ($access == "public") {
} elseif ($access == "viewer" && $userData["isViewer"]) {
} elseif ($access == "admin" && $userData["isAdmin"]) {
} else {
	if (file_exists('401.php')) {
		header('Location: 401.php');
	} else {
		header('Location: ../401.php');
	}
	exit();
}

?>
<!DOCTYPE html>
<html prefix="dct: http://purl.org/dc/terms/
              rdf: http://www.w3.org/1999/02/22-rdf-syntax-ns#
              dcat: http://www.w3.org/ns/dcat#
              odrs: http://schema.theodi.org/odrs#">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $site_title ?></title>
<!--<link href="http://assets.theodi.org/css/odi-bootstrap-crimson.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-green.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-orange.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-pomegranate.css" rel="stylesheet">
<link href="http://assets.theodi.org/css/odi-bootstrap-red.css" rel="stylesheet">-->
<link href="/css/odi-bootstrap.css" rel="stylesheet">
<link href="/css/style.css" rel="stylesheet">
<link href="/css/nav.css" rel="stylesheet">
<link rel="shortcut icon" href="/images/odifavicon32.ico">
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="/js/nav.js"></script>
<?php
	require_once('library/lessify.inc.php');
	$css = file_get_contents("https://raw.githubusercontent.com/theodi/ODI-eLearning/master/src/theme/$theme/less/variables.less");
	$css .= "@default-width:100%;\n";
	$css .= @file_get_contents("css/variables.less");
	$css .= @file_get_contents("../css/variables.less");
	$css .= file_get_contents("https://raw.githubusercontent.com/theodi/ODI-eLearning/master/src/theme/$theme/less/navigation.less");
	$css = str_replace("assets/","https://raw.githubusercontent.com/theodi/ODI-eLearning/master/src/theme/$theme/assets/",$css);
	$less = new lessc;
	echo '<style>' . "\n";
	echo $less->compile($css);
	echo '</style>' . "\n";
?>
</head>
<body>
<div class="navigation" style="margin: 0 -20px; width: initial;">
<div class="navigation-inner clearfix">
<a href="javascript:void(0)" class="icon">
  <div class="hamburger">
    <div class="menui top-menu"></div>
    <div class="menui mid-menu"></div>
    <div class="menui bottom-menu"></div>
  </div>
</a>
</div>
</div>
<div class="mobilenav">
  <?php 
     echo render_menu();
  ?>
</div>
	<div class='navbar navbar-static-top' id='mainnav'>
		<div class='container'>
			<div class='navbar-inner'>
				<ul class='nav pull-right'>
				<?php 
					echo render_menu();
				?>
				</ul>
			</div>
		</div>
	</div>
<div class='whiteout'>
	<header>
		<div class='container'>
			<h1><?php echo $current['long_title']; ?></h1>
		</div>
	</header>

<div class='container main-default' id='main'>

<?php 

function render_menu_item($current,$page) {
	$ret = "";
	if ($current == $page["url"]) {
		$ret .= '<li class="selected">';
	} else {
		$ret .= "<li>";
	}
	$ret .= '<a href="'.$page["url"].'">'.$page["title"].'</a>';
	$ret .= '</li>';
	return $ret;
}

function render_menu() {
  global $pages,$current,$userData;

  $ret = "";
  for ($i=0;$i<count($pages);$i++) {
	$page = $pages[$i];
	if (!$page["admin"] && !$page["loggedIn"]) {
		echo render_menu_item($current,$page);
	} elseif ($page["viewer"] && $userData["isViewer"]) {
		echo render_menu_item($current,$page);
	} elseif ($page["admin"] && $userData["isAdmin"]) {
		echo render_menu_item($current,$page);
	} elseif ($page["loggedIn"] && !$page["admin"] && $userData) {
		echo render_menu_item($current,$page);
	}
  }
  return $ret;
}

function getUserStatusLink($redirect_path,$pages) {
	global $authUrl;
	if ($authUrl) {
		$page['url'] = $authUrl;
		$page['title'] = 'Login';
		$pages[] = $page;
	} else {
		$page['url'] = $redirect_path . '/?logout';
		$page['title'] = 'Logout';
		$pages[] = $page;
	}
	return $pages;
}

?>
