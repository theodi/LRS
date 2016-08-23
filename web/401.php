<?php
	$location = "404.php";
	header("HTTP/1.0 401 Unauthorised");
	include('_includes/header.php');
?>
<style>
	.box {font-family: Arial, sans-serif;background-color: #F1F1F1;border:0;width:340px;webkit-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);margin: 0 auto 25px;text-align:center;padding:10px 0px;}
	.box img{padding: 10px 0px;}
	.box a{color: #427fed;cursor: pointer;text-decoration: none;}
	.heading {text-align:center;padding:10px;font-family: 'Open Sans', arial;color: #555;font-size: 18px;font-weight: 400;}
	.circle-image{width:100px;height:100px;-webkit-border-radius: 50%;border-radius: 50%;}
	.welcome{font-size: 16px;font-weight: bold;text-align: center;margin: 10px 0 0;min-height: 1em;}
	.oauthemail{font-size: 14px;}
	.logout{font-size: 13px;text-align: right;padding: 5px;margin: 20px 5px 0px 5px;border-top: #CCCCCC 1px solid;}
	.logout a{color:#8E009C;}
</style>

<div class="heading">401: Unauthorised</div>
<?php
	include('_includes/footer.html');
?>
