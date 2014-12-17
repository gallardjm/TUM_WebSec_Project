<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	$templateEngine = new TemplateEngine();
	$sessionManager = new SessionManager();
	
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
	<button type="submit" class="btn btn-primary btn-block">Login</button>
</form>		
LoginForm;

		$templateEngine->setContent("##BodyMaincontent##", $loginForm);		
	} else {
		if(!Tools::checkLogin($sessionManager, $_POST['username'], $_POST['password'])) {
			$maincontent = '<div class="alert alert-danger">Invalid login !!!</div>';
		} else {
			$maincontent = '<div class="alert alert-success">Welcome '.$_POST['username'].'</div>';
		}
		$templateEngine->setContent("##BodyMaincontent##", $maincontent);	
	}
	
	$templateEngine->render();
			
?>