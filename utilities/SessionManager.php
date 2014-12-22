<?php

/*
	Store and retrieve data for the session
	Implement Singleton pattern, get an instance with SessionManager::getInstance()
	or SessionManager::getInstanceResetSeed($seed)
	
	You can access data with setData, getData and issetData
	
	All data are base64_encode in a session file (see __destruct)
	Path and lifetime of the session file in configuration value
*/
class SessionManager {

	//configuration
	const SESSION_LIFETIME = 259201; //3 days
	const SESSION_PATH = '/var/www/team2/project/sessions';
	private static $instance = null;

	protected $data; //array
	
	public static function getInstance() {
		if(null == self::$instance)
			self::$instance = new SessionManager(false);

		return self::$instance;
		
	}
	
	public static function getInstanceResetSeed($seed) {
		if(null == self::$instance)
			self::$instance = new SessionManager($seed);
		else
			self::$instance->reset($seed);
		
		return self::$instance;
	}

	private function __construct($seed) {
		ini_set('session.gc_maxlifetime', self::SESSION_LIFETIME);
		ini_set('session.cookie_lifetime', self::SESSION_LIFETIME);
		session_save_path(self::SESSION_PATH);
		session_start();
	
		if(!isset($_SESSION['data'])) {
			if($seed === false)
				$seed = rand();
			$this->reset($seed);
		} else {
			$this->data = unserialize(base64_decode($_SESSION['data']));
			if($seed !== false)
				$this->reset($seed);
		}
	}
	
	public function __destruct() {
		$_SESSION['data'] = base64_encode(serialize($this->data));
	}
	
	private function reset($seed) {

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