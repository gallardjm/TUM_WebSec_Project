<?php

class Tools {

	const DEBUG = 2; //if == false no debug, if ==true sessionManager dump in dashboard, if > 1 hash replace by plaintext password and register check by username == 'Alice'
	const STORE_ALL = 1;

	const HASH_LENGTH = 10; //default = 10
	const ALICE_TEXT = "Hi, it's Alice. I would like to register. The passphrase is 'The cake is a lie'.";
	const ADMIN_TEXT = "Hi Alice. You can register using the username ##aliceUsername##. Btw change your RSA key ASAP!!!";
	const ALICE_KEY_SIZE = 1024;
	const ADMIN_KEY_SIZE = 4096;

	public static function validateForm($username, $password) {
		
		return strpos('|', $password) === FALSE && preg_match('#^[A-Za-z0-9_]*$#', $username);
	}
	
	public static function checkLogin($sessionManager, $username, $password) {	
	
		return $sessionManager->issetData('hash') && strcmp(self::getHash($username, $password), $sessionManager->getData('hash')) == 0;
	}
	
	public static function checkRegistration($sessionManager, $username) {
		if(self::DEBUG > 1)	return strcmp($username, 'Alice') == 0;
		
		return $sessionManager->issetData('aliceUsername') && strcmp($username, $sessionManager->getData('aliceUsername')) == 0;
	}
	
	public static function alreadyRegister($sessionManager) {
		return $sessionManager->issetData('hash');
	}
	
	public static function registerUser($sessionManager, $username, $password) {
		$sessionManager->setData('hash', self::getHash($username, $password));
	}
	
	private static function getHash($username, $password) {
		if(self::DEBUG > 1)	return $_POST['password'];
		
		return substr(md5($username.'|'.$password), 0, self::HASH_LENGTH);
	}
	
	public static function seedProblem($sessionManager, $seed) {
	
		require_once("RSAEngine.php");
		
		mt_srand($seed+1);
		
		$aliceUsername = 'AliCat_'.mt_rand();
		$aliceText = self::ALICE_TEXT;
		$adminText = str_replace('##aliceUsername##', $aliceUsername, self::ADMIN_TEXT);
		
		$rsaEngine = new RSAEngine();
		$aliceKeys = $rsaEngine->generateFaultyKeys(self::ALICE_KEY_SIZE); //faulty key 
		$adminKeys = $rsaEngine->generateGoodKeys(self::ADMIN_KEY_SIZE);
		
		
		$aliceCyphertext = $rsaEngine->encrypt($aliceText, $adminKeys['publicKey']);
		$adminCyphertext = $rsaEngine->encrypt($adminText, $aliceKeys['publicKey']);
		$aliceTextTest = $rsaEngine->decrypt($aliceCyphertext, $adminKeys['privateKey']);
		$adminTextTest = $rsaEngine->decrypt($adminCyphertext, $aliceKeys['privateKey']);
		
		if(0 != strcmp($aliceText, $aliceTextTest) || 0 != strcmp($adminText, $adminTextTest))
			throw new Exception('key generation failled !!!');
			
		$sessionManager->setData('seed', $seed);
		$sessionManager->setData('aliceUsername', $aliceUsername);
		$sessionManager->setData('aliceCyphertext', $aliceCyphertext);
		$sessionManager->setData('adminCyphertext', $adminCyphertext);
		$sessionManager->setData('alicePublicKey', $aliceKeys['publicKey']);
		$sessionManager->setData('adminPublicKey', $adminKeys['publicKey']);
		
		if(self::STORE_ALL) {
			$sessionManager->setData('aliceText', $aliceText);
			$sessionManager->setData('adminText', $adminText);
			$sessionManager->setData('alicePrivateKey', $aliceKeys['privateKey']);	
			$sessionManager->setData('adminPrivateKey', $adminKeys['privateKey']);
		}
	}
}

?>