<?php
	
	ini_set('memory_limit', '128M');
	require_once('../../../classes/Chatbot.php');

	$fallback = dirname(__FILE__) . '/bear.jpg';

	if (empty($_GET['file'])) {
		$_GET['file'] = $fallback;
	}

	$foo = new Chatbot();
	$result = $foo->overlay(dirname(__FILE__) . '/overlay.png', $_GET['file']);

	if ($result === false) {
	    header('Content-Type: image/jpeg');
	    $img = imagecreatefromjpeg($fallback);
	    imagejpeg($img);
	}

?>
