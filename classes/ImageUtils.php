<?php

	class ImageUtils {
		public static function getResizedPng ($w, $h, $src) {
	        $resized = imagecreatetruecolor($w, $h);

			$background = imagecolorallocate($src, 0, 0, 0);
			imagecolortransparent($resized, $background);
			imagealphablending($resized, false);
			imagesavealpha($resized, true);
			imagecopyresampled($resized, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));

			return $resized;
		}

		public static function getDistanceBetweenVertices ($a, $b) {
			return sqrt(pow(($b->x - $a->x), 2) + pow(($b->y - $a->y), 2));
		}

		public static function getTiltAngle ($eyes) {
			$yDiff = $eyes['right']->y - $eyes['left']->y;
			$xDiff = $eyes['right']->x - $eyes['left']->x;
			return rad2deg(atan2($yDiff, $xDiff));
		}

		private static function _rotateX ($x, $y, $theta) {
	    	return $x * cos($theta) - $y * sin($theta);
	    }

	    private static function _rotateY ($x, $y, $theta) {
	    	return $x * sin($theta) + $y * cos($theta);
	    }

		public static function getNewDimensions ($w, $h, $deg) {
			if ($deg == 0) {
				return array(
					'width' => $w,
					'height' => $h
				);
			}

		    $theta = deg2rad($deg);

	        $temp = array(
	        	self::_rotateX(0, 0, 0 - $theta),
	            self::_rotateX($w, 0, 0 - $theta),
	            self::_rotateX(0, $h, 0 - $theta),
	            self::_rotateX($w, $h, 0 - $theta)
	        );

			$width = floor(max($temp) - min($temp));
			$width -= 2;

			$temp = array(
				self::_rotateY(0, 0, 0 - $theta),
				self::_rotateY($w, 0, 0 - $theta),
				self::_rotateY(0, $h, 0 - $theta),
				self::_rotateY($w, $h, 0 - $theta)
			);

		    $height = floor(max($temp) - min($temp));
		    $height -= 2;

		    return array(
		    	'width' => $width,
		    	'height' => $height
		    );
		}
	}

?>
