<?php

	class AudioUtils {
		public static function download ($url, $id) {
			// download
			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'DNT: 1',
				'Accept-Encoding: gzip, deflate, sdch',
				'Accept-Language: en-US,en;q=0.8',
				'X-Chrome-UMA-Enabled: 1',
				'Accept: */*',
				'Cache-Control: max-age=0',
				'X-Client-Data: CIe2yQEIorbJAQjEtskBCP2VygEI7JjKAQjunMoB',
				'Connection: keep-alive',
				'If-Modified-Since: Fri, 16 Oct 2015 18:27:31 GMT',
				'Resource-Freshness: max-age=31536000,stale-while-revalidate=2592000,age=623885'
			));

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			// write file
			$orig = preg_replace('/\?.*/', '', basename($url));
			$extension = pathinfo($orig, PATHINFO_EXTENSION);
			$file = 'tmp/' . $id . '.' . $extension;

			$fp = fopen($file, 'w');
		    $write = fwrite($fp, $result);
		    fclose($fp);

			if ($write === false) return false;
			return $file;
		}

		public static function pitchApi ($input, $pitch = '20', $timbre = '10') {
			require(dirname(__FILE__) . '/../../private/credentials.php');
			$url = 'https://api.sonicAPI.com/process/elastique?';
			
			$parameters = array(
				'access_id' => $credentials['sonicapi']['default'],
				'input_file' => $input,
				'pitch_semitones' => $pitch,
				'tempo_factor' => '1',
				'formant_semitones' => $timbre
			);

			$url .= http_build_query($parameters);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			$response = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);

			if ($info['http_code'] == 200) {
				return $response;
			} else {
				// echo '<pre>';
				// print_r($parameters);
				// print_r($info);
				// echo '</pre>';

				return false;
			}
		}

		public static function pitch ($url, $id, $api = false) {
			$orig = AudioUtils::download($url, $id);
			if ($orig === false) return false;

			$orig = escapeshellarg($orig);

			if ($api) {
				$target = 'tmp/' . $id . '.wav';

				// convert to wav
				shell_exec('ffmpeg -i ' . $orig . ' -y ' . escapeshellarg($target));

				// send to api				
				$url = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
				$url .= $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . '/';
				$url .= $target;

				return AudioUtils::pitchApi($url);
			} else {
				return trim(shell_exec('./pitchShift ' . escapeshellarg($id) . ' ' . $orig));
			}
		}
	}

?>
