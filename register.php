<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	$templateEngine = new TemplateEngine();
	$sessionManager = new SessionManager();
	
	$jumbotron = <<<Jumbotron
<h1>Register</h1>
<p>You can only register with a validated username</p>
Jumbotron;
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	
	if(!isset($_POST['username']) || !isset($_POST['password'])) {
		$registrationForm = <<<RegistrationForm
<form role="form" method="post" name="register-form" action="register.php">
	<div class="form-group">
		<label>Username</label>
		<input type="text" class="form-control" name="username">
	</div>
	<div class="form-group">
		<label>Password</label>
		<input type="password" class="form-control" name="password">
	</div>
	<button type="submit" class="btn btn-primary btn-block">Register</button>
</form>
<br>
<p><a role="button" href="index.php" class="btn btn-info btn-block">Back to index</a></p>	
RegistrationForm;

		$templateEngine->setContent("##BodyMaincontent##", $registrationForm);		
	} else {
		if(!Tools::checkRegistration($sessionManager, $_POST['username'])) {
			$maincontent = '<div class="alert alert-danger">Unknown username !!!</div>';
		} else {
			Tools::registerUser($sessionManager, $_POST['username'],$_POST['password']);
			$maincontent = '<div class="alert alert-success">Welcome '.$_POST['username'].'</div>';
		}
		$templateEngine->setContent("##BodyMaincontent##", $maincontent);	
	}
	
	$templateEngine->render();
			
?>