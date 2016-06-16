<?php 

	require_once('AudioUtils.php');
	
	$isApi = (intval($_GET['api']) == 1);
	$new = AudioUtils::pitch($_GET['file'], $_GET['id'], $isApi);
	
	if ($new === false) {
		echo 'What the heck.';
	} else {
		header('Content-Type: audio/mpeg');
		
		if ($isApi) {
			echo $new;
		} else {
			readfile($new);	
		}
	}

?>
