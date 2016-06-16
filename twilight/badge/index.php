<!DOCTYPE html>
<html lang="en">
	<?php 

		require_once('../../classes/Chatbot.php');
		$foo = new Chatbot();
		$user = $foo->getUserData($_GET['id']);

		$title = 'Twilight Expedition Completed!';
		$description = $user->first_name . ' earned a sparkle ball. You can too!';
		$img = 'legacy.calacademy.org/chatbot/twilight/images/spidy.jpg';

	?>
	<head>
		<meta charset="utf-8" />
		<title><?php echo $title; ?></title>
		<meta property="og:title" content="<?php echo $title; ?>" />
		<meta name="description" content="<?php echo $description; ?>" />
		<meta property="og:description" content="<?php echo $description; ?>" />
		<link rel="image_src" href="http://<?php echo $img; ?>" />
		<meta property="og:image" content="http://<?php echo $img; ?>" />
		<meta property="og:image:secure_url" content="https://<?php echo $img; ?>" />
	</head>
	<body>
		<!--
		<img src="http://<?php echo $img; ?>" />
		<h1><?php echo $title; ?></h1>
		<p><?php echo $description; ?></p>
		//-->

		<script>

			window.location.href = 'http://m.me/calacademy';

		</script>
	</body>
</html>
