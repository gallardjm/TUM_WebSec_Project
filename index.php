<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";

	
	$templateEngine = new TemplateEngine();
	$sessionManager = new SessionManager();
	
	$seed = $sessionManager->issetData('seed') ? $sessionManager->getData('seed') : 10;
	if(!$sessionManager->issetData('adminPublicKey') || isset($_GET['reset'])) {
		include "utilities/ProblemManager.php";
		if(isset($_GET['seed']) && is_numeric($_GET['seed']))
			$seed = $_GET['seed'];
		ProblemManager::seed($sessionManager, $seed);
	}
	
	if(!$sessionManager->issetData('test')) $sessionManager->setData('test', -1);
	$test = $sessionManager->getData('test') +1;
	$sessionManager->setData('test', $test);
	
	$maincontent = '<p>Alice Cyphertext:<br>'.$sessionManager->getData('aliceCyphertext')
				.'<br><br>=> '.$sessionManager->getData('aliceText').'</p>'
				.'<p>Admin Cyphertext:<br>'.$sessionManager->getData('adminCyphertext')
				.'<br><br>=> '.$sessionManager->getData('adminText').'</p>'
				.'<p>Alice public key:<br>'.$sessionManager->getData('alicePublicKey').'</p>'
				.'<p>Admin public key:<br>'.$sessionManager->getData('adminPublicKey').'</p>'
				;
	
	$templateEngine->setContent("##BodyJumbotron##", "<p>The cake: $test</p><p>Seed: $seed</p>");
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
			
	//$sessionManager->dump();
	
	//phpinfo();
?>