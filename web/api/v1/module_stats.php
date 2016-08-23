<?php
    $access = "public";
//  $location = "/api/view_data.php";
    $path = "../../";
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    include_once('library/functions.php');
    include_once('header.php');

    $count = getNumberOfRecords($collection);

    $courses = getCoursesData();
    $mods = "";

    if ($theme && $theme != "default") {
        $filter = getClientMapping($theme);
        $courses = filterCourses($courses,$filter);
    }
    $tracking = getCourseIdentifiers();
    //$data = getDataFromCollection($collection);
    $collection = "elearning";
    $data = getDataFromCollection($collection);
	foreach ($data as $user) {
		$output = geteLearningCompletion($user,$courses,array());
		$all["active"] = rotate($output["active"],$all["active"],$mods);	
		$all["complete"] = rotate($output["complete"],$all["complete"],$mods);
		$all["in_progress"] = rotate($output["in_progress"],$all["in_progress"],$mods);
	}

	ksort($mods);
	foreach($mods as $key => $null){
		$output = "";
		$output["course_id"] = $key;
		$output["course_name"] = $courses[$key]["title"];
		$output["active"] = $all["active"][$key];
		$output["complete"] = $all["complete"][$key];
		$output["in_progress"] = $all["in_progress"][$key];
		$final[] = $output;
	}
	$out = fopen('php://output', 'w');
	$headings = array("course_id","course_name","active","complete","in_progress");
	fputcsv($out,$headings);
 	for($i=0;$i<count($final);$i++) {
		fputcsv($out,$final[$i]);
 	}
	fclose($out);

function rotate($in_array,$total,$mods) {
	global $mods;
	$done = array();
	for($i=0;$i<count($in_array);$i++) {
		$current_mod = $in_array[$i];
		if (!$done[$current_mod]) {
			$total[$current_mod]++;
			$done[$current_mod] = true;
			$mods[$current_mod] = true;
		}
	}
	return $total;
}

?>
