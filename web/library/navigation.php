<?php

function getMenuPages($redirect_path) {
	$pages = [];
	
	$page['url'] = $redirect_path . '/';
	$page['title'] = 'Home';
	$page['long_title'] = "Learning Management System";
	$pages[] = $page;
	
	$page['url'] = $redirect_path . '/courses/index.php';
	$page['title'] = 'Courses';
	$page['long_title'] = "Course list";
	$pages[] = $page;
	
	$page['url'] = $redirect_path . '/dashboard/trained.php';
	$page['title'] = 'Statistics';
	$page['long_title'] = "Statistics";
	$page['admin'] = true;
	$page['viewer'] = true;
	$pages[] = $page;

	$page['url'] = $redirect_path . '/profile/index.php';
	$page['title'] = 'Profile';
	$page["long_title"] = "Your profile";
	$page['loggedIn'] = true;
	$page['admin'] = false;
	$pages[] = $page;
	$page['url'] = $redirect_path . '/learners/index.php';
	
	$page['title'] = 'Learners';
	$page["long_title"] = "Learner profiles";
	$page['admin'] = true;
	$page['viewer'] = true;
	$pages[] = $page;
	$page['url'] = $redirect_path . '/admin/index.php';

	$page['title'] = 'Admin';
	$page["long_title"] = "LMS Administration";
	$page['admin'] = true;
	$page['viewer'] = false;
	$pages[] = $page;

	return $pages;
}

?>