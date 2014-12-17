<?php
	include "utilities/TemplateEngine.php";
	
	$templateEngine = new TemplateEngine();
	
	$jumbotron = "<h1>Secret Chat</h1><p>For insider only</p>";
	
	$maincontent = <<<Buttons
<a role="button" href="login.php" class="btn btn-lg btn-success btn-block">Login</a>
<a role="button" href="register.php" class="btn btn-lg btn-primary btn-block">Register</a>
<br>
<a role="button" href="dashboard.php" class="btn btn-lg btn-danger btn-block">Back to my evil lair</a>
Buttons;
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
?>