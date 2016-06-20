<?php 
	
	$includePath = dirname(__FILE__) . '/../../include/php/';
	
	$includes = array(
		'DatabaseUtil.php',
		'StringUtil.php',
		'phpmailer/class.phpmailer.php'
	);
	
	foreach ($includes as $include) {
		$file = $includePath . $include;

		if (file_exists($file)) {
			include_once($file);
		}
	}

	require_once('ImageUtils.php');
	require_once('MapUtils.php');

	class Chatbot {
		protected $_devEmail = 'grotter@gmail.com';
		protected $_devId = '1053133961425776';
		protected $_credentials;
		protected $_db = false;
		protected $_steps;
		protected $_exitStep;
		protected $_userVariables = array();

		public function __construct ($exitStep, $steps) {
			require(dirname(__FILE__) . '/../private/credentials.php');
			$this->_credentials = $credentials;
			$this->_exitStep = $exitStep;
			$this->_steps = $steps;

			foreach ($this->_steps as $step) {
				if (isset($step['target_variable'])) {
					if (is_array($step['target_variable'])) {
						$this->_userVariables = array_merge($this->_userVariables, $step['target_variable']);
					} else {
						$this->_userVariables[] = $step['target_variable'];	
					}
				}
			}

			// database init
			$db = new DatabaseUtil('chatbot');
			$this->_db = $db->getConnection();

			$this->_processMessages();
		}

		public function debug ($msg) {
			if (!class_exists('PHPMailer')) {
				error_log(var_export($msg, true));
				return;
			}

			$mail = new PHPMailer();
			$mail->From = 'legacy@calacademy.org';
			$mail->FromName = 'California Academy of Sciences';
			$mail->Subject = 'Twilight Expedition';
			$mail->AddAddress($this->_devEmail);
			
			if ($msg === false) {
				$mail->Body = 'system error';
			} else {
				$mail->Body = var_export($msg, true);
			}
			
			$mail->Send();
		}
		
		protected function _getDBResource ($query) {
			$resource = mysql_query($query, $this->_db);

			if (!$resource) {
				$this->debug(mysql_error());
			} else {
				return $resource;
			}
		}

		public function getFacebookUserData ($id) {
			$get = array(
				'access_token' => $this->_credentials['facebook']['access_token'],
				'fields' => 'first_name,last_name,profile_pic,locale,timezone,gender'
			);
			
			$ch = curl_init('https://graph.facebook.com/v2.6/' . $id . '?' . http_build_query($get));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			
			$obj = json_decode($result);
			if (is_null($obj)) return false;
			if (empty($obj->first_name)) return false;
			
			return $obj;
		}
		
		public function getMessage () {
			$json = file_get_contents('php://input');
			if ($json === false) return false;

			$obj = json_decode($json);
			if (is_null($obj)) return false;

			return $obj;
		}

		public function send ($id, $data) {
			$url = 'https://graph.facebook.com/v2.6/me/messages';
			
			$url .= '?' . http_build_query(array(
				'access_token' => $this->_credentials['facebook']['access_token']
			));

			$ch = curl_init($url);
			
			if (is_string($data)) {
				// simple text message
				$data = array(
					'text' => $data
				);
			}
			
			$payload = json_encode(array(
				'recipient' => array(
					'id' => $id
				),
				'message' => $data
			));

			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);

			return $result;
		}

		protected function _getBase64 ($str) {
			if (!base64_decode($str, true)) {
				$img = file_get_contents($str);
				if ($img === false) return false;
				return base64_encode($img);
			}

			return $str;
		}

		public function getImageData ($str) {
			$imgData = $this->_getBase64($str);
			if (!$imgData) return false;

			$payload = json_encode(array(
				'requests' => array(
					array(
						'image' => array(
							'content' => $imgData
						),
						'features' => array(
							array(
								'type' => 'LABEL_DETECTION'
							),
							array(
								'type' => 'FACE_DETECTION',
								'maxResults' => 100
							),
							array(
								'type' => 'LANDMARK_DETECTION'
							)
						)
					)
				)
			));

			$url = 'https://vision.googleapis.com/v1/images:annotate';
			
			$url .= '?' . http_build_query(array(
				'key' => $this->_credentials['google']['key']
			));

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    	'Content-Type: application/json',
			    	'Content-Length: ' . strlen($payload)
			    )
			);

			$result = curl_exec($ch);
			curl_close($ch);

			$obj = json_decode($result);
			if (is_null($obj)) return false;
			if (empty($obj->responses)) return false;
			
			return $obj;
		}

		public function getMapData ($imgData) {
			if (!is_array($imgData->landmarkAnnotations)) {
				return false;
			}

			$landmark = $imgData->landmarkAnnotations[0];
			$loc = $landmark->locations[0]->latLng;
			$latlng = $loc->latitude . ',' . $loc->longitude;

			$mapLinks = MapUtils::getMapLinks($latlng, $this->_credentials);

			return array(
				'score' => $landmark->score,
				'description' => $landmark->description,
				'link' => $mapLinks['link'],
				'imgUrl' => $mapLinks['imgUrl']
			);
		}

		public function overlay ($overlayFilepath, $originalFilepath) {
			$imgData = $this->getImageData($originalFilepath);
			if (!$imgData) return false;

			$imgData = $imgData->responses[0];
			if (!is_array($imgData->faceAnnotations)) return false;

            $originalInfo = getimagesize($originalFilepath);

            switch ($originalInfo['mime']) {
            	case 'image/gif':
					$original = imagecreatefromgif($originalFilepath);
					break;
				case 'image/jpeg':
					$original = imagecreatefromjpeg($originalFilepath);
					break;
				case 'image/png':
					$original = imagecreatefrompng($originalFilepath);
					break;
            	default:
            		return false;
            }

            $overlay = imagecreatefrompng($overlayFilepath);

            if ($overlay && $original) {
                // iterate each face
                foreach ($imgData->faceAnnotations as $face) {
                	
                	// get eye coordinates
                	$eyes = array();

                	foreach ($face->landmarks as $landmark) {
                		switch ($landmark->type) {
                			case 'LEFT_EYE':
                				$eyes['left'] = $landmark->position;
                				break;
                			case 'RIGHT_EYE':
                				$eyes['right'] = $landmark->position;
                				break;
                			case 'MIDPOINT_BETWEEN_EYES':
                				$eyes['midpoint'] = $landmark->position;
                				break;
                		}
                	}

                	// check if we have all the necessary landmarks
                	if (count($eyes) == 3) {
                		$eyes['bounds'] = $face->fdBoundingPoly->vertices;
                		
                		// do some calculations
                		$faceWidth = ImageUtils::getDistanceBetweenVertices($eyes['bounds'][0], $eyes['bounds'][1]);
                		$resizePer = $faceWidth / imagesx($overlay);
                		$resizePer += .03;

                		// resize
                		$overlayWidth = imagesx($overlay) * $resizePer;
                		$overlayHeight = imagesy($overlay) * $resizePer;
                		$resizedOverlay = ImageUtils::getResizedPng($overlayWidth, $overlayHeight, $overlay);
                		
                		// rotate
                		$deg = ImageUtils::getTiltAngle($eyes) * -1;
                		$alpha = imageColorAllocateAlpha($resizedOverlay, 0, 0, 0, 127);
                		$resizedOverlay = imagerotate($resizedOverlay, $deg, $alpha);
                		$overlayDimensions = ImageUtils::getNewDimensions($overlayWidth, $overlayHeight, $deg);

                		// place
                		$w = $overlayDimensions['width'];
                		$h = $overlayDimensions['height'];
                		$xCoord = $eyes['midpoint']->x - ($w / 2);
                		$yCoord = $eyes['midpoint']->y - ($h / 2) + ((45 / 907) * $h);

                		$copy = imagecopy($original, $resizedOverlay, $xCoord, $yCoord, 0, 0, $w, $h);
                		if (!$copy) return false;
                	}
                }

                header('Content-Type: image/jpeg');
                imagejpeg($original);
            } else {
                return false;
            }
		}

		public function getUserData ($id) {
			$id = intval($id);
			$query = "SELECT * FROM user WHERE id = '$id' LIMIT 1";
			$resource = $this->_getDBResource($query);

			return mysql_fetch_assoc($resource);
		}

		public function updateStep ($id, $step, $insert = false) {
			$id = intval($id);
			$step = intval($step);

			if ($step == 0) return;

			if ($insert) {
				$query = "INSERT INTO user (id, step, updated) VALUES ('$id', $step, CURRENT_TIMESTAMP)";
			} else {
				$query = "UPDATE user SET step = $step, updated = CURRENT_TIMESTAMP WHERE id = '$id'";	
			}
			
			$resource = $this->_getDBResource($query);
		}

		public function getMediaUrl ($attachments, $type) {
			$url = false;

			if (is_array($attachments)) {
				foreach ($attachments as $attachment) {
					if ($attachment->type == $type) {
						$url = $attachment->payload->url;
					}
				}
			}

			return $url;
		}

		public function updateUserVariable ($step, $msg) {
			// nothing to update
			if (!isset($step['target_variable'])) return;

			$id = $msg->sender->id;
			$text = trim($msg->message->text);
			$attachments = $msg->message->attachments;
			$postback = $msg->postback->payload;

			if (empty($text) && 
				empty($attachments) &&
				empty($postback)) {
				return;
			}

			$value = false;

			switch ($step['response']['type']) {
				case 'text':
					$value = $text;
					break;
				case 'button':
					// postback should already be validated
					$obj = json_decode($postback);
					$value = $obj->value;
					break;
				case 'audio':
					$value = $this->getMediaUrl($attachments, 'audio');
					break;
				case 'text||image':
				case 'text||selfie':
					$value = $this->getMediaUrl($attachments, 'image');
					break;
			}
			
			if ($value === false) return;

			$id = intval($id);
			
			if (is_array($step['target_variable'])) {
				if (!is_array($value)) return;

				$str = array();

				foreach ($value as $val) {
					$col = mysql_real_escape_string(trim($val->key));
					$v = mysql_real_escape_string(trim($val->value));
					$str[] = $col . ' = \'' . $v . '\'';
				}

				$query = "UPDATE user SET " . implode(', ', $str) . " WHERE id = '$id'";				
			} else {
				$column = mysql_real_escape_string($step['target_variable']);
				$value = trim(mysql_real_escape_string($value));
				$query = "UPDATE user SET $column = '$value' WHERE id = '$id'";
			}
			
			$resource = $this->_getDBResource($query);
		}

		public function logUserMessage ($id, $step, $msg) {
			$id = intval($id);
			$step = intval($step);

			$msg = mysql_real_escape_string(json_encode($msg));
			$query = "INSERT INTO log (id, step, msg) VALUES ('$id', $step, '$msg')";
			$resource = $this->_getDBResource($query);
		}

		public function getCleanString ($str, $numeric = false) {
			$str = strtolower($str);

			if ($numeric) {
                return preg_replace('/[^0-9]/', '', $str);
            } else {
                return preg_replace('/[^a-z]/', '', $str);
            }
		}

		public function isValidResponse ($step, $stepNum, $msg) {
			$text = trim($msg->message->text);
			$attachments = $msg->message->attachments;
			$postback = $msg->postback->payload;

			$response = $step['response'];

			switch ($response['type']) {
				case 'text':
					if (empty($text)) {
						return false;
					}

					if (isset($response['maxlength'])) {
						if (strlen($text) > $response['maxlength']) {
							return false;
						}	
					}
					
					return true;
					break;
				case 'button':
					$obj = json_decode($postback);
					$choices = $step['response']['choices'];
					
					// not json
					if (is_null($obj)) return false;
					
					// steps don't match
					if ($obj->step != $stepNum) return false;

					// no value
					if (empty($obj->value) && empty($obj->destination)) return false;

					// not a possible value
					$val = false;

					if (empty($obj->value)) {
						// validating button destination(s)
						if (is_numeric($obj->destination)) {
							$val = $obj->destination;
						} else {
							foreach ($obj->destination->choices as $choice) {
								if (!in_array($choice->destination, $step['response']['choices'])) {
									return false;
								}
							}

							return true;
						}
					} else {
						if (is_array($obj->value)) {
							$cast = json_decode(json_encode($obj->value), true);

							foreach ($choices as $choice) {
								if ($choice == $cast) {
									return true;
								}
							}

							return false;
						} else {
							$val = $obj->value;	
						}
					}
					
					if (!in_array($val, $choices)) return false;

					return true;
					break;
				case 'text||image':
				case 'text||selfie':
					$hasText = !empty($text);
					
					$hasImage = false;
					
					if (is_array($attachments)) {
						foreach ($attachments as $attachment) {
							if ($attachment->type == 'image') {
								$hasImage = true;
							}
						}
					}

					if ($hasText || $hasImage) return true;
					break;
				case 'audio':
					if (is_array($attachments)) {
						foreach ($attachments as $attachment) {
							if ($attachment->type == 'audio') {
								return true;
							}
						}
					}
					break;
			}

			return false;
		}

		protected function _processMessages () {
			$msg = $this->getMessage();
			if ($msg === false) return;

			foreach ($msg->entry as $entry) {
				foreach ($entry->messaging as $messaging) {
					$this->_respond($messaging);
				}
			}
		}

		protected function _getTruncated ($key, $str) {
			if ($key != 'text') return $str;

			if (strlen($str) > 320) {
				return substr($str, 0, 319) . '…';
			} else {
				return $str;
			}
		}

		protected function _getUserValueForKey ($key, $value) {
			switch (strtolower($key)) {
				case 'name_avatar':
					if (empty($value)) {
						return 'anonymous diver';
					} else {
						return $value;
					}
					break;
				case 'selfie':
				case 'audio':
					return urlencode($value);
					break;
				default:
					return $value;
			}
		}

		protected function _sendMessage ($id, $userData, $stepIndex) {
			if ($stepIndex != -1 && !isset($this->_steps[$stepIndex])) {
				// @todo
				$this->send($id, 'error! step not found.');
				return;
			}

			if ($stepIndex == -1) {
				$step = $this->_exitStep;
			} else {
				$step = $this->_steps[$stepIndex];	
			}
			
			foreach ($step['content'] as $msg) {
				// plug in user variables
				$userVariables = array(
					'name_avatar' => ''
				);

				foreach ($this->_userVariables as $var) {
					if (!empty($userData[$var])) {
						$userVariables[$var] = $userData[$var];
					}
				}

				foreach ($userVariables as $key => $value){
					if (is_array($msg)) {
						array_walk_recursive($msg, function (&$v, $k, $param) {
							if (is_string($v)) {
								$newVal = $this->_getUserValueForKey($param['key'], $param['value']);
								$v = str_replace('{' . strtoupper($param['key']) . '}', $newVal, $v);
							}
						}, array(
							'key' => $key,
							'value' => $value
						));
					} else {
						$newVal = $this->_getUserValueForKey($key, $value);
						$msg = str_replace('{' . strtoupper($key) . '}', $newVal, $msg);
					}
				}

				// truncate
				if (is_array($msg)) {
					array_walk_recursive($msg, function (&$v, $k) {
						if (is_string($v)) {
							$v = $this->_getTruncated($k, $v);
						}
					});
				} else {
					$msg = $this->_getTruncated('text', $msg);
				}

				$this->send($id, $msg);
			}

			if ($step['auto-advance'] && isset($step['destination'])) {
				// doing an auto-advance
				$this->updateStep($id, $step['destination']);
				$this->_sendMessage($id, $userData, $step['destination']);
			}
		}

		protected function _respond ($msg) {
			$id = $msg->sender->id;
			$text = trim($msg->message->text);
			$attachments = $msg->message->attachments;
			$postback = $msg->postback->payload;

			if (empty($text) && 
				empty($attachments) &&
				empty($postback)) {
				return;
			}

			$userData = $this->getUserData($id);
			$cleanText = $this->getCleanString($text);

			if (is_array($userData) && $userData['step'] > 0) {
				$this->logUserMessage($id, $userData['step'], $msg);
				
				// exit point
				if ($cleanText == 'quit') {
					$this->updateStep($id, -1, !is_array($userData));
					$this->_sendMessage($id, $userData, -1);
					return;
				}

				$step = $this->_steps[$userData['step']];
				$isValidResponse = $this->isValidResponse($step, $userData['step'], $msg);
				
				// check if response meets requirements
				if ($isValidResponse === true) {
					// update any variables
					// @todo
					// off-step button press?
					$this->updateUserVariable($step, $msg);
					$userData = $this->getUserData($id);

					// update step
					$destination = false;

					if (isset($step['destination'])) {
						$destination = $step['destination'];
					} else {
						// getting destination from button value
						$obj = json_decode($postback);
						$destination = $obj->destination;

						if (!is_numeric($destination)) {
							$userVal = $userData[$destination->key];

							foreach ($destination->choices as $choice) {
								if ($userVal == $choice->value) {
									$destination = $choice->destination;
									break;
								}
							}
						}
					}

					$this->updateStep($id, $destination);

					// send next message
					$this->_sendMessage($id, $userData, $destination);
				} else {
					// @todo
					// send error message
					$this->send($id, 'error! invalid response.');
				}
			} else {
				if ($cleanText == 'divein') {
					// entry point
					$this->logUserMessage($id, 0, $msg);
					$this->updateStep($id, 1, !is_array($userData));
					$this->_sendMessage($id, $userData, 1);
				}
			}	
		}
	}

?>
