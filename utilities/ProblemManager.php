<?php

include_once "RSAEngine.php";

class ProblemManager {
	
	const DEBUG = true;
	
	const ALICE_TEXT = "Hi, it's Alice. I would like to register. The passphrase is 'The cake is a lie'.";
	const ADMIN_TEXT = "Hi Alice. You can register using the username ##aliceUsername##. Btw change your RSA key ASAP!!!";
	const KEY_SIZE = 512;
	
	public static function seed($sessionManager, $seed = -1) {
		if($seed < 0) {
			$seed = rand();
		}
		
		mt_srand($seed);
		
		$aliceUsername = 'Alice_'.mt_rand();
		$aliceText = self::ALICE_TEXT;
		$adminText = str_replace('##aliceUsername##', $aliceUsername, self::ADMIN_TEXT);
		
		$rsaEngine = new RSAEngine();
		$aliceKeys = $rsaEngine->generateKeys(self::KEY_SIZE, true); //faulty key 
		$adminKeys = $rsaEngine->generateKeys(self::KEY_SIZE, false);
		
		
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
		
		if(self::DEBUG) {
			$sessionManager->setData('aliceText', $aliceText);
			$sessionManager->setData('adminText', $adminText);
			$sessionManager->setData('alicePrivateKey', $aliceKeys['privateKey']);	
			$sessionManager->setData('adminPrivateKey', $adminKeys['privateKey']);
		}
	}

}

?>