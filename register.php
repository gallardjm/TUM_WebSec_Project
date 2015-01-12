<?php
	include "utilities/TemplateEngine.php";
	include "utilities/SessionManager.php";
	include "utilities/Tools.php";

	$templateEngine = new TemplateEngine();
	$sessionManager = SessionManager::getInstance();
	
	$jumbotron = <<<Jumbotron
<h1>Register</h1>
<p>You can only register with a validated username</p>
Jumbotron;
	
	$templateEngine->setContent("##BodyJumbotron##", $jumbotron);
	
	if(isset($_POST['time_travel']) && $_POST['time_travel'] == 'before_registration') {
		$sessionManager->clearData('hash');
	}
	
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
	<button type="submit" class="btn btn-lg btn-primary btn-block">Register</button>
</form>
<br>
<p><a role="button" href="secretchat.php" class="btn btn-lg btn-info btn-block">Back to index</a></p>	
RegistrationForm;

		$templateEngine->setContent("##BodyMaincontent##", $registrationForm);		
	} else {
		if(!Tools::validateForm($_POST['username'],$_POST['password'])){
			$maincontent = '<div class="alert alert-danger">Invalid characters input!!!<ul><li>Username shall only use alphanumerics and _</li><li>Password cannot use "|"</li></ul></div>';
		} else if(!Tools::checkRegistration($sessionManager, $_POST['username'])) {
			$maincontent = '<div class="alert alert-danger">Unknown username!!!</div>';
		} else if(Tools::alreadyRegister($sessionManager, $_POST['username'])) {
			$maincontent = <<<TimeMachine
<div class="alert alert-danger">This username has already been registered and isn't valid anymore!!!</div>
<form role="form" method="post" name="timemachine-form" action="register.php">
<input type="hidden" class="form-control" name="time_travel" value="before_registration">
<button type="submit" class="btn btn-lg btn-warning btn-block">Go back in time to before you registered</button>
</form>
TimeMachine;
		} else {
			Tools::registerUser($sessionManager, $_POST['username'],$_POST['password']);
			$maincontent = '<div class="alert alert-success">You can now log in with your username '.$_POST['username'].'</div>';
		}
		$maincontent .= '<br><p><a role="button" href="secretchat.php" class="btn btn-lg btn-info btn-block">Back to index</a></p>';
		
		$templateEngine->setContent("##BodyMaincontent##", $maincontent);	
	}
	
	$templateEngine->render();
			
?>