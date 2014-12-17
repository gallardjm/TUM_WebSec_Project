<?php

class Tools {

	const DEBUG = 1;

	const HASH_LENGTH = 10;

	public static function checkRegistration($sessionManager, $username) {
		if(self::DEBUG)	return strcmp($username, 'Alice') == 0;
		
		return $sessionManager->issetData('aliceUsername') && strcmp($username, $sessionManager->getData('aliceUsername')) == 0;
	}
	
	public static function checkLogin($sessionManager, $username, $password) {	
	
		return $sessionManager->issetData('hash') && strcmp(self::getHash($username, $password), $sessionManager->getData('hash')) == 0;
	}
	
	public static function registerUser($sessionManager, $username, $password) {
		$sessionManager->setData('hash', self::getHash($username, $password));
	}
	
	private static function getHash($username, $password) {
		if(self::DEBUG)	return $_POST['password'];
		
		return substr(md5($username.$password), 0, self::HASH_LENGTH);
	}
}

?>