<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	
	$templateEngine = new TemplateEngine();
	$sessionManager = new SessionManager();
	
	$seed = $sessionManager->issetData('seed') ? $sessionManager->getData('seed') : rand();
	if(!$sessionManager->issetData('adminPublicKey') || isset($_POST['seed'])) {
		if(isset($_POST['seed']) && is_numeric($_POST['seed']) && $_POST['seed'] >= 0)
			$seed = $_POST['seed'];
		else $seed = rand();
		$sessionManager->dropAll();
		Tools::seedProblem($sessionManager, $seed);
	}
	
	if(!$sessionManager->issetData('test')) $sessionManager->setData('test', -1);
	$test = $sessionManager->getData('test') +1;
	$sessionManager->setData('test', $test);
	
	$jumbotron = "<h1>Eve's dashboard</h1><p>Cake #$test</p><p>Seed: $seed</p>";
	
	$maincontent = <<<MainContent
<a role="button" href="index.php" class="btn btn-lg btn-info btn-block">Secret chat</a>
<a role="button" href="#" class="btn btn-lg btn-danger btn-block">Eavesdropped conversation</a>
<br><br>
<h3>Important remarks</h3>
<p class="text-justify">This problem is dynamically generated.<br>
Your instance of the problem is stored in a session file so don't forget to include a session cookie in your script.<br>
Use the following form to generate a different instance of this problem with a given seed (for a random seed put a negative seed)</p>
<form class="form-inline" role="form" method="post" name="reset-form" action="dashboard.php">
	<div class="form-group">
		<label>seed</label>
		<input type="number" class="form-control" name="seed">
	</div>
	<button type="submit" class="btn btn-primary">Get a new instance</button>
</form>	
MainContent;
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