<?php

	class MapUtils {
		public static function getSignature ($secret, $path) {
	        $secret = base64_decode(str_replace(array('-', '_'), array('+', '/'), $secret));
			$signature = hash_hmac('sha1', $path, $secret, true);
			return str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));
		}

		public static function getMapLinks ($latlng, $credentials) {
			$params = array(
				'center' => $latlng,
				'markers' => 'color:blue|' . $latlng,
				'maptype' => 'satellite',
				'zoom' => 8,
				'size' => '640x400',
				'key' => $credentials['google']['static_maps']
			);

			$staticMapPath = '/maps/api/staticmap?' . http_build_query($params);
			$staticMapPath .= '&signature=' . MapUtils::getSignature($credentials['google']['static_maps_secret'], $staticMapPath);
			
			return array(
				'link' => 'http://maps.google.com/?ll=' . $latlng,
				'imgUrl' => 'https://maps.googleapis.com/' . $staticMapPath
			);
		}
	}

?>
