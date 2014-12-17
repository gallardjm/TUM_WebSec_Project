<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	
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
	
	$jumbotron = "<h1>Eve's hacking dashboard</h1><p>Cake #$test</p><p>Seed: $seed</p>";
	
	$maincontent = <<<Buttons
<a role="button" href="index.php" class="btn btn-lg btn-success btn-block">Secret chat</a>
<a role="button" href="#" class="btn btn-lg btn-danger btn-block">Eavesdropped conversation</a>
Buttons;
	/*
	$maincontent .= '<p>Alice Cyphertext:<br>'.$sessionManager->getData('aliceCyphertext')
				.'<br><br>=> '.$sessionManager->getData('aliceText').'</p>'
				.'<p>Admin Cyphertext:<br>'.$sessionManager->getData('adminCyphertext')
				.'<br><br>=> '.$sessionManager->getData('adminText').'</p>'
				.'<p>Alice public key:<br>'.$sessionManager->getData('alicePublicKey').'</p>'
				.'<p>Admin public key:<br>'.$sessionManager->getData('adminPublicKey').'</p>'
				;
	*/
	
	
	
	if(Tools::DEBUG) {
		$jumbotron .= '<p class="lead">DEBUG ON (see utilities/Tools.php)</p><ul><li>Register with username Alice</li><li>The hash is the plain password</li></ul>';
	}
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
	
	if(Tools::DEBUG) {
		$sessionManager->dump();
		//phpinfo();
	}
?>