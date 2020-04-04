<?php

namespace App\Helpers\Zalo;

abstract class ZaloAES
{
	protected static $_key = 'U0IkYQmnHqRRTPBB7cXAog==';
	protected static $_prevBlock = null;
	protected static $_iv = [0, 0, 0, 0];
	protected static $_keySchedule = [];
	protected static $_invKeySchedule = [];

	public static function parseKey ($e) {
	    $t = strlen($e);
	    $r = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	    $a = [null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null, 62,null,null,null, 63, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61,null,null,null, 64,null,null,null, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25,null,null,null,null,null,null, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51];

	    $o = substr($r, 64, 1);
	    if ($o) {
	        $s = strrpos($e, $o) - 1;

	        if ($s) {
	        	$t = $s;
	        }
	    }

	    return self::subParseKey($e, $t, $a);
	}

	public static function subParseKey($e, $t, $r) {

	    for ($a = [], $i = 0, $o = 0; $o < $t; $o++) {
	    	if ($o % 4) {
	            $s = $r[ord($e[$o - 1])] << $o % 4 * 2;
	            $l = $r[ord($e[$o])] >> 6 - $o % 4 * 2;
	            
	            if (!isset($a[$i >> 2])) {
	            	$a[$i >> 2] = null;
	            }
	            $a[$i >> 2] |= self::intval32bits(($s | $l) << 24 - $i % 4 * 8);
	            $i++;

	        } 
	    }
	        
	    return [
	    	'words'	=> $a,
	    	'sigBytes'	=> $i
	    ];
	}

	public static function setKeySchedule ($e) {
	    $a = [99,124,119,123,242,107,111,197,48,1,103,43,254,215,171,118,202,130,201,125,250,89,71,240,173,212,162,175,156,164,114,192,183,253,147,38,54,63,247,204,52,165,229,241,113,216,49,21,4,199,35,195,24,150,5,154,7,18,128,226,235,39,178,117,9,131,44,26,27,110,90,160,82,59,214,179,41,227,47,132,83,209,0,237,32,252,177,91,106,203,190,57,74,76,88,207,208,239,170,251,67,77,51,133,69,249,2,127,80,60,159,168,81,163,64,143,146,157,56,245,188,182,218,33,16,255,243,210,205,12,19,236,95,151,68,23,196,167,126,61,100,93,25,115,96,129,79,220,34,42,144,136,70,238,184,20,222,94,11,219,224,50,58,10,73,6,36,92,194,211,172,98,145,149,228,121,231,200,55,109,141,213,78,169,108,86,244,234,101,122,174,8,186,120,37,46,28,166,180,198,232,221,116,31,75,189,139,138,112,62,181,102,72,3,246,14,97,53,87,185,134,193,29,158,225,248,152,17,105,217,142,148,155,30,135,233,206,85,40,223,140,161,137,13,191,230,66,104,65,153,45,15,176,84,187,22];
	    $p = [0, 1, 2, 4, 8, 16, 32, 64, 128, 27, 54];

	    $t = $e['words'];
	    $n = $e['sigBytes'] / 4;
	    $_nRounds = $n + 6;
	    $r = 4 * ($_nRounds + 1);
	    $i = [];

	    for ($o = 0; $o < $r; $o++) {
	    	if ($o < $n) $i[$o] = $t[$o];
	    	else {
	    	    $s = $i[$o - 1];
	    	    if ($o % $n) {
	    	    	if ($n > 6 && $o % $n == 4) {
	    	    	 	$s = self::intval32bits($a[self::zerofill($s, 24)] << 24) | self::intval32bits($a[self::zerofill($s, 16) & 255] << 16) | self::intval32bits($a[self::zerofill($s, 8) & 255] << 8) | $a[255 & $s];
	    	    	}
	    	    } else {
	    	    	$s = self::intval32bits($s << 8) | self::zerofill($s, 24);
	    	    	$s = self::intval32bits($a[self::zerofill($s, 24)] << 24) | self::intval32bits($a[self::zerofill($s, 16) & 255] << 16) | self::intval32bits($a[self::zerofill($s, 8) & 255] << 8) | $a[255 & $s];
	    	    	$s ^= self::intval32bits($p[$o / $n | 0] << 24);
	    	    }
	    	    if (!isset($i[$o - $n])) {
	            	$i[$o - $n] = null;
	            }
	    	    $i[$o] = self::intval32bits($i[$o - $n] ^ $s);
	    	}
	    }
	    return $i;
	}

	public static function parse($e) {
	    for ($t = strlen($e), $n = [], $r = 0; $r < $t; $r++) {

	    	if(!isset($n[self::zerofill($r, 2)])) {
	    		$n[self::zerofill($r, 2)] = null;
	    	}
	    	
	    	$n[self::zerofill($r, 2)] |= self::intval32bits((255 & ord($e[$r])) << (24 - $r % 4 * 8));
	    }

	    return [
	    	'words'	=> $n,
	        'sigBytes'	=> $t
	    ];
	}

	abstract static function doFinalize($e);

	public static function pad($e, $t) {
		for ($n = 4 * $t, $r = $n - $e['sigBytes'] % $n, $a = self::intval32bits($r << 24) | self::intval32bits($r << 16) | self::intval32bits($r << 8) | $r, $o = [], $s = 0; $s < $r; $s += 4) $o[] = $a;

		$l = [
		    'words' => $o,
		    'sigBytes' => $r
		];

		$e = self::concat($e, $l);

		return $e;
	}

	public static function unpad($e) {
	    $t = (255 & $e['words'][self::zerofill($e['sigBytes'] - 1, 2)]);
	    $e['sigBytes'] -= $t;

	    return $e;
	}

	public static function concat($e1, $e2) {
	    $t = $e1['words'];
        $n = $e2['words'];
        $r = $e1['sigBytes'];
        $a = $e2['sigBytes'];

	    if ($r % 4)
	        for ($i = 0; $i < $a; $i++) {
	            $o = self::zerofill($n[self::zerofill($i,2)], 24 - $i % 4 * 8) & 255;

	            if (!isset($t[self::zerofill($r + $i,2)])) {
	            	$t[self::zerofill($r + $i,2)] = null;
	            }
	            $t[self::zerofill($r + $i,2)] |= self::intval32bits($o << 24 - ($r + $i) % 4 * 8);
	        } else
	            for ($i = 0; $i < $a; $i += 4) $t[self::zerofill($r + $i,2)] = $n[self::zerofill($i,2)];

	    $e1['sigBytes'] += $a;
	    $e1['words'] = $t;
	    return $e1;
	}

	public static function doAES($str) {
		$parseKey = self::parseKey (self::$_key);
		self::$_keySchedule = self::setKeySchedule($parseKey);
		$data = static::doFinalize($str);

		$result = static::stringify($data);

		return $result;
	}

	public static function process($n) {
	    $r = $n['words'];
        $a = $n['sigBytes'];
        $i = 4; //blockSize
        $s = ceil($a / (4 * $i)) * $i;
        $l = $s;
        $u = min(4 * $l, $a);
	    if ($l) {
	        for ($c = 0; $c < $l; $c += $i) {
	        	$r = static::processBlock($r, $c);
	        	// break;
	        }


	        $d = array_splice($r,0, $l);
	        $n['sigBytes'] -= $u;
	    }

	    return [
	    	'words'	=> $d,
	    	'sigBytes'	=> $u
	    ];
	}

	abstract public static function processBlock($e, $t);

	public static function ncall($e, $n, $r) {
	    $a = self::$_iv;
	    if ($a) {
	        $i = $a;
	        self::$_iv = null;
	    } else $i = self::$_prevBlock;
	    for ($o = 0; $o < $r; $o++) {
	    	if (!isset($e[$n + $o])) {
	    		$e[$n + $o] = null;
	    	}
	    	$e[$n + $o] ^= $i[$o];
	    }
	    return $e;
	}

	abstract public static function doCryptBlock($e, $t);

	abstract public static function stringify($e);

	public static function intval32bits($value)
	{
	    $value = ($value & 0xFFFFFFFF);

	    if ($value & 0x80000000)
	        $value = -((~$value & 0xFFFFFFFF) + 1);

	    return $value;
	}

	public static function zerofill($a,$b) { 
	    if($a>=0) return $a>>$b;
	    if($b==0) return (($a>>1)&0x7fffffff)*2+(($a>>$b)&1);
	    return ((~$a)>>$b)^(0x7fffffff>>($b-1)); 
	}

}

?>