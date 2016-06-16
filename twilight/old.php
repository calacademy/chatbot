<?php
	
	function banana ($name) {
		if ($name === false) return 'unknown banana';
		
		$name = trim(strtolower($name->first_name));
		$vowels = 'aeiou';
		
		// find first instance of a vowel
		$i = 0;
		
		while ($i < strlen($name)) {
			if (strpos($vowels, $name{$i}) !== false) {
				$firstVowel = $i;
				break;
			}
			
			$i++;
		}
		
		$shortname = substr($name, $firstVowel);
		return ucwords($name) . ' ' . ucwords($name) . ' Bo B' . $shortname . ' Banana Fana Fo F' . $shortname;
	}
	
	function isFaceShowingLikelihood ($prop, $faceAnnotation) {
		$likely = array(
			'POSSIBLE',
			'LIKELY',
			'VERY_LIKELY'
		);

		if (is_null($faceAnnotation->{$prop . 'Likelihood'})) {
			return false;
		}

		return in_array($faceAnnotation->{$prop . 'Likelihood'}, $likely);
	}

	function getEnglishInteger ($int) {
		$english = array(
			'zero',
			'one',
			'two',
			'three',
			'four',
			'five',
			'six',
			'seven',
			'eight',
			'nine',
			'ten',
			'eleven',
			'twelve',
			'thirteen',
			'fourteen',
			'fifteen',
			'sixteen',
			'seventeen',
			'eighteen',
			'nineteen'
		);

		if ($int >= count($english) || $int < 0) return $int;
		return $english[$int];
	}

	function getImageAnalysis ($imgUrl, $foo) {
		$str = 'I’m having trouble analyzing this image 😬';

		$imgData = $foo->getImageData($imgUrl);
		if ($imgData === false) return $str;

		$imgData = $imgData->responses[0];
		$labels = array();

		foreach ($imgData->labelAnnotations as $label) {
			$labels[] = $label->description;
		}

		if (!is_array($imgData->faceAnnotations)) {
			$str = 'Probably not a human face. But it looks like ' . implode(', ', $labels) . '.';
		} else {
			$faces = array();
			$i = 0;

			foreach ($imgData->faceAnnotations as $face) {
				$i++;
				$faceDesc = array();

				if (isFaceShowingLikelihood('sorrow', $face)) {
					$faceDesc[] = 'Why so glum, face #' . $i . '?';
				}
				if (isFaceShowingLikelihood('joy', $face)) {
					$faceDesc[] = 'You look stoked, face #' . $i . '!';
				}
				if (isFaceShowingLikelihood('anger', $face)) {
					$faceDesc[] = 'Don’t be mad, face #' . $i . '.';
				}
				if (isFaceShowingLikelihood('surprise', $face)) {
					$faceDesc[] = 'Surprise, face #' . $i . '!';
				}
				if (isFaceShowingLikelihood('headwear', $face)) {
					$faceDesc[] = 'Nice headwear, face #' . $i . '.';
				}

				if (!empty($faceDesc)) {
					$faces[] = trim(implode(' ', $faceDesc));	
				}
			}

			if (count($imgData->faceAnnotations) == 1) {
				$str = 'Look at that punim. I found one face. ';
			} else {
				$str = 'Look at those punim. I found ';
				$str .= getEnglishInteger(count($imgData->faceAnnotations)) . ' faces. ';	
			}
			
			$str .= implode(' ', $faces);
			
			$str = trim($str);
			$str .= ' Also looks like ' . implode(', ', $labels) . '.';
		}

		if (strlen($str) > 320) {
			$str = substr($str, 0, 319) . '…';
		}

		$landmark = $foo->getMapData($imgData);

		return array(
			'string' => $str,
			'landmark' => $landmark,
			'containsFaces' => is_array($imgData->faceAnnotations) 
		);
	}

	function respondToMessage ($msg, $foo) {
		$replyContent = '';
		$senderId = $msg->sender->id;
		$content = $msg->message->text;
		$postback = $msg->postback->payload;
		$user = false;

		if (!empty($content)
			|| !empty($postback)
			|| !empty($msg->message->attachments)) {
			$user = $foo->getUserData($senderId);
		}

		// process any attachments
		foreach ($msg->message->attachments as $attachment) {
			$mediaUrl = $attachment->payload->url;

			switch ($attachment->type) {
				case 'audio':
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'button',
								'text' => 'Way to be, Alvin.',
								'buttons' => array(
									array(
										'type' => 'web_url',
										'url' => 'https://legacy.calacademy.org/chatbot/twilight/helium/?id=' . urlencode($senderId) . '&file=' . urlencode($mediaUrl),
										'title' => 'Listen 🔈'
									)
								)
							)
						)
					);
					
					$response = $foo->send($senderId, $replyContent);
					break;
				case 'image':
					// $replyContent = array(
					// 	'attachment' => array(
					// 		'type' => 'template',
					// 		'payload' => array(
					// 			'template_type' => 'generic',
					// 			'elements' => array(
					// 				array(
					// 					'title' => 'Nice ' . $attachment->type . ', ' . $user->first_name,
					// 					'subtitle' => 'This image can be analyzed / manipulated.',
					// 					'image_url' => $mediaUrl
					// 				)
					// 			)
					// 		)
					// 	)
					// );

					// $response = $foo->send($senderId, $replyContent);
					
					$analysis = getImageAnalysis($mediaUrl, $foo);
					
					if (is_array($analysis['landmark'])) {
						// first send a description
						// $response = $foo->send($senderId, $analysis['string']);

						// then send a map bubble
						$landmark = $analysis['landmark'];

						$replyContent = array(
							'attachment' => array(
								'type' => 'template',
								'payload' => array(
									'template_type' => 'generic',
									'elements' => array(
										array(
											'title' => $landmark['description'],
											'subtitle' => 'I’m about ' . ($landmark['score'] * 100) . '% sure your image contains this landmark.',
											'image_url' => $landmark['imgUrl'],
											'buttons' => array(
												array(
													'type' => 'postback',
													'title' => 'Right',
													'payload' => 'LANDMARK_CORRECT'
												),
												array(
													'type' => 'postback',
													'title' => 'Wrong',
													'payload' => 'LANDMARK_INCORRECT'
												),
												array(
													'type' => 'web_url',
													'url' => $landmark['link'],
													'title' => 'View Map'
												)
											)
										)
									)
								)
							)
						);

						$response = $foo->send($senderId, $replyContent);
					} else if ($analysis['containsFaces']) {
						// faces, ask for mask
						$payload = array(
							'action' => 'SCUBA_FACE',
							'image_url' => $mediaUrl
						);

						$replyContent = array(
							'attachment' => array(
								'type' => 'template',
								'payload' => array(
									'template_type' => 'button',
									'text' => $analysis['string'],
									'buttons' => array(
										array(
											'type' => 'postback',
											'title' => 'Scuba Face',
											'payload' => json_encode($payload)
										)
									)
								)
							)
						);
						
						$response = $foo->send($senderId, $replyContent);
					} else {
						// no landmark or faces, just send a description
						$response = $foo->send($senderId, $analysis['string']);
					}

					break;
			}
		}
		
		// already sent some media, quit
		if (!empty($msg->message->attachments)) return;
		
		if (!empty($content)) {
			// process a message
			$content = trim(strtolower($content));
			
			switch ($content) {
				case 'just buttons':
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'button',
								'text' => 'Just some boring ol’ buttons.',
								'buttons' => array(
									array(
										'type' => 'web_url',
										'url' => 'http://www.calacademy.org',
										'title' => 'Visit our website'
									),
									array(
										'type' => 'postback',
										'title' => 'banana',
										'payload' => 'BANANA'
									)
								)
							)
						)
					);
					break;
				case 'me':
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'generic',
								'elements' => array(
									array(
										'title' => $user->first_name . ' ' . $user->last_name,
										'subtitle' => 'locale: ' . $user->locale . ', timezone: ' . $user->timezone . ', gender: ' . $user->gender,
										'image_url' => $user->profile_pic
									)
								)
							)
						)
					);
					break;
				case 'badge':
					$shareUrl = urlencode('http://legacy.calacademy.org/chatbot/twilight/badge/?id=' . $senderId);
					
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'generic',
								'elements' => array(
									array(
										'title' => 'Twilight Expedition Completed!',
										'subtitle' => 'You’ve earned a sparkle ball. Invite your friends by sharing to your timeline.',
										'image_url' => 'http://legacy.calacademy.org/chatbot/twilight/images/spidy.jpg',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.facebook.com/sharer/sharer.php?u=' . $shareUrl,
												'title' => 'Share'
											)
										)
									)
								)
							)
						)
					);
					break;
				case 'frog':
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'generic',
								'elements' => array(
									array(
										'title' => 'Hi. I am a frog.',
										'subtitle' => 'ribbit ribbit ribbit',
										'image_url' => 'http://www.calacademy.org/sites/all/themes/calacademy_zen/images/gifs/frog.gif',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.calacademy.org',
												'title' => 'Visit our website'
											),
											array(
												'type' => 'postback',
												'title' => 'banana',
												'payload' => 'BANANA'
											)
										)
									)
								)
							)
						)
					);
					break;
				case 'jelly':				
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'generic',
								'elements' => array(
									array(
										'title' => 'Hi. I am a jellyfish.',
										'subtitle' => 'slup slup slup squish',
										'image_url' => 'http://www.calacademy.org/sites/all/themes/calacademy_zen/images/big-blue-jelly.jpg',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.calacademy.org',
												'title' => 'Visit our website'
											),
											array(
												'type' => 'postback',
												'title' => 'banana',
												'payload' => 'BANANA'
											)
										)
									)
								)
							)
						)
					);
					break;
				case 'multi':
					$replyContent = array(
						'attachment' => array(
							'type' => 'template',
							'payload' => array(
								'template_type' => 'generic',
								'elements' => array(
									array(
										'title' => 'Hi. I am a frog.',
										'subtitle' => 'ribbit ribbit ribbit',
										'image_url' => 'http://www.calacademy.org/sites/all/themes/calacademy_zen/images/gifs/frog.gif',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.calacademy.org',
												'title' => 'Visit our website'
											),
											array(
												'type' => 'postback',
												'title' => 'banana',
												'payload' => 'BANANA'
											)
										)
									),
									array(
										'title' => 'Hi. I am a jellyfish.',
										'subtitle' => 'squish squish squish squonk.',
										'image_url' => 'http://www.calacademy.org/sites/all/themes/calacademy_zen/images/big-blue-jelly.jpg',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.calacademy.org',
												'title' => 'Visit our website'
											),
											array(
												'type' => 'postback',
												'title' => 'banana',
												'payload' => 'BANANA'
											)
										)
									),
									array(
										'title' => 'Hi. I am a fancy fishy.',
										'subtitle' => 'blub blub blub',
										'image_url' => 'http://www.calacademy.org/sites/all/themes/calacademy_zen/images/bg-fancy-fish.jpg',
										'buttons' => array(
											array(
												'type' => 'web_url',
												'url' => 'http://www.instagram.com/calacademy',
												'title' => 'Visit us on Instagram'
											),
											array(
												'type' => 'postback',
												'title' => 'banana',
												'payload' => 'BANANA'
											),
											array(
												'type' => 'postback',
												'title' => 'what the',
												'payload' => 'WHAT_THE'
											)
										)
									)
								)
							)
						)
					);
					break;
				default:
					if ($user === false) {
						$replyContent = 'repeat after me: ' . $content;
					} else {
						$replyContent = 'repeat after me, ' . $user->first_name . ': ' . $content;
					}
					
			}
		}
		
		if (!empty($postback)) {
			// process a postback
			$json = json_decode($postback);

			if (!is_null($json)) {
				$postback = $json->action;
			}

			switch ($postback) {
				case 'SCUBA_FACE':
					$replyContent = array(
						'attachment' => array(
							'type' => 'image',
							'payload' => array(
								'url' => 'http://legacy.calacademy.org/chatbot/twilight/images/mask/?file=' . urlencode($json->image_url)
							)
						)
					);
					break;
				case 'BANANA':
					$replyContent = banana($user);
					break;
				case 'WHAT_THE':
					$replyContent = 'heck!';
					break;
				case 'LANDMARK_CORRECT':
					$replyContent = '🙌';
					break;
				case 'LANDMARK_INCORRECT':
					$replyContent = 'Awww, nuts 🤔';
					break;
				default:
					$replyContent = 'unknown postback';
			}
		}
		
		if (!empty($replyContent)) {
			// send a reply
			$response = $foo->send($senderId, $replyContent);
		}
	}
	
	require_once('../classes/Chatbot.php');

	$foo = new Chatbot();
	$msg = $foo->getMessage();

	foreach ($msg->entry as $entry) {
		foreach ($entry->messaging as $messaging) {
			respondToMessage($messaging, $foo);
		}
	}

	// require_once('../private/credentials.php');

	// if ($_REQUEST['hub_verify_token'] == $credentials['facebook']['chatbot_token']) {
	// 	echo $_REQUEST['hub_challenge'];	
	// } else {
	// 	echo 'fail!';
	// }

?>
