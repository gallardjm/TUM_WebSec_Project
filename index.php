<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	
	$templateEngine = new TemplateEngine();
	$sessionManager = null;
	
	if(isset($_POST['seed'])) {
		if(is_numeric($_POST['seed']) && $_POST['seed'] >= 0)
			$seed = $_POST['seed'];
		else
			$seed = rand();
		$sessionManager = SessionManager::getInstanceResetSeed($seed);
	} else {
		$sessionManager = SessionManager::getInstance();
		$seed = $sessionManager->getData('seed');
	}
	
	$jumbotron = "<h1>Eve's dashboard</h1><br><p><strong>Goal:</strong> Log into Secret Chat with username = Admin</p><p><strong>Seed</strong> #$seed</p>";
	
	$maincontent = <<<MainContent
<p><strong>Code leak:</strong> <code>\$hash = substr(md5(\$username.'|'.\$password), 0, ##hashlength##);</code></p>
<br>
<a role="button" href="secretchat.php" class="btn btn-lg btn-info btn-block">Secret chat</a>
<a role="button" href="eavesdrop.php" class="btn btn-lg btn-danger btn-block">Eavesdropped conversation</a>
<br>
<br>
<h3>README</h3><br>
<p class="text-justify"><strong>This problem is dynamically and randomly generated.</strong></p>
<p class="text-justify">Your instance of the problem is stored in a session file so don't forget to include a session cookie in your script.</p>
<p class="text-justify">For your script, you can start an instance of the problem with a fixed seed by doing a POST request to this page with a parameter 'seed' (int>0).</p>
<p class="text-justify">Your exploit should be able to crack instances of this problem with any given seed.</p>
<p class="text-justify">Use the following form to generate a different instance of this problem with a given seed (for a random seed put a negative value)</p><br>
<form class="form-inline" role="form" method="post" name="reset-form" action="index.php">
	<div class="form-group">
		<label>seed</label>
		<input type="number" class="form-control" name="seed" value="-1">
	</div>
	<button type="submit" class="btn btn-primary">Get a new instance</button>
</form>	
MainContent;
	
	$maincontent = str_replace('##hashlength##', Tools::HASH_LENGTH, $maincontent);
	
	if(Tools::DEBUG) {
		if(!$sessionManager->issetData('test')) $sessionManager->setData('test', -1);
		$test = $sessionManager->getData('test') +1;
		$sessionManager->setData('test', $test);
		$jumbotron .= '<p>---------------------------------------------------------</p><p>Cake #'.$test.'</p><p class="lead">DATA DUMP ON (see utilities/Tools.php)</p>';
	}
	
	if(Tools::DEBUG > 1) {
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