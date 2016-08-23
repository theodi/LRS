<?php
	$location = "/dashboard/index.php?module=1";
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('_includes/header.php');
	$module = $_GET["module"];
?>
<form action="" method="get" style="text-align: right; position: relative; bottom: 5em; margin-bottom: -60px;">
    <select name="module">
<?php
	for ($i=1;$i<14;$i++) {
        	echo '<option ';
		if ($module == $i) {
			echo 'selected ';
		}
		echo 'value='.$i.'>Module ' . $i . '</option>';
	}
?>
    </select>
    <input type="submit" value="Go" style="padding: 0.2em 1em; position: relative; bottom: 5px;"/>
</form>
<?php
	$path = "../";
	set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	include('dashboard/board.html');
	include('_includes/footer.html');
?>
