<?php

namespace App\Helpers\TTS;

class GoogleTTS
{
	public static function getLinkTTS($messenger) {
		$tk = static::genTk($messenger);
		$q = urlencode($messenger);
		$result = "https://translate.google.com/translate_tts?ie=UTF-8&q=". $q ."&tl=vi&total=1&idx=0&textlen=".mb_strlen($messenger)."&tk=". $tk ."&client=webapp&prev=input";

		return $result;
	}

	public static function genTk($messenger) {
		$a = $messenger;
		$b = '440066.238569069';
		$d = explode(".", $b);

		$b = (int)$d[0];
		$e = [];
		$f = 0;
		preg_match_all('/./u', $a, $split_a);
		$split_a = $split_a[0];

		for ($g=0; $g < count($split_a); $g++) {
			$k = static::JS_charCodeAt($split_a[$g], 0);
			if (128 > $k) {
				$e[$f++] = $k;
			} else {
				if (2048 > $k) {
					$e[$f++] = $k >> 6 | 192;
				} else {
					if (55296 == ($k & 64512) && $g + 1 < count($split_a) && 56320 == (static::JS_charCodeAt($split_a[$g + 1], 0) & 64512)) {
						$g += 1;
						$k = 65536 + (($k & 1023) << 10) + (static::JS_charCodeAt($split_a[$g], 0) & 1023);
						$e[$f++] = $k >> 18 | 240;
						$e[$f++] = $k >> 12 & 63 | 128;
					} else {
						$e[$f++] = $k >> 12 | 224;
						$e[$f++] = $k >> 6 & 63 | 128;
					}
				}

				$e[$f++] = $k & 63 | 128;
			}
		}

		$a = $b;
		for ($f = 0; $f < count($e); $f++) {
			$a += $e[$f];
			$a = static::sub($a, "+-a^+6");
		}
		$a = static::sub($a, "+-3^+b+-f");
		$a ^= (int)$d[1];

		0 > $a && ($a = ($a & 2147483647) + 2147483648);
		$a %= 1E6;

		return $a . "." . ($a ^ $b);
	}

	public static function sub($a, $b) {

		// $test = static::sub(440186, "+-a^+6");
		#445582599
		for ($c=0; $c < strlen($b) - 2; $c += 3) { 
			$d = substr($b, $c + 2, 1);

			$d = "a" <= $d ? ord($d) - 87 : (int)$d;

			$d = "+" == substr($b, $c + 1, 1) ? $a >> $d : $a << $d;

			$a = "+" == substr($b, $c, 1) ? $a + $d & 4294967295 : $a ^ $d;
		}

		return $a;
	}

	public static function getUTF16CodeUnits($string) {
	    $string = substr(json_encode($string), 1, -1);
	    preg_match_all("/\\\\u[0-9a-fA-F]{4}|./mi", $string, $matches);
	    return $matches[0];
	}

	public static function JS_StringLength($string) {
	    return count(static::getUTF16CodeUnits($string));
	}

	public static function JS_charCodeAt($string, $index) {
	    $utf16CodeUnits = static::getUTF16CodeUnits($string);
	    $unit = $utf16CodeUnits[$index];
	    
	    if(strlen($unit) > 1) {
	        $hex = substr($unit, 2);
	        return hexdec($hex);
	    }
	    else {
	        return ord($unit);
	    }
	}
}

?>