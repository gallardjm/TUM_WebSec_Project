<?php

/*
	Store and retrieve data for the session
	
	You can access data with setData, getData and issetData
	
	All data are base64_encode in a session file (see __destruct)
	Path and lifetime of the session file in configuration value
*/
class SessionManager {

	//configuration
	const SESSION_LIFETIME = 259201; //3 days
	const SESSION_PATH = '/var/www/team2/project/sessions';

	protected $data; //array

	public function __construct($seed = -1) {
		ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
		ini_set('session.cookie_lifetime', self::SESSION_LIFETIME);
		session_save_path(self::SESSION_PATH);
		session_start();
	
		if(!isset($_SESSION['data'])) 
			$this->reset($seed);
		else
			$this->data = unserialize(base64_decode($_SESSION['data']));
	}
	
	public function __destruct() {
		$_SESSION['data'] = base64_encode(serialize($this->data));
	}
	
	public function reset($seed) {
	
		require_once("Tools.php");

		$this->data = array();
		Tools::seedProblem($this, ((is_numeric($seed) && $seed>=0) ? $seed : rand()));
	}
	
	public function setData($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function getData($key) {
		if(!isset($this->data[$key]))
			throw new InvalidArgumentException("The key $key isn't defined");
		return $this->data[$key];
	}
	
	public function issetData($key) {
		return isset($this->data[$key]);
	}
	
	public function clearData($key) {
		unset($this->data[$key]);
	}
	
	public function getKeys() {
		return array_keys($this->data);
	}

	public function dump() {
		var_dump($this->data);
	}

}
?>