<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	$templateEngine = new TemplateEngine();
	$sessionManager = SessionManager::getInstance();
	
	$jumbotron = <<<Jumbotron
<h1>Login</h1>
<p>Your privacy is our priority</p>
Jumbotron;
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	
	if(!isset($_POST['username']) || !isset($_POST['password'])) {
		$loginForm = <<<LoginForm
<form role="form" method="post" name="login-form" action="login.php">
	<div class="form-group">
		<label>Username</label>
		<input type="text" class="form-control" name="username">
	</div>
	<div class="form-group">
		<label>Password</label>
		<input type="password" class="form-control" name="password">
	</div>
	<button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
</form>
<br>
<p><a role="button" href="index.php" class="btn btn-lg btn-info btn-block">Back to index</a></p>	
LoginForm;

		$templateEngine->setContent("##BodyMaincontent##", $loginForm);		
	} else {
		if(!Tools::validateForm($_POST['username'],$_POST['password'])){
			$maincontent = '<div class="alert alert-danger">Invalid characters input!!!<ul><li>Username shall only use alphanumerics and _</li><li>Password cannot use "|"</li></ul></div>';
		} else if(!Tools::checkLogin($sessionManager, $_POST['username'], $_POST['password'])) {
			$maincontent = '<div class="alert alert-danger">Invalid login!!!</div>';
		} else {
			$maincontent = '<div class="alert alert-success">Welcome '.$_POST['username'].'</div>';
			if(strtolower($_POST['username']) == 'admin') {
				$maincontent .= '<div class="alert alert-info">Reminder: buy a cake for Eve\'s birthday. It isn\'t a lie!</div>';
			}
		}
		$maincontent .= '<br><p><a role="button" href="index.php" class="btn btn-lg btn-info btn-block">Back to index</a></p>';
		
		$templateEngine->setContent("##BodyMaincontent##", $maincontent);	
	}
	
	$templateEngine->render();
			
?>