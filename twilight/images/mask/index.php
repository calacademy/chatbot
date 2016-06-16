<?php
	
	ini_set('memory_limit', '128M');
	require_once('../../../classes/Chatbot.php');

	$foo = new Chatbot();
	$result = $foo->overlay(dirname(__FILE__) . '/overlay.png', $_GET['file']);

	if ($result === false) {
		echo 'no face found';
	}

?>
