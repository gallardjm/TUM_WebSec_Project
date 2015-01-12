<?php
	include "utilities/TemplateEngine.php";
	
	$templateEngine = new TemplateEngine();
	
	$jumbotron = "<h1>Secret Chat</h1>";
	
	$maincontent = <<<Content
<h3>Privacy</h3><br>
<h4>Anonymity guaranty</h4>
<p class="text-justify">To ensure our user privacy, no username or password is stored in our database. Even if someone could breach our database, he would only find hash values without use for him.</p>
<h4>Login security</h4>
<p class="text-justify">We don't store your username, instead we use the hash produced by your username and your password to identify you.</p>
<p class="text-justify">As a cryptographic hash ensure that the smallest modification of the input changes the hash value radically, we can ensure that nobody 
can use your username without knowing your password.</p>
<h4>Registration for insider only</h4>
<p class="text-justify">Only user validated by an admin can register to our chat. They also have to use an admin approved username.</p>
<br>
<p><a role="button" href="secretchat.php" class="btn btn-lg btn-info btn-block">Back to index</a></p>
Content;
	
	//$templateEngine->addStyle('p {text-indent:45px}');
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	$templateEngine->setContent("##BodyMaincontent##", $maincontent);
	
	$templateEngine->render();
?>