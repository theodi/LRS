<?php
$access = "viewer";
$location = "/dashboard/trained.php";
$path = "../";
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
include('_includes/header.php');

function getCachedCompletionStats($theme,$type,$date,$collection) {
   global $connection_url, $db_name;
   try {
    $m = new MongoClient($connection_url);
    $col = $m->selectDB($db_name)->selectCollection($collection);
    $query = array('theme' => new MongoRegex('/^' .  $theme . '$/i'), 'type' => $type, 'date' => $date);
    $res = $col->find($query);
    $ret = "";
    foreach ($res as $doc) {
        $ret =  $doc["modules"]["completeCount"];
    }
    $m->close();
    return $ret;
   } catch ( Exception $e ) { syslog(LOG_ERR,'Error: ' . $e->getMessage()); }
}

$date = date("Y-m-d");
$type = "trained";
$complete = getCachedCompletionStats($theme,$type,$date,"statisticsCache");
ksort($complete);

?>
<style>
	body {line-height: 1;}
        .box {font-family: Arial, sans-serif;background-color: #F1F1F1;border:0;width:340px;webkit-box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.3);margin: 0 auto 25px;text-align:center;padding:10px 0px; display: inline-block; height: 14em; font-size: 15px; vertical-align: top;}
        .box img{padding: 10px 0px;}
        .box a{color: #427fed;cursor: pointer;text-decoration: none;}
        .number {font-size: 8em;}
        .sub {display: block; font-size: 2em;}
        .subsub {display: block; font-size: 1em;}
</style>
<link href="css/nv.d3.css" rel="stylesheet">
<script src="js/d3.v3.js"></script>
<script src="js/nv.d3.js"></script>
<div align="center">
<div id="trained_box" class="box">
  <div>
        <span id="trained" class="number">WAIT</span>
        <span class="sub">people trained</span>
        <span class="subsub">(have completed at least 1 face to face course and/or eLearning module)</span>
  </div>
</div>
&nbsp;
&nbsp;
&nbsp;
&nbsp;
<div id="completions_box" class="box">
  <div>
        <span id="completions" class="number">WAIT</span>
        <span class="sub">Module completions</span>
        <span class="subsub">(1 person can complete multiple modules - eLearning ONLY)</span>
  </div>
</div>
&nbsp;
&nbsp;
&nbsp;
&nbsp;
<div id="active_box" class="box">
  <div>
        <span id="active" class="number">WAIT</span>
        <span class="sub">Active users</span>
        <span class="subsub">(People who have not completed any eLearning modules but have engaged for over 5 minutes and completed more than 50% of at least one eLearning module)</span>
  </div>
</div>
</div>
<div id="chart">
<svg style="height:500px"></svg>
</div>
<script src="js/stack.js"></script>
<h2>Completed modules breakdown</h2>
<p>The table below shows how many people have completed at least X modules. All those who have completed 2 will have completed 1 but are not included in this count. Everyone in this table has completed at least 1 module. It is not possible to tell from this data which modules have been completed, just the count.</p>
<div align="center">
<table style="width: 400px; text-align: center; line-height:20px;">
<tr><th>Number of modules completed</th><th>Number of people</th></tr>
<?php
  foreach ($complete as $key => $value) {
    $max = $key;
  }
	for($i=1;$i<=$max;$i++) {
    if (!is_numeric($complete[$i])) {
      $complete[$i] = 0;
    }
		echo '<tr><td>' . $i . '</td><td>' . $complete[$i] . '</td></tr>';
	}
?>
</table>
</div>
<script>
$( document ).ready(function() {
	if (!$('#active').text()) { $('#active_box').hide(); }	
	if (!$('#trained').text()) { $('#trained_box').hide(); }	
	if (!$('#completions').text()) { $('#completions_box').hide(); }	
});
</script>
<?php
	include('_includes/footer.html');
?>
