<?php
	
	$stats_min = 0;

	$stats_cmd = "/usr/bin/wget -O /dev/null -q -t 1 --no-check-certificate https://lms2.learndata.info/api/v1/trained_stats.php?theme=";
	$dashboard_cmd = "/usr/bin/wget -O /dev/null -q -t 1 --no-check-certificate https://lms2.learndata.info/api/v1/data2.php";
	$courses_url = "https://lms2.learndata.info/api/v1/courses.php?theme=";
	$cron_writer = fopen('auto.cron',"w");

	$handle = fopen("clients.csv","r");
	while ($line = fgets($handle)) {
		stats_hourly($line,$stats_cmd,$cron_writer);
		dashboard_nightly(trim($line),$courses_url,$dashboard_cmd,$cron_writer);
	}

	fclose($handle);
	fclose($cron_writer);

	function dashboard_nightly($client,$courses_url,$cmd,$writer) {
		$data = ""; $content = "";
		$url = $courses_url . $client;
		$content = file_get_contents(trim($url));
		$data = json_decode($content,true);
		$data = $data["data"];
		for($i=0;$i<count($data);$i++) {
			if ($data[$i]["format"] == "course") {
				continue;
			}
			$id = false;
			$id = $data[$i]["ID"];
			if (!$id) {
				$id = $data[$i]["id"];
			} 
			if (!$id) {
				$id = $data[$i]["_id"];
			}
			make_nightly_cron($client,$cmd,$id,$writer);
		}
	}

	function make_nightly_cron($client,$cmd,$id,$writer) {
		$hour = rand(0,7);
		$minute = rand(0,59);
		$interval = $minute . " " . $hour . " * * *";
		$full_cmd = $interval . " " . $cmd . "?module=" . $id . "&theme=" . $client;
		fwrite($writer, $full_cmd . "\n");
	}

	function stats_hourly($client,$cmd,$writer) {
		global $stats_min;
		$interval = $stats_min . " * * * *";
		$full_cmd = $interval . " " . $cmd . $client;
		fwrite($writer, $full_cmd);
		$stats_min += 5;
		if ($stats_min > 59) {
			$stats_min = 0;
		}
	}
?>
