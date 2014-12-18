<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	
	$templateEngine = new TemplateEngine();
	$sessionManager = new SessionManager();
	
	if(isset($_POST['seed'])) {
		if(is_numeric($_POST['seed']) && $_POST['seed'] >= 0)
			$seed = $_POST['seed'];
		else
			$seed = rand();
		$sessionManager->reset($seed);
	} else {	
		$seed = $sessionManager->getData('seed');
	}
	
	$jumbotron = "<h1>Eve's dashboard</h1><br><p>Seed: $seed</p>";
	
	$maincontent = <<<MainContent
<p><strong>Code leak:</strong> <code>\$hash = substr(md5(\$username.'|'.\$password), 0, ##hashlength##)</code></p>
<br>
<a role="button" href="index.php" class="btn btn-lg btn-info btn-block">Secret chat</a>
<a role="button" href="eavesdrop.php" class="btn btn-lg btn-danger btn-block">Eavesdropped conversation</a>
<br>
<br>
<h3>Important remark</h3><br>
<p class="text-justify"><strong>This problem is dynamically generated.</strong></p>
<p>Your instance of the problem is stored in a session file so don't forget to include a session cookie in your script.</p>
<p>Use the following form to generate a different instance of this problem with a given seed (for a random seed put a negative seed)</p><br>
<form class="form-inline" role="form" method="post" name="reset-form" action="dashboard.php">
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
		$jumbotron .= '<p>Cake #'.$test.'</p><p class="lead">DATA DUMP ON (see utilities/Tools.php)</p>';
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