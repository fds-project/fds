<?php
error_reporting(-1);
ini_set('display_errors', 'On');
class encryption {
	public $method = 'aes-256-cfb8';
	
	public function encode($encoded, $key, $iv) {
		if(!$iv) {
			$iv = chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0);
		}
		$encrypted = base64_encode(openssl_encrypt($encoded, $this->method, $key, 1, $iv));
		return $encrypted;
	}
	
	public function decode($encoded, $key, $iv) {
		if(!$iv) {
			$iv = chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0).chr(0x0);
		}
		
		$decrypted = openssl_decrypt(base64_decode($encoded), $this->method, $key, 1, $iv);
		$decrypted = $this->unpad($decrypted, 32);
		//$decrypted = utf8_encode($decrypted);
		
		return $decrypted;
	}
	
	private function unpad($str, $blocksize)
    {
        $len = strlen($str);
        $pad = ord($str[$len - 1]);
        if ($pad && $pad <= $blocksize) {
            if (substr($str, -$pad) === str_repeat(chr($pad), $pad)) {
                return substr($str, 0, $len - $pad);
            }
        }
        return $str;
    }
	public function ivCreateValidationToken ($iv, $len=40) {
		$iv = base64_encode($iv);
		$enc = sha1($iv).sha1($iv);
		$enc = sha1($enc);
		$enc = str_rot13($enc);
		return substr($enc, 0, $len);
	}
	
	public function mergeKeyCiphers($key1, $key2, $len = 32) {
		$enc = sha1($key1).sha1($key2);
		$enc = sha1($enc);
		$enc = str_rot13($enc);
		return substr($enc, 0, $len);
	}
	
	public function randomKey($len = 32) {
		$random = rand(9999, 999999);
		$random2 = rand(9999, 999999);
		$enc = sha1($random).sha1($random2);
		$enc = sha1($enc);
		$enc = str_rot13($enc);
		return substr($enc, 0, $len);
	}
	
	public function randIV() {
		$wasItSecure = false;
		$iv = openssl_random_pseudo_bytes(16, $wasItSecure);
		if ($wasItSecure) {
			return $iv;
		} else {
			die("Seems like there is an error in the IV generation sequence, cannot create fully random key");
			return false;
		}
	}
}
?>