<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";

	$templateEngine = new TemplateEngine();
	$sessionManager = SessionManager::getInstance();
	
	$templateEngine->addStyle('pre {white-space: pre-wrap;}');
	
	$jumbotron = <<<Jumbotron
<h1>Eavesdropped Conversation</h1>
Jumbotron;
	
	$maincontent = <<<Eavesdrop
<h3>Conversation</h3><br>
<p>
	<strong>Alice:</strong><br>
	<pre id="AliceCyphertext">##aliceCyphertext##</pre><br><br>
	<strong>Admin response:</strong><br>
	<pre id="AdminCyphertext">##adminCyphertext##</pre><br>
</p>
<h3>Keys</h3><br>
<p>
	<strong>Alice:</strong><br>
	<pre id="AlicePublicKey">##alicePublicKey##</pre><br><br>
	<strong>Admin:</strong><br>
	<pre id="AdminPublicKey">##adminPublicKey##</pre><br>
</p>
<br>
<a role="button" href="dashboard.php" class="btn btn-lg btn-danger btn-block">Back to my evil lair</a>
Eavesdrop;

	$maincontent = str_replace('##aliceCyphertext##', $sessionManager->getData('aliceCyphertext'), $maincontent);
	$maincontent = str_replace('##adminCyphertext##', $sessionManager->getData('adminCyphertext'), $maincontent);
	$maincontent = str_replace('##alicePublicKey##', $sessionManager->getData('alicePublicKey'), $maincontent);
	$maincontent = str_replace('##adminPublicKey##', $sessionManager->getData('adminPublicKey'), $maincontent);
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
			
?>