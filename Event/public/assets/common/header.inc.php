<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content_Type" content="text/html;charset=utf-8">
	<title><?php echo $page_title; ?></title>
	<?php foreach ($css_files as $css) : ?>
	<link rel='stylesheet' type="text/css" media="screen,projection" href="assets/css/<?php echo $css; ?>" />
	<?php endforeach; ?>
	<script type="text/javascript" src="assets/js/jquery-1.8.3.js"></script>
	<script type="text/javascript" src="assets/js/init.js"></script>
</head>