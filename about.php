<?php
	include "utilities/TemplateEngine.php";
	
	$templateEngine = new TemplateEngine();
	
	$jumbotron = "<h1>Secret Chat</h1>";
	
	$maincontent = <<<Content
	<p>TO DO</p>
<a role="button" href="index.php" class="btn btn-lg btn-primary btn-block">Back to index</a>
Content;
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
?>